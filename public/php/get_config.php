<?php

include 'config.php';

/* === 
  'ac' => $active_captcha
  'au' => $active_uploader
 */
$ress = array(
    'ac' => $active_captcha,
    'au' => $active_uploader
);

header('Content-type: application/json');
echo json_encode($ress);