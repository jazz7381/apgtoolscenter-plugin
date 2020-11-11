<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
$response = [
  'status'  => FALSE,
  'message' => 'Failed to get response.'
];
try {
  $params = $post['params'];
  // edit post
  if($post['type'] == 'edit'){  
    // convert special character to match with wordpress default
    require_once(ABSPATH.'wp-config.php');
    // get global class
    global $wpdb;
    // get posts
    if($params['target'] == 'content'){

      if($params['type'] == 'latest'){
        // make where clause condition
        $tailWhere = "ORDER BY id DESC LIMIT {$params['latest_value']}";
      }else{
        // get the check point start and end
        $from = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_from']}" );
        $to = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_to']}" );
        // get latest 'from' and latest 'to'
        $realFrom = $from[count($from)-1];
        $realTo   = $to[count($to)-1];
        // make where clause condition
        $tailWhere = "AND ID >= {$realTo->ID} AND ID <= {$realFrom->ID}";
      }
      // query to get result
      $results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%{$params['keyword']}%' $tailWhere", OBJECT );

    }else{

      if($params['type'] == 'latest'){
        // make where clause condition
        $tailWhere = "ORDER BY id DESC LIMIT {$params['latest_value']}";
      }else{
        // get the check point start and end
        $from = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_from']}" );
        $to = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_to']}" );
        // get latest 'from' and latest 'to'
        $realFrom = $from[count($from)-1];
        $realTo   = $to[count($to)-1];
        // make where clause condition
        $tailWhere = "AND ID >= {$realTo->ID} AND ID <= {$realFrom->ID}";
      }
      $results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%{$params['keyword']}%' $tailWhere", OBJECT );

    }
    // loop if result
    if(count($results) > 0){
      $countSuccess = 0;
      foreach($results as $value){
        $replacer = $params['replace_with'][array_rand($params['replace_with'])];
        // conditional branch
        if($params['target'] == 'content'){
          $data = [
            'post_content'  =>  str_replace($params['keyword'], $replacer, $value->post_content)
          ];
        }else{
          $data = [
            'post_title'  =>  str_replace($params['keyword'], $replacer, htmlspecialchars_decode($value->post_title)),
            'post_name'   =>  str_replace(sanitize_title($params['keyword']), sanitize_title($replacer), htmlspecialchars_decode($value->post_name)),
            'guid'        =>  str_replace(sanitize_title($params['keyword']), sanitize_title($replacer), htmlspecialchars_decode($value->guid))
          ];
        }
        $wpdb->update($wpdb->posts, $data, ['ID' => $value->ID]);
        $countSuccess++;
      }
    }
    $message = 'edited';
  }elseif($post['type'] == 'delete'){
    // get global class
    global $wpdb;
    // get posts
    if($params['target'] == 'content'){

      if($params['type'] == 'latest'){
        // make where clause condition
        $tailWhere = "ORDER BY id DESC LIMIT {$params['latest_value']}";
      }else{
        // get the check point start and end
        $from = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_from']}" );
        $to = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_to']}" );
        // get latest 'from' and latest 'to'
        $realFrom = $from[count($from)-1];
        $realTo   = $to[count($to)-1];
        // make where clause condition
        $tailWhere = "AND ID >= {$realTo->ID} AND ID <= {$realFrom->ID}";
      }
      // query to get result
      $results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%{$params['keyword']}%' $tailWhere", OBJECT );

    }else{

      if($params['type'] == 'latest'){
        // make where clause condition
        $tailWhere = "ORDER BY id DESC LIMIT {$params['latest_value']}";
      }else{
        // get the check point start and end
        $from = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_from']}" );
        $to = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_author != 0 ORDER BY ID DESC LIMIT {$params['range_post_to']}" );
        // get latest 'from' and latest 'to'
        $realFrom = $from[count($from)-1];
        $realTo   = $to[count($to)-1];
        // make where clause condition
        $tailWhere = "AND ID >= {$realTo->ID} AND ID <= {$realFrom->ID}";
      }
      $results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%{$params['keyword']}%' $tailWhere", OBJECT );

    }
    // loop if result
    if(count($results) > 0){
      $countSuccess = 0;
      foreach($results as $value){
        wp_delete_post($value->ID, true);
        $countSuccess++;
      }
    }
    $message = 'deleted';
  }
  $response['status']   = TRUE;
  $response['message']  = "Success. $countSuccess post $message.";
} catch (\Throwable $th) {
  $response['status']   = FALSE;
  $response['message']  = 'Error : '.$th->getMessage();
  file_put_contents('log.txt', $th->getMessage());
}
// return response as json
echo json_encode($response);
exit;