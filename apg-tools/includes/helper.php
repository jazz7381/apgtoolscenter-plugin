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