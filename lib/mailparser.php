<?php

define('APPROOT', dirname(__DIR__));

include APPROOT . "/lib/ZBateson/MailMimeParser/MailMimeParser.php";

/**
 * Autoloader for ZBateson PHP libruary
 * @param $classname
 */
function __autoload($classname)
{
    if (strpos($classname, "ZBateson") !== false) {
        $filepath = APPROOT . "/lib/" . str_replace("\\", "/", $classname) . ".php";
        if (is_file($filepath))
            include_once($filepath);
    }
}


/**
 * Mail log writer
 * @param $data_string
 */
function loger($data_string)
{
    $file_path = APPROOT . "/mailparser.log";
    chmod($file_path, 0777);
    $data = "\n" . date("Y.m.d H:i:s") . ": " .trim($data_string);
    file_put_contents($file_path, $data, FILE_APPEND);
}


/**
 * @param $data_string
 */
function loger_error($data_string)
{
    $file_path = APPROOT . "/mailparser_error.log";
    chmod($file_path, 0777);
    $data = "\n" . date("Y.m.d H:i:s") . ": " .trim($data_string);
    file_put_contents($file_path, $data, FILE_APPEND);
}


/**
 * @return mixed
 */
function parse_source_mail_data()
{
    // for xDebug
    //$resource   = fopen(__DIR__."/group.mail", "r");

    $data       = [];
    $resource   = fopen("php://stdin", "r");
    $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
    $message    = $mailParser->parse($resource);

    try {
        $data['parsMessage']  = $message;
        $data['to']          = $message->getHeaderValue('to');
        $data['to_name']     = is_object($message->getHeader('to')) ? $message->getHeader('to')->getPersonName() : '';
        $data['from']        = $message->getHeaderValue('from');
        $data['from_name']   = is_object($message->getHeader('from')) ? $message->getHeader('from')->getPersonName() : '';
        $data['subject']     = $message->getHeaderValue('subject');
        $data['content']     = stream_get_contents($message->getTextStream());
        $data['files_count'] = $message->getAttachmentCount();
        $data['files_parts'] = (is_numeric($data['files_count']) && $data['files_count'] > 0)
                                ? $message->getAllAttachmentParts()
                                : false;

    } catch (Exception $error) {
        loger_error("Line: ".__LINE__."; Error parse source stdin resource. Message error: ".$error->getMessage());
    }

    fclose($resource);
    return $data;
}


/**
 * @var array $config
 * @param array $messageData
 */
function send_to_app(array $messageData)
{

    $config_file = APPROOT . '/appinfo/config.php';
    if(!is_file($config_file)) {
        loger_error("Line: ".__LINE__."; Not found file config.php");
        exit;
    }

    $config = include $config_file;
    $url = $config['site_url'] . 'index.php/apps/owncollab_talks/parse_manager';

    $fcount = $messageData['files_count'];
    $fparts = $messageData['files_parts'];
    $parsMessage = $messageData['parsMessage'];

    if($fcount > 0) {
        $fieldsData['files'] = files_parser($fparts, $parsMessage);
    }

    unset($messageData['parsMessage']);
    unset($messageData['files_parts']);

    $fieldsData = $messageData;
    $fieldsData['mail_domain'] = $config['mail_domain'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        loger_error("Line: " . __LINE__ . "; cURL request fail! Error: " . $error);
        exit();
    }
    try {
        $resultData = json_decode($result, true);

        if ($resultData['type'] != 'ok') {
            loger_error("Line: " . __LINE__ . "; Result from server is bad! QueryData:" . $result);
        } else {
            loger("Parse and send mail data is success! From: " .$fieldsData['from']. " To: " . $fieldsData['to'] . " Result data: " . $result);
        }

    } catch (Exception $e) {
        loger_error("Line: " . __LINE__ . "; Result from server not decode! QueryData:" . $result);
    }

    print_r($result);
}

function files_parser($fparts, $parsMessage)
{

}

loger("The script is is running...");

// Realization
send_to_app(parse_source_mail_data());
