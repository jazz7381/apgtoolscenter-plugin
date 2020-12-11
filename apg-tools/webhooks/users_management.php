<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
// default error response
$response = [
  'status'  => FALSE,
  'message' => 'Failed to get response.'
];

try {
  // get post body
  $realPost = $post['post'];

  switch ($realPost['type']) {
    case 'get':
      $response['data']     = get_users();
      break;
    case 'edit':
      $user = get_user_by('id', $realPost['id']);
      $user->data->user_login = $realPost['username'];
      $user->data->user_email = $realPost['email'];
      $user->data->user_nicename = $realPost['user_nicename'];
      $user->data->display_name = $realPost['display_name'];

      if(!empty($realPost['password'])){
        $user->data->user_pass = wp_hash_password( $realPost['password'] );
      }

      if(!in_array($realPost['role'], $user->roles)){
        $user->set_role($realPost['role']);
      }

      $response['message']     = wp_insert_user($user);
      $response['message']  = 'Success';
      break;
    case 'add':
      $user = new WP_User;
      $user->data->user_login = $realPost['username'];
      $user->data->user_email = $realPost['email'];

      $idUser = wp_insert_user($user);

      $userNew = get_user_by('id', $idUser);
      $userNew->data->user_pass = wp_hash_password( $realPost['password'] );
      $userNew->set_role($realPost['role']);
      wp_insert_user($userNew);
      
      $response['message']     = 'success';
      break;
    case 'delete':
      $count = 0;
      foreach($realPost['users'] as $userId){
        if(wp_delete_user($userId, $realPost['reassign'])){
          $count++;
        }
      }
      $response['message']  = "Success delete $count user.";
      break;
    default:
      # code...
      break;
  }
  // response
  $response['status']   = TRUE;
} catch (\Throwable $th) {
  $response['status']   = FALSE;
  $response['message']  = 'Error : '.$th->getMessage();
  file_put_contents('log.txt', $th->getMessage());
}
// return response as json
echo json_encode($response);
exit;