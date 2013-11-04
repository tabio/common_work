<?php
/**
 * send_files.php
 * Send file by e-mail
 * PHP version 5.4.14
 * @link   http://github.com/tabio/common_work
*/

//---------------------------------------------------
// Load pear modules
// ==> pear install Mail
// ==> pear install Mail_Mime
// ==> pear install Net_SMTP
//---------------------------------------------------
require_once('Mail.php');
require_once('Mail/mime.php');

//***************************************************
// Define
//***************************************************
define('MAIL_TO',        'hoge@hoge.co.jp');
define('MAIL_TO_NAME',   'name for mail client');
define('MAIL_FROM',      'hoge@hoge.co.jp');
define('MAIL_FROM_NAME', 'name for mail client');
define('SMTP_HOST',      'ssl://hoge.mail.jp');
define('PORT',           465);
define('SMTP_USER',      'hoge@hoge.co.jp');
define('SMTP_PASS',      '123456789');
define('MAX_FILE_SIZE',  10); // Mbyte


//***************************************************
// Methods
//***************************************************
function main($fpaths=array()) {
  try {

    // use japanese
    mb_language('Japanese');
    mb_internal_encoding('ISO-2022-JP');
    mb_http_output('UTF-8');

    // setting smtp params
    $params = array(
      'host'     => SMTP_HOST,
      'port'     => PORT,
      'auth'     => true,
      'username' => SMTP_USER,
      'password' => SMTP_PASS,
      'timeout'  => 30
    );

    // subject
    $subject = 'このメールはプログラムより送信されております。';
    $subject = mb_convert_encoding($subject, 'ISO-2022-JP', 'auto');

    // body
    $body = 'このメールはプログラムより送信されております。';
    $body = mb_convert_encoding($body, 'ISO-2022-JP', 'auto');
    $mime_obj = new Mail_Mime("\n");
    $mime_obj->setTxtBody($body);
    
    // add files
    if ($fpaths) {
      foreach($fpaths as $path) {
        $mime_obj->addAttachment($path, get_mime_type($path));
      }
    }

    $body_param = array(
      'head_charset' => 'ISO-2022-JP',
      'text_charset' => 'ISO-2022-JP'
    );

    $body = $mime_obj->get($body_param);

    // set header
    $add_headers = array(
      'From'    => mb_encode_mimeheader(MAIL_FROM_NAME).'<'.MAIL_FROM.'>',
      'To'      => mb_encode_mimeheader(MAIL_TO_NAME).  '<'.MAIL_TO.'>',
      'Subject' => mb_encode_mimeheader($subject)
    );
    $headers = $mime_obj->headers($add_headers);

    $mail = Mail::factory('smtp', $params);
    if (PEAR::isError($mail->send(MAIL_TO, $headers, $body))) {
      throw new Exception('failed to send mail');
    }

  } catch(Exception $e) {
    opt_msg(4, $e->getMessage());
  }
  return false;
}

function get_mime_type($name) {
  $tmp = pathinfo($name);
  switch ($tmp['extension']) {
    case 'html':
      $str = 'text/html';
      break;
    case 'xml':
      $str = 'text/xml';
      break;
    case 'js':
      $str = 'text/javascript';
      break;
    case 'css':
      $str = 'text/css';
      break;
    case 'gif':
      $str = 'image/gif';
      break;
    case 'jpg':
    case 'jpeg':
      $str = 'image/jpeg';
      break;
    case 'png':
      $str = 'image/png';
      break;
    case 'doc':
      $str = 'application/msword';
      break;
    case 'pdf':
      $str = 'application/pdf';
      break;
    default:
     $str = 'text/plain';
  }
  return $str;
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
    echo "[error] fail send mail => $msg\n";
  } elseif($type === 5) {
    echo "[error] file size over > ".MAX_FILE_SIZE."MB\n";
  }
}

function chk_file_size($files=array()) {
  $size = 0;
  foreach($files as $file) {
    $size += @filesize($file);
  }
  return (($size / 1048576) > MAX_FILE_SIZE) ? 0 : 1;
}

//===================================================
// start proguram
//===================================================
opt_msg(0);
$targets  = array();

// check files
for ($i=1;  $i<=$argc - 1; $i++) {
  if (file_exists($argv[$i])) {
    array_push($targets, $argv[$i]);
  } else {
    // file not found
    opt_msg(3, $argv[$i]);
  }
}

// check file size
if ($targets) {
  if (!chk_file_size($targets)) opt_msg(5);
}

// send mail
main($targets);

opt_msg(1);
//===================================================
// end proguram
//===================================================

?>
