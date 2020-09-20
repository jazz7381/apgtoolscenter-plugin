<?php
ini_set('max_execution_time', 0);
try {
  define( 'WP_ADMIN', TRUE );
  define( 'WP_NETWORK_ADMIN', TRUE );
  define( 'WP_USER_ADMIN', TRUE );
  require_once('includes/config.php');
  require_once(ABSPATH.'wp-admin/includes/admin.php');
  require_once(ABSPATH.'wp-admin/includes/file.php');
  require_once(ABSPATH.'wp-admin/includes/plugin.php');
  require_once(ABSPATH.'wp-content/plugins/apg-tools/includes/helper.php');

  $post = $_POST;

  if($post['token_auth'] == apg_config()->token_auth){
    foreach($post['plugins'] as $key => $value){
      if($value['is_external_link'] == 0){
        $file_url = $post['url_download']."/{$value['file_name']}";
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
      apg_activate_plugin($value['slug'].'/'.$value['core_file']);
    }
    return true;
  }else{
    return http_response_code(403);
  }
} catch (\Throwable $th) {
  file_put_contents('log.txt', $th);
}