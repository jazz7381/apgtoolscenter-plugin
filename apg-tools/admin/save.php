<?php

require_once('../includes/config.php');
define('APGPATH', ABSPATH.'wp-content/plugins/apg-tools');
require_once(APGPATH.'/includes/helper.php');

$post = $_POST;

$array = array(
  "token_auth" => $post['token-auth']
);

file_put_contents(APGPATH.'/apg-tools.json', json_encode($array));

redirect_back();