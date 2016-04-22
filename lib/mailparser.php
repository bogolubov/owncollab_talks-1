<?php

$msg = file_get_contents("php://stdin");

if (strlen($msg) > 1000) {
    //file_put_contents('/tmp/inb.log', "\nNEW MESSAGE\n", FILE_APPEND);
    //file_put_contents('/tmp/inb.log', "\nmsg : " . $msg . "\n", FILE_APPEND);

    $path = realpath(dirname(dirname(dirname(__DIR__))));

    //file_put_contents('/tmp/inb.log', "\npath : " . $path . "\n", FILE_APPEND);

    include $path . '/config/config.php';

    $url = 'https://' . $CONFIG['trusted_domains'][0] . '/index.php/apps/owncollab_talks/parsemail';
    //file_put_contents('/tmp/inb.log', "\nurl : " . $url . "\n", FILE_APPEND);

    //file_put_contents('/tmp/inb.log', "\ncurl!\n", FILE_APPEND);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["message" => $msg]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultCurl = curl_exec($ch);
    $errorCurl = curl_error($ch);
    curl_close($ch);
    //file_put_contents('/tmp/inb.log', "\nFinish!\n", FILE_APPEND);
}
