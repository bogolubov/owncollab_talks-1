<?php

include __DIR__."/ZBateson/MailMimeParser/MailMimeParser.php";

/**
 * get stdin stream
 */
$resource = fopen("php://stdin", "r");
$path = realpath(dirname(dirname(dirname(__DIR__))));

/**
 * Autoloader for ZBateson PHP libruary
 * @param $classname
 */
function __autoload($classname) {
    if (strpos($classname, "ZBateson") !== false) {
        $filename = __DIR__."/". str_replace("\\", "/", $classname) .".php";
        if(is_file($filename))
            include_once($filename);
    }
}

/**
 * Mail log writer
 * @param $data_string
 */
function loger ($data_string) {
    $path = dirname(__DIR__)."/mailparser.log";
    $data = "\n".date("Y.m.d H:i:s").": $data_string";
    file_put_contents($path, $data, FILE_APPEND);
}

function parse_source_mail_data ($resource) {

    $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
    $message = $mailParser->parse($resource);

    fclose($resource);

    $data['to']         = $message->getHeaderValue('to');
    $data['to_name']    = $message->getHeader('to')->getPersonName();
    $data['from']       = $message->getHeaderValue('from');
    $data['from_name']  = $message->getHeader('from')->getPersonName();
    $data['subject']    = $message->getHeaderValue('subject');
    $data['conten']     = stream_get_contents($message->getTextStream());
    $data['attachs']    = $message->getAttachmentCount();

//    $to = $message->getHeaderValue('to');
//    $toName = $message->getHeader('to')->getPersonName();
//    $from = $message->getHeaderValue('from');                       // user@example.com
//    $fromName = $message->getHeader('from')->getPersonName();       // Person Name
//    $subject = $message->getHeaderValue('subject');                 // The email's subject
//    $res = $message->getTextStream();                               // or getHtmlStream
//    $content = stream_get_contents($res);
//    $attachescount = $message->getAttachmentCount();

    var_dump($data);
}

function send_to_app ($url, array $arr_data) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resultCurl = curl_exec($ch);
    $errorCurl = curl_error($ch);

    curl_close($ch);
}

// Realization

if(!empty($resource) && strlen($resource) > 10) {

    parse_source_mail_data ($resource);

}


