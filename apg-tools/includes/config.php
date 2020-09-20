<?php

require_once(__DIR__.'/../../../../wp-load.php');

define('APGPATH', ABSPATH.'wp-content/plugins/apg-tools');

$config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$config['base_url'] .= "://".$_SERVER['HTTP_HOST'];
$config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$config['base_url'] = str_replace('wp-admin', 'wp-content/plugins/apg-tools', $config['base_url']);

function base_url($path = NULL){
  global $config;
  return $config['base_url'].$path;
}

function apg_config(){
  $json = file_get_contents(APGPATH.'/apg-tools.json', true);
  return json_decode($json);
}