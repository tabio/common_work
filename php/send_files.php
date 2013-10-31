<?php
/***************************************
send file by e-mail

***************************************/
function main($fpaths=array()) {
  try {
    var_dump($fpaths);
  } catch(Exception $e) {
  }
  return false;
}

function opt_msg($type=0,$msg='') {
  if ($type === 0) {
    echo "[start] ".date('Y-m-d H:i:s')."\n";
  } elseif($type === 1) {
    echo "[end]   ".date('Y-m-d H:i:s')."\n";
  } elseif($type === 2) {
    echo "[warn]  please check argments.\n";
  } elseif($type === 3) {
    echo "[warn]  not found => $msg\n";
  } elseif($type === 4) {
    echo "[error] fail send mail\n";
  }
}

if ($argc > 1) {
  $targets  = array();

  // start program
  opt_msg(0);

  for ($i=1;  $i<=$argc - 1; $i++) {
    if (file_exists($argv[$i])) {
      array_push($targets, $argv[$i]);
    } else {
      // file not found
      opt_msg(3, $argv[$i]);
    }
  }

  // send mail with target files
  if ($targets) main($targets);

  // end program
  opt_msg(1);
} else {
  opt_msg(2);
}

?>
