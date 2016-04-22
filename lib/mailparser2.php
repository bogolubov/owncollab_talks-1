<?php

file_put_contents('/tmp/inb.log', "\nTODAY'S NEW MESSAGE\n", FILE_APPEND);
$msg = file_get_contents("php://stdin");

//file_put_contents('/tmp/inb.log', "curl!\n", FILE_APPEND);
//$url = $CONFIG['overwrite.cli.url'].'/index.php/apps/owncollab_talks/parsemail';
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

//file_put_contents('/tmp/inb.log', "Query sent!\n", FILE_APPEND);

/* $sender = getenv('SENDER');

$recipient = getenv('RECIPIENT');

if (is_string($msg) && !empty($msg)) {
    list($header, $body) = explode("\n\n", $msg, 2);
}

file_put_contents('/tmp/inb.log', "\nNEW MESSAGE\n", FILE_APPEND);

$message = array();
$subject = '';
$from = '';
$to = '';
$headerArr = explode("\n", $header);
foreach ($headerArr as $str) {
    if (strpos($str, 'Subject:') === 0) {
        $subject = $str;
    }
    if (strpos($str, 'From:') === 0) {
        $from = $str;
        $author = getUserIdFromAddress($from);
    }
    if (strpos($str, 'To:') === 0) {
        $to = $str;
        $subscribers = getSubscribers($to);
    }
    if (strpos($str, 'Delivery-date:') === 0) {
        $date = $str;
    }
}

file_put_contents('/tmp/inb.log', "Connection!\n", FILE_APPEND);
$connection = new Connect(Helper::getConnection());
if ($connection) {
    file_put_contents('/tmp/inb.log', "Connected!\n", FILE_APPEND);
}
$messages = $connection->connect->messages();
if ($messages) {
    file_put_contents('/tmp/inb.log', "Got messages!\n", FILE_APPEND);
}
$talkid = $messages->getIdByHash($subscribers[0]['hash']);
file_put_contents('/tmp/inb.log', "TalkId = ".$talkid."\n", FILE_APPEND);

$messagedata = array(
    'rid' => $talkid,
    'date' => $date,
    'title' => $subject,
    'text' => Helper::checkTxt($msg),
    'attachements' => NULL,
    'author' => $author,
    'subscribers' => implode(',', array_column($subscribers, 'userid')),
    'hash' => $subscribers[0]['hash'],
    'status' => 0
);

$logMsg = "=== MSG ===\n";
$logMsg .= "SENDER: $sender\n";
$logMsg .= "RECIPIENT: $recipient\n";
$logMsg .= "$from\n";
$logMsg .= "$subject\n\n";
file_put_contents('/tmp/inb.log',$logMsg, FILE_APPEND);

$saved = $messages->save($messagedata);
if ($saved) {
    file_put_contents('/tmp/inb.log', 'SAVED', FILE_APPEND);
}

function getSubscribers($address) {
    if (strpos($address, '<') && strpos($address, '>')) {
        preg_match('/<(.*?)>/', $address, $match);
        $address = $match[1];
    }
    $subscribers = array();
    $subscribers[] = $this->getUserIdFromAddress($address);
    return $subscribers;
}

function getUserIdFromAddress($address) {
    if (strpos($address, '<') && strpos($address, '>')) {
        preg_match('/<(.*?)>/', $address, $match);
        $address = $match[1];
    }
    $to = substr($address, 0, strpos($address, '@'));
    if ($delimiter = strpos($to, '+')) {
        $userid = substr($to, 0, $delimiter);
        $hash = substr($to, $delimiter+1);
        return ['userid' => $userid, 'hash' => $hash];
    }
    else {
        return $to;
    }
} */
