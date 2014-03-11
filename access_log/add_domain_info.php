<?php
/**
 * [Summary]
 *   This program is to add domain infomation from ip address for target file.
 *
 * [Usage]
 *   php add_domain_info.php [target file]
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
 * @link      https://github.com/tabio/common_work
 * @auther    tabio <tabio.github@gmail.com>
 * @version   1.1
 * @copyright Copyright (c) 2014 tabio
 *            All rights reserved.
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 */

// Check target file
if (!isset($argv[1]) && empty($argv[1])) return false;

// Set file
$fname  = $argv[1];
$fname2 = $argv[1]."_opt";

$fp  = fopen($fname,  'r');
$fp2 = fopen($fname2, 'w');

while (!feof($fp)) {
  $line = fgets($fp);
  $line = trim($line);
  if (empty($line)) continue;

  // add domain info
  $arr = explode("\t", $line);
  $tmp = gethostbyaddr($arr[1]);
  fwrite($fp2, "$line\t$tmp\n");
}

fclose($fp);
fclose($fp2);

exit;
?>
