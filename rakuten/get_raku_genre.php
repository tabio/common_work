<?php
/**
 *  [ Summary ]
 *    Get genre data from rakuten API and output sql
 *  [ Usage ]
 *    php get_raku_genre.php [genre id (level 1)]
 *  [ Etc. ]
 *    PHP version 5.5.6
 *    @link    https://github.com/tabio/common_work
 *    @auther  tabio <tabio.github@gmail.com>
 *    @version 1.0
 *    @license Copyright (c) 2014 tabio
 *      This software is released under the MIT License.
 *      http://opensource.org/licenses/mit-license.php
 **/

function main( $g_id_lv1 ){

  // set parameters
  $sleep_cnt = 10;
  $fname     = 'raku_genre.tsv';
  $app_id    = '';
  $b_url     = 'https://app.rakuten.co.jp/services/api/IchibaGenre/Search/20120723?format=xml&';
  $b_url    .= "applicationId=".$app_id;
  $b_url    .= "&genreId=%s";

  // make query url
  $url = sprintf($b_url, $g_id_lv1);

  sleep($sleep_cnt);

  $xml = @simplexml_load_file($url);
  if( empty($xml) ){
    echo "[Wrong URL]\n$url\n";
    exit();
  }

  if( empty($xml->current) ){
    // false get node
    echo "[FALSE] GET CURRENT NODE\n genreId => $g_id_lv1\n";
    exit();
  } elseif( empty($xml->children) ) {
    // go to next node
    echo "[FINISH] ".$xml->current->genreId."\n";
    return false;
  } else {
    foreach( $xml->children->child as $val ) {

      $info = array();
      // parent info
      if(!empty($xml->parents)){
        foreach($xml->parents as $pval){
          $info[(int)$pval->genreLevel] = (int)$pval->genreId;
        }
      }

      // current info
      $info[(int)$xml->current->genreLevel] = (int)$xml->current->genreId;

      // target info
      $info[(int)$val->genreLevel] = (int)$val->genreId;
      $info["name"]                = (string)$val->genreName;

      // output
      try{
        $fp = fopen($fname, 'a+');
        $line = mkSql($info);
        fwrite($fp, $line);
      } catch(Exception $e) {
        echo "[Output Error] ".$e->getMessage()."\n";
        exit;
      }

      // next
      while( main((int)$val->genreId) );
    }
  }
}

function mkSql(&$info){

  $mst    = "mst_genre_lv";
  $col    = "name";
  $values = "'".$info["name"]."'";
  $max    = 0;
  $sql    = "INSERT INTO %s ( %s ) VALUES ( %s );\n";

  foreach($info as $key => $val){
    if($key != "name"){
      if( $max < $key ) $max = $key;
      $col    .= ",g_id_lv".$key;
      $values .= ",'".$val."'";
    }
  }
  $mst = $mst.$max;

  return sprintf($sql,$mst,$col,$values);
}

if( isset($argv[1]) && $argv[1] != "" ){
  // start
  main($argv[1]);
} else {
  echo "No Genre ID(LEVEL 1)\n";
}

?>
