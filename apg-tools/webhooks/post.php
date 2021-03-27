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
    // Post Title
    $postTitle = wp_strip_all_tags( $post['post']['title'] );
    // get image source
    $image = $realPost['image_source']['source'];
    // Create post object
    $my_post = array(
      'post_title'    => $postTitle,
      'post_content'  => $post['post']['content'],
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_category' => $catIds,
      'tags_input'    => $realPost['tag'],
      'meta_input'    => [
        'rank_math_focus_keyword' => $postTitle,
      ]
    );

    if ($realPost['image_source']['type'] == 'url') {
      $my_post['meta_input']['fifu_image_url'] = $image;
      $my_post['meta_input']['fifu_image_alt'] = $postTitle;
    }

    // Insert the post into the database
    $postId = apg_wp_insert_post($my_post);
    // switch case
    switch ($realPost['image_source']['type']) {
      case 'content':
        if (substr($image, 0, 5) == 'data:') {
          $attach_id = apg_base64_image_upload($my_post['title'], $image, $postId);
        }else{
          $attach_id = apg_base64_image_download($my_post['title'], $image, $postId);
        }
        break;
      case 'url':
        $attachment = array(
          'post_author' => 77777,
          'post_mime_type' => 'image/jpeg',
          'post_title' => $postTitle,
          'post_content' => '',
          'post_status' => 'inherit',
          'post_name' => '',
          'guid' => $image
        );
        $attach_id = wp_insert_attachment( $attachment, $filename, $postId );
        break;
      case 'base64':
        $attach_id = apg_base64_image_upload($my_post['title'], $image, $postId);
        break;
    }
    // set attachment thumbnail to post
    set_post_thumbnail($postId, $attach_id);
  }elseif($realPost['type'] == 'schedule'){// schedule post
    // get image
    $image  = $realPost['image'];
    $imageType = $realPost['image_type'];
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

    if ($imageType == 'url') {
      $my_post['meta_input']['fifu_image_url'] = $image;
      $my_post['meta_input']['fifu_image_alt'] = $postTitle;
    }

    // Insert the post into the database
    $postId             = apg_wp_insert_post($my_post);

    if ($imageType == 'url') {
      $attachment = array(
        'post_author' => 77777,
        'post_mime_type' => 'image/jpeg',
        'post_title' => $postTitle,
        'post_content' => '',
        'post_status' => 'inherit',
        'post_name' => '',
        'guid' => $image
      );
      $attach_id = wp_insert_attachment( $attachment, $filename, $postId );
    } else {
      // set image as atachment of post
      $attach_id          = apg_base64_image_upload($my_post['post_title'], $image, $postId);
    }
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