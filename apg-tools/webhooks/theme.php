<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
try {
  if($post['token_auth'] == apg_config()->token_auth){
    $theme = $post['theme'];
    
    if($theme['is_external_link'] == 0){
      $file_url = $post['url_download']."/{$theme['file_name']}";
    }else{
      $file_url = $theme['external_link'];
    }
    $bytes = chunkDownloadFile($file_url, $theme['file_name'], 300);
    // check if folder is exists
    if(file_exists(ABSPATH.'wp-content/plugins/'.$value['slug']) && !empty($value['slug'])){
      WP_Filesystem();
      global $wp_filesystem;
      // delete directory path
      $wp_filesystem->rmdir(ABSPATH.'wp-content/themes/'.$value['slug'], true);
    }
    // extract plugin to plugins folder
    $zip = new ZipArchive;
    $zip->open($theme['file_name']);
    $zip->extractTo(ABSPATH.'wp-content/themes/');
    $zip->close();
    // remove zip file
    unlink($theme['file_name']);
    // swithc theme
    switch_theme($theme['slug']);
    // return response as json
    echo json_encode([
      'status' => TRUE,
      'message' => 'Success'
    ]);
  }else{
    return http_response_code(403);
  }
} catch (\Throwable $th) {
  file_put_contents('log.txt', $th);
}