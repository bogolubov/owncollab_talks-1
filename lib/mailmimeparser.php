<?php 
include __DIR__."/ZBateson/MailMimeParser/MailMimeParser.php"; 

function __autoload($classname) {
    if (strpos($classname, "ZBateson") !== false) { 
      $filename = __DIR__."/". str_replace("\\", "/", $classname) .".php"; 
    } 
    include_once($filename);
}

$path = realpath(dirname(dirname(dirname(__DIR__))));

// we've called a class ***
//$obj = new myClass();

$mailParser = new ZBateson\MailMimeParser\MailMimeParser();

$handle = fopen(__DIR__.'/tester-group.mail', 'r');
//if (strlen($msg) > 1000) {
    $message = $mailParser->parse($handle);         // returns a ZBateson\MailMimeParser\Message
    fclose($handle);

    $to = $message->getHeaderValue('to');
    $toName = $message->getHeader('to')->getPersonName();
    $from = $message->getHeaderValue('from');                       // user@example.com
    $fromName = $message->getHeader('from')->getPersonName();       // Person Name
    $subject = $message->getHeaderValue('subject');                 // The email's subject

    $res = $message->getTextStream();                               // or getHtmlStream
    $content = stream_get_contents($res);

    if ($att = $message->getAttachmentPart(0)) {                        // first attachment
        $headerValue = $att->getHeaderValue('Content-Type');            // text/plain for instance
        $headerParameter = $att->getHeaderParameter(                    // value of "charset" part
            'content-type',
            'charset'
        );
    }

//    $contents = stream_get_contents(
//        $att->getContentResourceHandle()
//    );


    $head = "\n\n\n================================================================\n".
        date("Y.m.d H:i:s")." MSG: ";
    //file_put_contents('/tmp/inb.log', $head . $msg, FILE_APPEND);

    //file_put_contents('/tmp/inb.log', "\npath : " . $path . "\n", FILE_APPEND);

    include $path . '/config/config.php';

    //$url = $CONFIG['overwrite.cli.url'] . '/index.php/apps/owncollab_talks/savemail';
    $url = 'http://13-59.skconsulting.cc.colocall.com/index.php/apps/owncollab_talks/savemail';
    //$url = $CONFIG['trusted_domains'][0] . '/index.php/apps/owncollab_talks/parsemail';
    echo "\n\n";
    echo $url."\n";
    //file_put_contents('/tmp/inb.log', "\nurl : " . $url . "\n", FILE_APPEND);

    //file_put_contents('/tmp/inb.log', "\ncurl!\n", FILE_APPEND);

    $messageParams = [
        'to' => $to,
        'toName' => $toName,
        'from' => $from,
        'fromName' => $fromName,
        'subject' => $subject,
        'contents' => $content,
        'hash' => substr($to, strpos($to, '+'), strpos($to, '@'))
    ];

    //print_r($messageParams);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $messageParams);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultCurl = curl_exec($ch);
    $errorCurl = curl_error($ch);
    curl_close($ch);
//}

?>