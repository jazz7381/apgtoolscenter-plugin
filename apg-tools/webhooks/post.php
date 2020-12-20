<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
$response = [
  'status'  => FALSE,
  'message' => 'Failed to get response.'
];
try {
  $realPost = $post['post'];
  // common bulk post
  if($realPost['type'] == 'common'){
    unset($realPost['content']);
    $catIds = [];
    foreach($realPost['category'] as $category){
      $catIds[] = wp_create_category($category);
    }
    // Create post object
    $my_post = array(
      'post_title'    => wp_strip_all_tags( $post['post']['title'] ),
      'post_content'  => $post['post']['content'],
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_category' => $catIds,
      'tags_input'    => $realPost['tag'],
      'meta_input'    => [
        'rank_math_focus_keyword' => wp_strip_all_tags( $post['post']['title'] )
      ]
    );
    // Insert the post into the database
    $postId = apg_wp_insert_post($my_post);
    // get image source
    $image = $realPost['image_source']['source'];
    // switch case
    switch ($realPost['image_source']['type']) {
      case 'content':
        if (substr($image, 0, 5) == 'data:') {
          $attach_id = apg_base64_image_upload($my_post['title'], $image);
        }else{
          $attach_id = apg_base64_image_download($my_post['title'], $image);
        }
        break;
      case 'url':
        $attach_id = apg_base64_image_download($my_post['title'], $image);
        break;
      case 'base64':
        $attach_id = apg_base64_image_upload($my_post['title'], $image);
        break;
    }
    // set attachment thumbnail to post
    set_post_thumbnail($postId, $attach_id);
  }elseif($realPost['type'] == 'schedule'){// schedule post
    // get image
    $imageBase64  = $realPost['image'];
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
      'meta_input'    => [
        'rank_math_focus_keyword' => wp_strip_all_tags( $post['post']['title'] )
      ]
    );
    // Insert the post into the database
    $postId             = apg_wp_insert_post($my_post);
    // set image as atachment of post
    $attach_id          = apg_base64_image_upload($my_post['post_title'], $imageBase64);
    // set attachment thumbnail to post
    set_post_thumbnail($postId, $attach_id);
  }
  $response['status']   = TRUE;
  $response['message']  = 'Success';
} catch (\Throwable $th) {
  $response['status']   = FALSE;
  $response['message']  = 'Error : '.$th->getMessage();
  file_put_contents('log.txt', $th->getMessage());
}
// return response as json
echo json_encode($response);
exit;