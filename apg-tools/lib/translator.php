<?php

class Translator {

  private $endpoint = "https://translate.google.com/translate_a/single?client=gtx&dj=1&sl=en&tl=id&hl=en&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&ie=UTF-8&oe=UTF-8&otf=1&ssel=0&tsel=0&kc=1&tk=685532.807248&q=";

  public function __construct()
  {

  }

  public function translate($text = NULL)
  {
    $response = '';
    try {
      $curl = new CurlWrapper;
      $json = json_decode($curl->get($this->endpoint.rawurlencode($text)));
      foreach($json->sentences as $value){
        $response .= $value->trans;
      }
    } catch (\Throwable $th) {
      $response = $text;
    }
    return $response;
  }

}