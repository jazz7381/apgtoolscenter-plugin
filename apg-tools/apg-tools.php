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

define( 'WPRP_PLUGIN_SLUG', 'apgtools' );
define( 'WPRP_PLUGIN_BASE',  plugin_basename(__FILE__) );
define( 'WPRP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPR_API_URL', 'http://localhost/testing/apg-tools/api/json.php' );
define( 'WPR_WEBHOOK', 'http://localhost/testing/apg-tools/webhook.php' );

define('WP_DEBUG', 1);