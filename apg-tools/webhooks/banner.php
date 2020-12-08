<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
// default error response
$response = [
  'status'  => FALSE,
  'message' => 'Failed to get response.'
];
// return false if adning not installed
if ( ! class_exists( 'ADNI_CPT' ) ) {
  echo json_encode([
    'status' => FALSE,
    'message' => 'Adning plugins not installed or APG Tools not support current adning version.'
  ]);
  die;
}

try {
  // get post body
  $realPost = $post['post'];
  $bannerIds = [];
  foreach($realPost['banner'] as $postbanner){
    // init all post data
    $bannerTitle = $postbanner['title'];
    $bannerWidth  = $postbanner['image']['width'];
    $bannerHeight = $postbanner['image']['height'];
    $source = $postbanner['image']['source'];
    $size = $bannerWidth.'x'.$bannerHeight;
    $mime = $postbanner['image']['mime'];
    $extension = $postbanner['image']['extension'];
    $bannerUrl = $postbanner['image']['url'];
    
    if ($existsBanner = get_page_by_title( $bannerTitle, OBJECT, 'adni_banners' )) {
      $bannerId                        = $existsBanner->ID;
      $argsBanner                      = get_post_meta($bannerId, '_adning_args', array());
      $uploadedPath                    = apg_base64_upload(['id'=>$bannerId,'title'=>$bannerTitle,'mime'=>$mime,'extension'=>$extension], $source);
      $argsBanner[0]['status']         = 'active';
      // $argsBanner[0]['size']           = 'custom';
      // $argsBanner[0]['size_w']         = $bannerWidth;
      // $argsBanner[0]['size_h']         = $bannerHeight;
      $argsBanner[0]['banner_url']     = $bannerUrl;
      $argsBanner[0]['banner_content'] = "<div class='_ning_elmt'><img src='{$uploadedPath}'></div>";
      // return ID banner
      $bannerIds[] = $bannerId;
    }else{
      // insert banner post and get ID
      $bannerId = ADNI_CPT::add_update_post([
        'post_type' => ADNI_CPT::$banner_cpt,
        'title' => wp_strip_all_tags($bannerTitle),
        'responsive' => 1,
        'df_show_desktop' => 1,
        'df_show_tablet' => 1,
        'df_show_mobile' => 1
      ]);
      $uploadedPath = apg_base64_upload(['id'=>$bannerId,'title'=>$bannerTitle,'mime'=>$mime,'extension'=>$extension], $source);
      // update size of banner
      update_post_meta($bannerId, '_adning_size', $size);
      // get banner args
      $argsBanner                      = get_post_meta($bannerId, '_adning_args', array());
      // $argsBanner[0]['size']           = 'custom';
      // $argsBanner[0]['size_w']         = $bannerWidth;
      // $argsBanner[0]['size_h']         = $bannerHeight;
      $argsBanner[0]['banner_url']     = $bannerUrl;
      $argsBanner[0]['banner_content'] = "<div class='_ning_elmt'><img src='{$uploadedPath}'></div>";
      // return ID banner
      $bannerIds[] = $bannerId;
    }
    // update adning args meta
    update_post_meta($bannerId, '_adning_args', $argsBanner[0]);
  }

  $adzonePosts = get_posts([
    'numberposts' => 1,
    'order' => 'ASC',
    'post_type' => ADNI_CPT::$adzone_cpt
  ]);
  // check if any adzone
  if(count($adzonePosts) > 0){

    $adzoneId                        = $adzonePosts[0]->ID;
    // get adzone args
    $argsAdzone                      = get_post_meta($adzoneId, '_adning_args', array());
    $bannerAdzoneIds                 = $argsAdzone[0]['linked_banners'];
    // check if banner already exists
    foreach($bannerIds as $bannerId){
      if(!in_array($bannerId, $bannerAdzoneIds)){
        $argsAdzone[0]['linked_banners'][] = $bannerId;
      }
    }

  }else{
    // Create adzone post
    $adzoneId = ADNI_CPT::add_update_post([
      'post_type' => ADNI_CPT::$adzone_cpt,
      'title' => 'float',
      'responsive' => 1,
      'df_show_desktop' => 1,
      'df_show_tablet' => 1,
      'df_show_mobile' => 1
    ]);
    // get adzone args
    $argsAdzone = get_post_meta($adzoneId, '_adning_args', array());
    // update args
    $argsAdzone[0]['random_order']   = 1;
    $argsAdzone[0]['load_single']    = 1;
    $argsAdzone[0]['adzone_size']    = $size;
    $argsAdzone[0]['adzone_size_w']  = $bannerWidth;
    $argsAdzone[0]['adzone_size_h']  = $bannerHeight;
    $argsAdzone[0]['linked_banners'] = $bannerIds;
    $argsAdzone[0]['positioning']    = 'popup';
  }
  // update adning args meta
  update_post_meta($adzoneId, '_adning_args', $argsAdzone[0]);
  
  // response
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