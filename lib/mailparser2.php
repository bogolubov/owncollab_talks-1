<?php

$msg = file_get_contents("php://stdin");

$url = 'http://13-59.skconsulting.cc.colocall.com/index.php/apps/owncollab_talks/parsemail';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ["message" => $msg] );
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resultCurl = curl_exec($ch);
$errorCurl = curl_error($ch);
curl_close($ch);
