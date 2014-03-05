<?php
/**
 * [ Summary ]
 *   make sql file target mysql table data to import on phpMyAdmin console
 *   1. get mysql data from mysql database
 *   2. divide mysql data into each files 
 *   3. zip target sql files
 * [ Usage ]
 *   php mk_sql_phpmyadmin.php
 * [ Etc. ]
 *   PHP version 5.4.14
 *   @link    https://github.com/tabio/common_work
 *   @auther  tabio <tabio.github@gmail.com>
 *   @version 1.0
 *   @license Copyright (c) 2014 tabio
 *            This software is released under the MIT License.
 *            http://opensource.org/licenses/mit-license.php
*/

function main() {
  // set params
  $dsn      = 'localhost';
  $db_name  = 'test_db';
  $tbl_name = 'test_tbl';
  $db_user  = 'root';
  $db_pass  = '';
  $limit    = 4000;
  $pdo      = null;
  $dbh      = null;
  $fname    = 'insert_%s.sql';
  $fname_z  = 'insert_%s.zip';
  
  // set db connection
  try {
    $conn = sprintf('mysql:host=%s;dbname=%s', $dsn, $db_name);
    $pdo  = new PDO($conn, $db_user, $db_pass);
    $pdo->query('SET NAMES utf8');
  } catch(PDOException $e) {
    echo "db connect error : ".$e->getMessage();
    exit;
  }

  // make insert sql file
  try {
    $dbh   = $pdo->query( mkSqlCnt($tbl_name) );
    $count = $dbh->fetch();
    for ($i=0; $i<$count[0] - 1; $i=$i+$limit) {
      // set parameters
      $fp  = null;
      $dbh = null;
      $ret = null;

      // get data from database
      $dbh = $pdo->prepare( mkSql($tbl_name, $limit, $i) );
      $dbh->execute();
      $ret = $dbh->fetchAll(PDO::FETCH_ASSOC);

      // make insert file
      $file   = sprintf($fname,   $i);
      $file_z = sprintf($fname_z, $i);

      $fp     = fopen($file, "w");
      foreach ($ret as $row) {
        fwrite($fp, mkLine($tbl_name, $row));
      }
      fclose($fp);

      // gzip
      $zip = new ZipArchive();
      if ($zip->open($file_z, ZipArchive::CREATE)) {
        $zip->addFile($file);
        $zip->close();
        unlink($file);
      } else {
        echo "error make zip file $file_z\n";
      }

    }
  } catch(PDOException $e) {
    echo "make file error : ".$e->getMessage();
    exit;
  }
}

function mkLine($tbl_name, &$row) {
  $i    = 0;
  $keys = array();
  $vals = array();
  foreach ($row as $key => $val) {
    $keys[$i] = $key;
    if (preg_match('/^[0-9\.]+$/', $val)) {
      $vals[$i] = $val;
    } else {
      $vals[$i] = "'".str_replace("'", '', $val)."'";
    }
    $i++;
  }
  $line = "insert into $tbl_name ( %s ) values ( %s );\n";
  return sprintf($line, implode(',', $keys), implode(',', $vals));
}

function mkSqlCnt($tbl_name) {
  $sql = "select count(*) from $tbl_name";
  return $sql;
}

function mkSql($tbl_name, $limit=0, $offset=0) {
  $sql = "select * from $tbl_name limit $limit offset $offset";
  return $sql;
}

main();
?>
