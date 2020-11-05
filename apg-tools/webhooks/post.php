<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
$response = [
  'status'  => FALSE,
  'message' => 'Failed to get response.'
];
try {
  if($post['token_auth'] == apg_config()->token_auth){
    $realPost = $post['post'];
    // common bulk post
    if($realPost['type'] == 'common'){
      unset($realPost['content']);
      $imageBase64 = getImageHtml($post['post']['content']);
      $attach_id = apg_base64_image_upload($realPost, $imageBase64);
      $catId = wp_create_category($post['post']['category']);
      // Create post object
      $my_post = array(
        'post_title'    => wp_strip_all_tags( $post['post']['title'] ),
        'post_content'  => $post['post']['content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => array($catId)
      );
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // set attachment thumbnail to post
      set_post_thumbnail($postId, $attach_id);
    }elseif($realPost['type'] == 'schedule'){// schedule post
      // get image
      $imageBase64  = $realPost['image'];
      // set image as atachment of post
      $attach_id    = apg_base64_image_upload($realPost, $imageBase64);
      // get category
      $arrayCatIds = [];
      foreach($realPost['category'] as $value){
        $arrayCatIds[] = wp_create_category($value);
      }
      // Create post object
      $my_post = array(
        'post_title'    => wp_strip_all_tags( $post['post']['title'] ),
        'post_content'  => $post['post']['content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $arrayCatIds,
        'tags_input'    => $realPost['tag'],
      );
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // set attachment thumbnail to post
      set_post_thumbnail($postId, $attach_id);
    }
    $response['status']   = TRUE;
    $response['message']  = 'Success';
  }else{
    $response['status']   = FALSE;
    $response['message']  = 'Token Invalid.';
  }
} catch (\Throwable $th) {
  $response['status']   = FALSE;
  $response['message']  = 'Error : '.$th->getMessage();
  file_put_contents('log.txt', $th->getMessage());
}
// return response as json
echo json_encode($response);
exit;