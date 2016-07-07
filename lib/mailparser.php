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
    $data = [];
    $resource   = fopen("php://stdin", "r");
    $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
    $message    = $mailParser->parse($resource);

    fclose($resource);

    try{
        $data['to']         = $message->getHeaderValue('to');
        $data['to_name']    = is_object($message->getHeader('to')) ? $message->getHeader('to')->getPersonName() : '';
        $data['from']       = $message->getHeaderValue('from');
        $data['from_name']  = is_object($message->getHeader('from')) ? $message->getHeader('from')->getPersonName() : '';
        $data['subject']    = $message->getHeaderValue('subject');
        $data['content']    = stream_get_contents($message->getTextStream());
        $data['files']      = $message->getAttachmentCount();
    } catch (Exception $error) {
        loger_error("Line: ".__LINE__."; Error parse source stdin resource. Message error: ".$error->getMessage());
    }

    return $data;
}


/**
 * @param array $arr_data
 */
function send_to_app(array $arr_data)
{
    $config_file = dirname(dirname(dirname(__DIR__))) . '/config/config.php';

    if(!is_file($config_file)) {
        loger_error("Line: ".__LINE__."; Not found file config.php");
        exit;
    }

    include $config_file;

    /** @var array $CONFIG */
    $url = $CONFIG['overwrite.cli.url'] . '/index.php/apps/owncollab_talks/parse_manager';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $error = curl_error($ch);

    //print($result . "\n");
    loger('Send to app result: ' . serialize($result));

    /*
    if($error)
        loger_error("Line: ".__LINE__."; Curl error: " . $error);
    elseif($result == 'ok')
        loger("Parse and send mail data is success! From: " .$arr_data['from']. " To: " . $arr_data['to']);
    else
        loger_error("Line: ".__LINE__."; Curl success, but result is not response confirmation. Response: " .substr($result,0,20). "..." );
    */
    curl_close($ch);
}

// [include_path:".get_include_path()."]
loger("The script is is running...");

// Realization
send_to_app(parse_source_mail_data());

