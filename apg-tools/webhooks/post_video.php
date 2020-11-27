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
      if(!empty($realPost['sponsor_text'])){
        $my_post['meta_input']['sponsor_link_txt'] = $realPost['sponsor_text'];
      }
      if(!empty($realPost['sponsor_url'])){
        $my_post['meta_input']['sponsor_link_url'] = $realPost['sponsor_url'];
      }
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // check img thumbnail type
      if($realPost['image_source']['source_type'] == 'file'){
        $attach_id = apg_base64_image_upload($my_post['post_title'], $realPost['image_source']['source_content']);
      }else{
        $attach_id = apg_base64_image_download($realPost['title'], $realPost['image_source']['source_content']);
      }
      set_post_thumbnail($postId, $attach_id);
      // set performer
      if(!empty($realPost['performer'])){
        wp_set_object_terms( $postId, $realPost['performer'], 'performer');
      }
      // set channel
      if(!empty($realPost['channel'])){
        wp_set_object_terms( $postId, $realPost['channel'], 'channel');
      }
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
      // video type
      $videoType = 'movie';
      if($realPost['video_type'] == 'series'){
        $videoType = 'tv';
      }
      // get all response type
      $responseDetail = json_decode($curl->get("https://api.themoviedb.org/3/$videoType/$movieId?api_key=$tmdbApiKey"));
      $responseImage  = json_decode($curl->get("https://api.themoviedb.org/3/$videoType/$movieId/images?api_key=$tmdbApiKey"));
      $responseVideo  = json_decode($curl->get("https://api.themoviedb.org/3/$videoType/$movieId/videos?api_key=$tmdbApiKey"));
      $responseCredit = json_decode($curl->get("https://api.themoviedb.org/3/$videoType/$movieId/credits?api_key=$tmdbApiKey"));
      // post title
      $postTitle = $responseDetail->name;
      // poster url
      $posterUrl = 'https://image.tmdb.org/t/p/w185'.$responseDetail->poster_path;
      // get directors
      $directors = [];
      if($realPost['video_type'] == 'movie'){
        foreach($responseCredit->crew as $value){
          if($value->job == 'Director'){
            $directors[] = $value->name;
          }
        }
      }else{
        $responseSeason = json_decode($curl->get("https://api.themoviedb.org/3/$videoType/$movieId/season/{$realPost['season']}?api_key=$tmdbApiKey"));
        $postTitle = $postTitle.' '.$responseSeason->name;
        $posterUrl = 'https://image.tmdb.org/t/p/w185'.$responseSeason->poster_path;
        foreach($responseDetail->created_by as $value){
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
      // get quality
      $quality = $realPost['quality'];
      // Create post object
      $my_post = [
        'post_content'  => $realPost['content'],
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_category' => $catIds,
        'tags_input'    => $realPost['tag'],
        'meta_input'    => [
          // meta atachment
          'poster_url'              =>  $posterUrl,
          'fondo_player'            =>  'https://image.tmdb.org/t/p/w780'.$responseDetail->backdrop_path,
          'youtube_id'              =>  "[{$responseVideo->results[0]->key}]"
        ]
      ];
      // get image backdrop
      foreach($responseImage->backdrops as $value){
        $my_post['meta_input']['imagenes']    .= "https://image.tmdb.org/t/p/w300$value->file_path\n";
      }
      if($realPost['video_type'] == 'movie'){
        $responseOmdb   = json_decode($curl->get("https://www.omdbapi.com/?apikey=$omdbApiKey&i=$movieId"));
        // get countries
        $countries = [];
        foreach(explode(',', $responseOmdb->Country) as $value){
          $countries[] = $value;
        }
        // set post title
        $my_post['post_title']                        = wp_strip_all_tags($responseDetail->title);
        // imdb movie id
        $my_post['meta_input']['Checkbx2']            = $responseDetail->imdb_id;
        $my_post['meta_input']['Title']               = $responseDetail->original_title;
        // imdb data
        $my_post['meta_input']['idtmdb']              = $responseDetail->id;
        $my_post['meta_input']['imdbRating']          = $responseOmdb->imdbRating;
        $my_post['meta_input']['imdbVotes']           = $responseOmdb->imdbVotes;
        $my_post['meta_input']['Rated']               = $responseOmdb->Rated;
        $my_post['meta_input']['Country']             = $responseOmdb->Country;
        // tmdb data
        $my_post['meta_input']['tagline']             = $responseDetail->tagline;
        $my_post['meta_input']['release_date']        = $responseDetail->release_date;
        $my_post['meta_input']['vote_average']        = $responseDetail->vote_average;
        $my_post['meta_input']['vote_count']          = $responseDetail->vote_count;
        $my_post['meta_input']['Runtime']             = date('H:i:00', mktime(0,$responseDetail->runtime));
        // release year
        $releaseYear                                  = $responseOmdb->Year;
        $sourceName                                   = 'SERVER VIP';
      }else{
        // concat with overview of season response
        $my_post['post_content'] .= "\n\n".$translator->translate($responseSeason->overview);
        // get countries
        $countries = [];
        foreach($responseDetail->origin_country as $key => $value){
          switch ($value) {
            case 'US':
              $countries[$key] = 'USA';
              break;
            case 'JP':
              $countries[$key] = 'Japan';
              break;
            default:
              $countries[$key] = $value;
              break;
          }
        }
        $my_post['post_title']                        = wp_strip_all_tags($postTitle);
        $my_post['post_type']                         = 'tvshows';
        $my_post['meta_input']['id']                  = $responseDetail->id;
        // TV Series Data
        $my_post['meta_input']['original_name']       = $responseDetail->original_name;
        $my_post['meta_input']['first_air_date']      = $responseDetail->first_air_date;
        $my_post['meta_input']['last_air_date']       = $responseDetail->last_air_date;
        $my_post['meta_input']['serie_vote_average']  = $responseDetail->vote_average;
        $my_post['meta_input']['serie_vote_count']    = $responseDetail->vote_count;
        $my_post['meta_input']['number_of_episodes']  = count($responseSeason->episodes);
        $my_post['meta_input']['number_of_seasons']   = $responseSeason->season_number;
        $my_post['meta_input']['episode_run_time']    = $responseDetail->episode_run_time[0];
        $my_post['meta_input']['status']              = $responseDetail->status;
        // release year
        $releaseYear                                  = date('Y', strtotime($responseDetail->first_air_date));
        $sourceName                                   = 'Episode ';
      }
      // set rankmath focus keyword
      $my_post['meta_input']['rank_math_focus_keyword']             = wp_strip_all_tags($my_post['post_title']);
      // video source
      $videoSources = [];
      foreach($realPost['video_source'] as $key => $value){
        $num = $key + 1;
        $videoSources[] = [
          'name'    => $sourceName.$num,
          'idioma'  => 'id',
          'select'  => 'iframe',
          'url'     => $value,
          'mid'     => $num
        ];
      }
      // set video source meta
      $my_post['meta_input']['repeatable_fields']     = $videoSources;
      // Insert the post into the database
      $postId = apg_wp_insert_post($my_post);
      // insert thumbnail
      $attach_id = apg_base64_image_download($realPost['title'], $my_post['meta_input']['poster_url']);
      // set attachment id thumbnail to post
      set_post_thumbnail($postId, $attach_id);
      // set release year
      wp_set_object_terms( $postId, array($releaseYear), 'release-year');
      // set director
      wp_set_object_terms( $postId, $directors, 'director');
      // set actor
      wp_set_object_terms( $postId, $actors, 'stars');
      // set country
      wp_set_object_terms( $postId, $countries, 'country');
      // set network and studios for series video
      if($my_post['post_type'] == 'tvshows'){
        // array networks
        $networks = [];
        foreach($responseDetail->networks as $value){
          $networks[] = $value->name;
        }
        // array studio
        $studio = [];
        foreach($responseDetail->production_companies as $value){
          $studio[] = $value->name;
        }
        // set custom taxonomy
        wp_set_object_terms( $postId, $networks, 'networks');
        wp_set_object_terms( $postId, $studio, 'studio');
      }else{
        // set quality
        wp_set_object_terms( $postId, $quality, 'quality');
      }
      // end ---------------------- indoxximovie
    }
    // return response as json
    echo json_encode([
      'status' => TRUE,
      'message' => 'Success'
    ]);
  }else{
    return http_response_code(403);
  }
} catch (\Throwable $th) {
  // return response as json
  echo json_encode([
    'status' => FALSE,
    'message' => $th->getMessage()
  ]);
  file_put_contents('log.txt', $th);
}