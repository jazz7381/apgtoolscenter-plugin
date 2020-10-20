<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
try {
  if($post['token_auth'] == apg_config()->token_auth){
    // assign real post to variable
    $realPost = $post['post'];

    if($realPost['type'] == 'bestia'){
      // start ---------------------- bestia
      $catIds = [];
      // loop categories name and get ids
      foreach($realPost['category'] as $value){
        $catIds[] = wp_create_category($value);
      }
      // set to dummy content if empty to prevent error by wordpress
      if(empty($realPost['content'])){
        $realPost['content'] = '<br>';
      }
      // Create post object
      $my_post = array(
        'post_title'    => wp_strip_all_tags($realPost['title']),
        'post_content'  => $realPost['content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $catIds,
        'tags_input'    => $realPost['tag'],
        'meta_input'    => array(
          'rank_math_focus_keyword' => $realPost['focus_keyword'],
          'video_url'               => $realPost['video_source']['source_content'],
        )
      );
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // check img thumbnail type
      if($realPost['image_source']['source_type'] == 'file'){
        $attach_id = apg_base64_image_upload($realPost, $realPost['image_source']['source_content']);
      }else{
        $attach_id = apg_base64_image_download($realPost['title'], $realPost['image_source']['source_content']);
      }
      set_post_thumbnail($postId, $attach_id);
      // end ---------------------- bestia
    }elseif($realPost['type'] == 'indoxximovie'){
      // start ---------------------- indoxximovie
      // call curl library
      require_once(APGPATH.'/lib/curl.php');
      // call translate library
      require_once(APGPATH.'/lib/translator.php');
      // instan object from library
      $curl = new CurlWrapper;
      //array category container
      $catIds = [];
      // loop categories name and get ids
      foreach($realPost['category'] as $value){
        $catIds[] = wp_create_category($value);
      }
      // end ---------------------- indoxximovie
    }
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