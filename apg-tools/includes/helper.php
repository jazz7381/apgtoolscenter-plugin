<?php

function dd(...$params){
  echo '<pre>';
  var_dump($params);die;
  echo '</pre>';
}

function apg_activate_plugin( $plugin ) {
  $current = get_option( 'active_plugins' );
  $plugin = plugin_basename( trim( $plugin ) );
  if ( !in_array( $plugin, $current ) ) {
    $current[] = $plugin;
    sort( $current );
    do_action( 'activate_plugin', trim( $plugin ) );
    update_option( 'active_plugins', $current );
    do_action( 'activate_' . trim( $plugin ) );
    do_action( 'activated_plugin', trim( $plugin) );
  }
  return null;
}

function apg_view($path, $data = []){
  if(!empty($data)){
    extract($data);
  }
  if(file_exists($path)){
    require_once($path);
  }
}

function redirect_back(){
  header("location: {$_SERVER['HTTP_REFERER']}");
}

function chunkDownloadFile($srcName, $dstName, $chunkSize = 1, $returnbytes = true) {
  $chunksize = $chunkSize*(1024*1024); // How many bytes per chunk
  $data = '';
  $bytesCount = 0;
  $handle = fopen($srcName, 'rb');
  $fp = fopen($dstName, 'w');
  if ($handle === false) {
    return false;
  }
  while (!feof($handle)) {
    $data = fread($handle, $chunksize);
    fwrite($fp, $data, strlen($data));
    if ($returnbytes) {
        $bytesCount += strlen($data);
    }
  }
  $status = fclose($handle);
  fclose($fp);
  if ($returnbytes && $status) {
    return $bytesCount; // Return number of bytes delivered like readfile() does.
  }
  return $status;
}