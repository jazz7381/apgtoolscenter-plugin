<?php

define( 'WP_ADMIN', TRUE );
define( 'WP_NETWORK_ADMIN', TRUE );
define( 'WP_USER_ADMIN', TRUE );

require_once('../../../wp-load.php');
require_once(ABSPATH.'wp-admin/includes/admin.php');
require_once(ABSPATH.'wp-admin/includes/file.php');
require_once(ABSPATH.'wp-admin/includes/plugin.php');

try {
  $post = $_POST;
  $plugins = [];
  // file_put_contents('logs.txt', json_encode($post));die;
  foreach($post['plugins'] as $key => $value){
    if($value['is_external_link'] == 0){
      $file_url = $post['url_download']."/{$value['id']}";
    }else{
      $file_url = $value['external_link'];
    }
    $tmp_file = download_url($file_url);
    copy($tmp_file, $value['file_name']);
    unlink($tmpfile);
    // extract plugin to plugins folder
    $zip = new ZipArchive;
    $zip->open($value['file_name']);
    $zip->extractTo(ABSPATH.'wp-content/plugins/');
    $zip->close();
    // remove zip file
    unlink($value['file_name']);
    // activate plugin
    // $res = activate_plugin(ABSPATH.'wp-content/plugins/'.$value['slug'].'/'.$value['core_file']);
    $plugins[] = ABSPATH.'wp-content/plugins/'.$value['slug'].'/'.$value['core_file'];
  }
  // activate plugin
  $res = activate_plugins($plugins);
  // get error response if there
  if(is_wp_error($result)){
    file_put_contents("logs.txt", $res->get_error_messages());
  }
} catch (\Throwable $th) {
  file_put_contents('log.txt', $th);
}