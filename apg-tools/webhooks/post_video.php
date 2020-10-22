<?php
ini_set("memory_limit",'-1');
ini_set('max_execution_time', 0);
ini_set('auto_detect_line_endings', true);
try {
  if($post['token_auth'] == apg_config()->token_auth){
    // assign real post to variable
    $realPost = $post['post'];
    // if theme wp is bestia
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
          'video_duration'          => $realPost['video_duration']
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
      // set performer
      wp_set_object_terms( $postId, $realPost['performer'], 'performer');
      // end ---------------------- bestia
    }elseif($realPost['type'] == 'indoxximovie'){ // if theme wp is indoxximovie
      // start ---------------------- indoxximovie
      // call curl library
      require_once(APGPATH.'/lib/curl.php');
      // call translate library
      require_once(APGPATH.'/lib/translator.php');
      // instan object from library
      $curl = new CurlWrapper;
      // get movie id
      $movieId = $realPost['movie_id'];
      // get tmdb api key
      $tmdbApiKey = $post['tmdb_api_key'];
      // get omdb api key
      $omdbApiKey = $post['omdb_api_key'];
      // get all response type
      $responseDetail = json_decode($curl->get("https://api.themoviedb.org/3/movie/$movieId?api_key=$tmdbApiKey"));
      $responseImage  = json_decode($curl->get("https://api.themoviedb.org/3/movie/$movieId/images?api_key=$tmdbApiKey"));
      $responseVideo  = json_decode($curl->get("https://api.themoviedb.org/3/movie/$movieId/videos?api_key=$tmdbApiKey"));
      $responseCredit = json_decode($curl->get("https://api.themoviedb.org/3/movie/$movieId/casts?api_key=$tmdbApiKey"));
      $responseOmdb   = json_decode($curl->get("https://www.omdbapi.com/?apikey=$omdbApiKey&i=$movieId"));
      // get directors
      $directors = [];
      foreach($responseCredit->crew as $value){
        if($value->job == 'Director'){
          $directors[] = $value->name;
        }
      }
      // get actors
      $actors = [];
      foreach($responseCredit->cast as $key => $value){
        if($key == 0 || $key == 1 || $key == 2 || $key == 3 || $key == 4 || $key == 5 || $key == 6 || $key == 7){
          $actors[] = $value->name;
        }
      }
      // get countries
      $countries = [];
      foreach(explode(',', $responseOmdb->Country) as $value){
        $countries[] = $value;
      }
      // instant translator object
      $translator = new Translator;
      if(!empty($realPost['content'])){
        $realPost['content'] .= "\n\n".$translator->translate($responseDetail->overview);
      }else{
        $realPost['content'] = $translator->translate($responseDetail->overview);
      }
      // get category from api as genre
      $catIds = [];
      foreach($responseDetail->genres as $value){
        $catIds[] = wp_create_category($value->name);
      }
      // release year
      $releaseYear   = $responseOmdb->Year;
      // get quality
      $quality = [];
      if(empty($realPost['quality'])){
        $quality[] = get_option('d_quality');
      }else{
        $quality = $realPost['quality'];
      }
      // video source
      $videoSources = [];
      foreach($realPost['video_source'] as $key => $value){
        $num = $key + 1;
        $videoSources[] = [
          'name'    => 'SERVER VIP'.$num,
          'idioma'  => 'id',
          'select'  => 'iframe',
          'url'     => $value,
          'mid'     => $num
        ];
      }
      // Create post object
      $my_post = array(
        'post_title'    => wp_strip_all_tags($responseDetail->title),
        'post_content'  => $realPost['content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $catIds,
        'tags_input'    => $realPost['tag'],
        'meta_input'    => array(
          'rank_math_focus_keyword' =>  wp_strip_all_tags($responseDetail->title),
          'repeatable_fields'       =>  $videoSources,
          'idtmdb'                  =>  $responseDetail->id,
          // imdb movie id
          'Checkbx2'                =>  $responseDetail->imdb_id,
          // meta atachment
          'poster_url'              =>  'https://image.tmdb.org/t/p/w185'.$responseDetail->poster_path,
          'fondo_player'            =>  'https://image.tmdb.org/t/p/w780'.$responseDetail->backdrop_path,
          'youtube_id'              =>  "[{$responseVideo->results[0]->key}]",
          // imdb data
          'imdbRating'              =>  $responseOmdb->imdbRating,
          'imdbVotes'               =>  $responseOmdb->imdbVotes,
          'Rated'                   =>  $responseOmdb->Rated,
          'Country'                 =>  $responseOmdb->Country,
          'Title'                   =>  $responseDetail->original_title,
          'tagline'                 =>  $responseDetail->tagline,
          'release_date'            =>  $responseDetail->release_date,
          'vote_average'            =>  $responseDetail->vote_average,
          'vote_count'              =>  $responseDetail->vote_count,
          'Runtime'                 =>  date('H:i:00', mktime(0,$responseDetail->runtime))
        )
      );
      foreach($responseImage->backdrops as $value){
        $my_post['meta_input']['imagenes']    .= "https://image.tmdb.org/t/p/w300/$value->file_path\n";
      }
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // insert thumbnail
      $attach_id = apg_base64_image_download($realPost['title'], $my_post['meta_input']['poster_url']);
      // set attachment id thumbnail to post
      set_post_thumbnail($postId, $attach_id);
      // set release year
      wp_set_object_terms( $postId, array($releaseYear), 'release-year');
      // set quality
      wp_set_object_terms( $postId, $quality, 'quality');
      // set director
      wp_set_object_terms( $postId, $directors, 'director');
      // set actor
      wp_set_object_terms( $postId, $actors, 'stars');
      // set country
      wp_set_object_terms( $postId, $countries, 'country');
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