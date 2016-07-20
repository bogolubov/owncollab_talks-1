<?php

//set_include_path(__DIR__);

include __DIR__ . "/ZBateson/MailMimeParser/MailMimeParser.php";

/**
 * Autoloader for ZBateson PHP libruary
 * @param $classname
 */
function __autoload($classname)
{
    if (strpos($classname, "ZBateson") !== false) {
        $filename = __DIR__ . "/" . str_replace("\\", "/", $classname) . ".php";
        if (is_file($filename))
            include_once($filename);
    }
}

/**
 * Mail log writer
 * @param $data_string
 */
function loger($data_string)
{
    $file_path = dirname(__DIR__) . "/mailparser.log";
    chmod($file_path, 0777);
    $data = "\n" . date("Y.m.d H:i:s") . ": " .trim($data_string);
    file_put_contents($file_path, $data, FILE_APPEND);
}

/**
 * @param $data_string
 */
function loger_error($data_string)
{
    $file_path = dirname(__DIR__) . "/mailparser_error.log";
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
    //$resource   = fopen(__DIR__."/mails/group.mail", "r");

    $data       = [];
    $resource   = fopen("php://stdin", "r");
    $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
    $message    = $mailParser->parse($resource);

    try {
        $data['mailParser']  = $message;
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
 * @param array $messageData
 */
function send_to_app(array $messageData)
{
    $config_file = dirname(dirname(dirname(__DIR__))) . '/config/config.php';

    if(!is_file($config_file)) {
        loger_error("Line: ".__LINE__."; Not found file config.php");
        exit;
    }

    include $config_file;

    /** @var array $CONFIG */
    $url = $CONFIG['overwrite.cli.url'] . '/index.php/apps/owncollab_talks/parse_manager';

    $messageFilesCount = $messageData['files_count'];
    $messageFilesParts = $messageData['files_parts'];
    $objectMailParser = $messageData['mailParser'];

    unset($messageData['mailParser']);
    unset($messageData['files_parts']);

    $fieldsData = $messageData;
    //$fieldsData['config'] = $CONFIG;

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
            exit();
        }

    } catch (Exception $e) {
        loger_error("Line: " . __LINE__ . "; Result from server not decode! QueryData:" . $result);
    }

    print_r($result);
}



loger("The script is is running...");

// Realization
send_to_app(parse_source_mail_data());
