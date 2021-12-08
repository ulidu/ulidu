<?php

session_start();
//configuration 
include 'config.php';
$path = 'uploads';
$filename = NULL;

//check config  $active_uploader = TRUE;
if ($active_uploader) {
  $filename = (isset($_POST['file']) && $_POST['file'] != '') ? $_POST['file'] : NULL;
}

$message = $_POST['message'];
$from = $_POST['email'];
$subject = (isset($_POST['subject']) && $_POST['subject'] != "") ? $_POST['subject'] : $default_subject;
$mailto = $my_email;

// check if any attachment

if ($filename) {
  $file = $path . "/" . $filename;
  $file_size = filesize($file);
  $handle = fopen($file, "r");
  $content = fread($handle, $file_size);
  fclose($handle);
  $content = chunk_split(base64_encode($content));
}

// a random hash will be necessary to send mixed content
$separator = md5(time());

// carriage return type (we use a PHP end of line constant)
$eol = PHP_EOL;

// main header (multipart mandatory)
$headers = "From: " . $_POST['fullname'] . " <" . $from . ">" . $eol;
$headers .= 'Reply-To: <' . $from . '>' . $eol;
$headers .= "MIME-Version: 1.0" . $eol;
$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
$headers .= "Content-Transfer-Encoding: 7bit" . $eol;
$headers .= "This is a MIME encoded message." . $eol . $eol;

// message
$headers .= "--" . $separator . $eol;
$headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
$headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
$headers .= $message . $eol . $eol;

// attachment
if ($filename) {
  $headers .= "--" . $separator . $eol;
  $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
  $headers .= "Content-Transfer-Encoding: base64" . $eol;
  $headers .= "Content-Disposition: attachment" . $eol . $eol;
  $headers .= $content . $eol . $eol;
  $headers .= "--" . $separator . "--";
}


$ress = array('error' => NULL, 'msg' => NULL);

error_reporting(0);
$err = "Mailing failed: Error message: ";


//check config  $active_captcha = TRUE;
if ($active_captcha) {
  // check captcha first;
  if (isset($_SESSION['simpleCaptchaAnswer']) && $_POST['captchaSelection'] == $_SESSION['simpleCaptchaAnswer']) {
    //SEND Mail
    if (mail($mailto, $subject, "", $headers)) {
      $ress['msg'] = $success_msg;
    } else {
      $get_last_error = error_get_last();
      $ress['error'] = $err . $get_last_error['message']; //"error : email not sent";
    }
  } else {
    $ress['error'] = "Please check your answer!";
  }
} else {
  //SEND Mail
  if (mail($mailto, $subject, "", $headers)) {
    $ress['msg'] = $success_msg;
  } else {
    $get_last_error = error_get_last();
    $ress['error'] = $err . $get_last_error['message']; //"error : email not sent";
  }
}

//respond to json
echo json_encode($ress);
