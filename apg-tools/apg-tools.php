<?php
/**
 * @package APG_Tools
 * @version 1.3.2
 */
/*
Plugin Name: APG Tools
Plugin URI: https://www.asiapowergames.com
Description: Tools used to control multiple wordpress site specific for APG wordpress site.
Author: Jazz
Version: 1.3.2
Author URI: https://www.github.com/7381jazz
*/

require_once('includes/config.php');
require_once(ABSPATH.'wp-content/plugins/apg-tools/includes/function.php');
require_once(ABSPATH.'wp-admin/includes/admin.php');
require_once(ABSPATH.'wp-admin/includes/file.php');
require_once(ABSPATH.'wp-admin/includes/plugin.php');
require_once(ABSPATH.'wp-includes/theme.php');
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

// ------------------------- API Request ---------------------------- //
add_action( 'rest_api_init', 'apg_webhooks' );

function apg_webhooks(){
	register_rest_route('apg/v1', 'webhooks', [
		"methods" 	=> "post",
		"callback"	=> "apg_response"
	]);
}

function apg_response(){
	$post = $_POST;
	if(!empty($post['token_auth']) && $post['token_auth'] == apg_config()->token_auth){
		switch ($post['webhooks_type']) {
			case 'plugin':
				require_once(APGPATH.'/webhooks/plugin.php');
			break;
			case 'theme':
				require_once(APGPATH.'/webhooks/theme.php');
				break;
			case 'post':
				require_once(APGPATH.'/webhooks/post.php');
				break;
			case 'post_video':
				require_once(APGPATH.'/webhooks/post_video.php');
				break;
			default:
				return http_response_code(403);
				break;
		}
	}
}