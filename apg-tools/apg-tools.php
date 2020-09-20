<?php
/**
 * @package APG_Tools
 * @version 1.0
 */
/*
Plugin Name: APG Tools
Plugin URI: https://www.asiapowergames.com
Description: Tools used to control multiple wordpress site specific for APG.
Author: Jazz
Version: 1.0
Author URI: https://www.github.com/7381jazz
*/

// define( 'WPRP_PLUGIN_SLUG', 'apgtools' );
// define( 'WPRP_PLUGIN_BASE',  plugin_basename(__FILE__) );
// define( 'WPRP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
// define( 'WPR_API_URL', 'http://localhost/testing/apg-tools/api/json.php' );
// define( 'WPR_WEBHOOK', 'http://localhost/testing/apg-tools/webhook.php' );

require_once('includes/config.php');

require_once(ABSPATH.'wp-admin/includes/admin.php');
require_once(ABSPATH.'wp-admin/includes/file.php');
require_once(ABSPATH.'wp-admin/includes/plugin.php');
require_once(ABSPATH.'wp-content/plugins/apg-tools/includes/helper.php');

add_action('admin_menu', 'init_actions');

function init_actions(){
  add_menu_page( 'APG Tools Page', 'APG Tools', 'manage_options', 'apg-tools', 'page_render' );
}

function page_render(){
  $json = file_get_contents(APGPATH.'/apg-tools.json', true);
  $data['apg_config'] = json_decode($json);
  apg_view(ABSPATH.'wp-content/plugins/apg-tools/admin/apg-admin.php', $data);
}

// ----------------------------------------------------------------------------------

add_filter('plugins_api', 'apg_plugin_info', 20, 3);
/*
 * $res empty at this step
 * $action 'plugin_information'
 * $args stdClass Object ( [slug] => woocommerce [is_ssl] => [fields] => Array ( [banners] => 1 [reviews] => 1 [downloaded] => [active_installs] => 1 ) [per_page] => 24 [locale] => en_US )
 */
function apg_plugin_info( $res, $action, $args ){

	// do nothing if this is not about getting plugin information
	if( 'plugin_information' !== $action ) {
		return false;
	}

	$plugin_slug = 'apg-tools'; // we are going to use it in many places in this function

	// do nothing if it is not our plugin
	if( $plugin_slug !== $args->slug ) {
		return false;
	}

	// trying to get from cache first
	if( false == $remote = get_transient( 'apg_update_' . $plugin_slug ) ) {

		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 'https://ip1.kingspace.net/storage/update/info.json', array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) )
		);

		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			set_transient( 'apg_update_' . $plugin_slug, $remote, 43200 ); // 12 hours cache
		}

	}

	if( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {

		$remote = json_decode( $remote['body'] );
		$res = new stdClass();

		$res->name = $remote->name;
		$res->slug = $plugin_slug;
		$res->version = $remote->version;
		$res->tested = $remote->tested;
		$res->requires = $remote->requires;
		$res->author = $remote->author;
		$res->author_profile = $remote->author_profile;
		$res->download_link = $remote->download_url;
		$res->trunk = $remote->download_url;
		$res->requires_php = $remote->requires_php;
		$res->last_updated = $remote->last_updated;
		$res->sections = array(
			'description' => $remote->sections->description,
			'installation' => $remote->sections->installation,
			'changelog' => $remote->sections->changelog
			// you can add your custom sections (tabs) here
		);

		// in case you want the screenshots tab, use the following HTML format for its content:
		// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
		if( !empty( $remote->sections->screenshots ) ) {
			$res->sections['screenshots'] = $remote->sections->screenshots;
		}

		$res->banners = array(
			'low' => $remote->banners->low,
			'high' => $remote->banners->high
		);
		return $res;

	}

	return false;

}

add_filter('site_transient_update_plugins', 'apg_push_update' );

function apg_push_update( $transient ){

	if ( empty($transient->checked ) ) {
    return $transient;
  }

	// trying to get from cache first, to disable cache comment 10,20,21,22,24
	if( false == $remote = get_transient( 'apg_upgrade_apg-tools' ) ) {

		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 'https://ip1.kingspace.net/storage/update/info.json', array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) )
		);

		if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
			set_transient( 'apg_upgrade_apg-tools', $remote, 43200 ); // 12 hours cache
		}

	}

	if( $remote ) {

		$remote = json_decode( $remote['body'] );

		// your installed plugin version should be on the line below! You can obtain it dynamically of course 
		if( $remote && version_compare( '1.0', $remote->version, '<' ) && version_compare($remote->requires, get_bloginfo('version'), '<' ) ) {
			$res = new stdClass();
			$res->slug = 'apg-tools';
			$res->plugin = 'apg-tools/apg-tools.php'; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
			$res->new_version = $remote->version;
			$res->tested = $remote->tested;
			$res->package = $remote->download_url;
      $transient->response[$res->plugin] = $res;
      //$transient->checked[$res->plugin] = $remote->version;
    }

	}
  return $transient;
}