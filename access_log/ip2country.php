<?php
/**
 * [Summary]
 *   This program is to get country information for ip list.
 *
 * [Usage]
 *   php ip2country.php -f [target file] -c [cidr file path] -w[Contry Code]
 *
 * [target file format]
 *   --> tsv format
 *   --> column1 : access count
 *   --> column2 : ip address
 *   (ex)
 *     123  192.168.1.12
 *     234  192.168.1.27
 *
 * [Etc.]
 * @link      https://github.com/tabio/uniprot
 * @auther    tabio <tabio.github@gmail.com>
 * @version   1.1
 * @copyright Copyright (c) 2013 tabio
 *            All rights reserved.
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 */


/**
 * set cidr array
 * -> download the latest cidr.txt from http://nami.jp/ipv4bycc/
 * @param  $path string path of cidr.txt
 * @return array(country => range)
 */
function setCidr($path) {
  $res = array();
  try {
    $fp = fopen($path, 'r');
    while (!feof($fp)) {
      $line = trim(fgets($fp));
      if (empty($line)) continue;
      $tmp = split("\t", $line);
      $res[$tmp[1]] = $tmp[0];
    }
    fclose($fp);
  } catch(Exception $e) {
    var_dump($e->getMessage());
    exit(1);
  }
  return $res;
}


/**
 * check country coude from ip addres
 * @param  $target_ip string ip address
 * @return string country code
 */
function chkCountry($target_ip, &$cidrs) {
  foreach ($cidrs as $key => $val) {
    list($ip, $mask_bit) = explode("/", $key);

    // right bit shift
    $ip_long   = ip2long($ip) >> (32 - $mask_bit);
    $t_ip_long = ip2long($target_ip) >> (32 - $mask_bit);

    if ($t_ip_long === $ip_long) return $val;
  }
  return false;
}


/**
 * useage
 */
function useage() {
  echo "[warning] check args!!\n";
  echo "php ".basename(__FILE__)." -f [target file path] -c [cidr file path]\n";
}


function main($t_path, $c_path, $w_code=null) {
  $res   = array();

  // set cidr information
  $cidrs = setCidr($c_path);

  try {
    $fp = fopen($t_path, 'r');
    while (!feof($fp)) {
      $line = trim(fgets($fp));
      if (empty($line)) continue;
      $tmp = split("\t", $line);
      $cn  = chkCountry($tmp[1], $cidrs);
      if ($cn) {
        if ($w_code) {
          if ($cn == $w_code) {
            if (!isset($res[$tmp[1]])) $res[$tmp[1]] = 0;
            $res[$tmp[1]] += $tmp[0];
          }
        } else {
          // count list
          if (!isset($res[$cn])) $res[$cn] = 0;
          $res[$cn] += $tmp[0];
        }
      }
    }

    // output
    foreach ($res as $key => $val) {
      echo $key."\t".$val."\n";
    }

  } catch(Exception $e) {
    var_dump($e->getMessage());
    exit(1);
  }
}

//-- check argument
$opt = getopt('f:c:w::');
if (isset($opt['f']) && !empty($opt['f'])
 && isset($opt['c']) && !empty($opt['c'])
) {
  if (!file_exists($opt['f']) || !file_exists($opt['c'])) {
    useage();
    exit(1);
  } else {
    main($opt['f'], $opt['c'], @$opt['w']);
  }
} else {
  useage();
}

?>
