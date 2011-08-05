<?php


class google_tools {

  /***********************************************************
  ******** Top 100 search results 
  ***********************************************************/
  function search_results($keywords){
    $keywords_encode=urlencode($keywords);
    $num=100;
    $contents=self::search_results_fetch("http://www.google.com/search?num=$num&q=$keywords_encode");
    if(!preg_match_all('/<h3 class="r"><a href="(.*?)"/',$contents,$matches)) return false; 
    else {
      if(count($matches[1])<100){
        if(!$additional_array= self::search_results_next_10($keywords)){
          die("get additional search result fail");
        } else {
          foreach($additional_array as $url){
            array_push($matches[1],$url);
            if(count($matches[1])>=100) break;
          }
        }
      }
      return $matches[1];
    }
  }
  // helper functions
  function search_results_next_10($keywords){
    $keywords_encode=urlencode($keywords);
    $num=10;
    $url = "http://www.google.com/search?num=$num&start=100&q=$keywords_encode";
    $contents= self::search_results_fetch($url);
    if(!preg_match_all('/<h3 class="r"><a href="(.*?)"/',$contents,$matches)) return false; 
    else return $matches[1];
  }
  function search_results_fetch($url){
    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    return curl_exec($ch);
    curl_close($ch);
  }


  /***********************************************************
  ******** URL Pagerank 
  ***********************************************************/
  function pagerank($url) {
    $googleurl = 'http://toolbarqueries.google.com/search?features=Rank&sourceid=navclient-ff&client=navclient-auto-ff&googleip=O;66.249.81.104;104&ch=' . self::genhash($url) . '&q=info:' . urlencode($url);
    if(function_exists('curl_init')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $googleurl);
      $out = curl_exec($ch);
      curl_close($ch);
    } else $out = file_get_contents($googleurl); 
    $rank=substr($out,9);
    if(strlen($rank)>100) $rank=-1;
    return $rank;
  } 
  // helper functions
  function genhash ($url) {
    $url=urldecode($url);
    $hash = 'Mining PageRank is AGAINST GOOGLE\'S TERMS OF SERVICE. Yes, I\'m talking to you, scammer.';
    $c = 16909125;
    $length = strlen($url);
    $hashpieces = str_split($hash);
    $urlpieces = str_split($url);
    for ($d = 0; $d < $length; $d++) {
      $c = $c ^ (ord($hashpieces[$d]) ^ ord($urlpieces[$d]));
      $c = self::zerofill($c, 23) | $c << 9;
     }
    return '8' . self::hexencode($c);
  }
  function zerofill($a, $b) {
    $z = hexdec(80000000);
    if ($z & $a) {
      $a = ($a>>1);
      $a &= (~$z);
      $a |= 0x40000000;
      $a = ($a>>($b-1));
    } else {
      $a = ($a>>$b);
    }
    return $a;
  }
  function hexencode($str) {
    $out  = self::hex8(self::zerofill($str, 24));
    $out .= self::hex8(self::zerofill($str, 16) & 255);
    $out .= self::hex8(self::zerofill($str, 8 ) & 255);
    $out .= self::hex8($str & 255);
    return $out;
  }
  function hex8 ($str) {
    $str = dechex($str);
    (strlen($str) == 1 ? $str = '0' . $str: null);
    return $str;
  }



  /***********************************************************
  ******** Keyword suggestions with query count 
  ***********************************************************/
  function suggestions($keyword) { 
    $url = 'http://google.com/complete/search?output=toolbar&q='.$keyword;
    $xml = simplexml_load_file($url);
    $data = $xml->xpath("/toplevel/CompleteSuggestion/suggestion");
    $queries = $xml->xpath("/toplevel/CompleteSuggestion/num_queries"); 
    $i = 0;
    $count = count($data)-1; 
    while($i <= $count){
      $kw = (string)$data[$i]['data'];
      $q_count = (int)$queries[$i]['int'];
      if ($kw && $q_count) $result[] = array('keyword'=> $kw, 'queries'=> $q_count);
      $i++;
    }
    return $result;
  }
  

  
  


}






















