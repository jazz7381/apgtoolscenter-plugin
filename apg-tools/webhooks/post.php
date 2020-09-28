<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
try {
  if($post['token_auth'] == apg_config()->token_auth){
    $realPost = $post['post'];
    unset($realPost['content']);
    $imageBase64 = getImageHtml($post['post']['content']);
    $attach_id = apg_base64_image_upload($realPost, $imageBase64);
    //  create category
    $catId = wp_insert_category([
      'cat_name' => $post['post']['category']
    ]);
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

    set_post_thumbnail($postId, $attach_id);
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