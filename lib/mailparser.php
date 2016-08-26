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


    $data       = [];
    $resource   = fopen("php://stdin", "r");
    // for xDebug
    //$resource   = fopen(__DIR__."/mails/bogdan.mail", "r");
    $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
    $message    = $mailParser->parse($resource);

    try {
        $data['parser_message'] = $message;
        $data['to']          = $message->getHeaderValue('to');
        $data['to_name']     = is_object($message->getHeader('to')) ? $message->getHeader('to')->getPersonName() : '';
        $data['from']        = $message->getHeaderValue('from');
        $data['from_name']   = is_object($message->getHeader('from')) ? $message->getHeader('from')->getPersonName() : '';
        $data['subject']     = $message->getHeaderValue('subject');
        $data['content']     = stream_get_contents($message->getTextStream());
        $data['files_count'] = $message->getAttachmentCount();
        //$data['all_attachment_parts'] = (is_numeric($data['files_count']) && $data['files_count'] > 0)
        //                        ? $message->getAllAttachmentParts()
        //                        : false;

    } catch (Exception $error) {
        loger_error("Line: ".__LINE__."; Error parse source stdin resource. Message error: ".$error->getMessage());
    }

    fclose($resource);
    return $data;
}


/**
 * @var array $config
 * @param array $fieldsData
 */
function send_to_app(array $fieldsData)
{

    $config_file = APPROOT . '/config/config.php';
    if(!is_file($config_file)) {
        loger_error("Line: ".__LINE__."; Not found file config.php");
        exit;
    }

    $config = include $config_file;
    $url = $config['site_url'] . 'index.php/apps/owncollab_talks/parse_manager';

    $fcount = $fieldsData['files_count'];
    $parserMessage = $fieldsData['parser_message'];

    if($fcount > 0) {
        $fieldsData['files'] = files_parser($parserMessage, $fieldsData);
    }

    unset($fieldsData['parser_message']);
    $fieldsData['mail_domain'] = $config['mail_domain'];
    $fieldsData['site_url'] = $config['site_url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fieldsData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // console info
    print_r($result);

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

}

/**
 * @param \ZBateson\MailMimeParser\MimePart[] $mimePart
 * @param \ZBateson\MailMimeParser\Message $message
 * @return array
 */
function files_parser($message, $messageData)
{
    $i = 0;
    $files = [];

    while ($att = $message->getAttachmentPart($i)) {

        try{
            //$typeHeaders = $att->getHeaders();
            $typeHeader     = $att->getHeader('Content-Type');
            $filetype       = $typeHeader->getValue();
            $filename       = trim(explode('name=',$typeHeader->getRawValue())[1], "\"'");
            $filecontent    = stream_get_contents($att->getContentResourceHandle());

            $tmp_name = APPROOT . '/temp/' . time() . '-' . $messageData['from'] . '-' . $filename;

            if(file_put_contents($tmp_name, $filecontent)){
                chmod($tmp_name, 0755);
                //chown($tmp_name, 'www-data');
                $files[$i]['filename'] = $filename;
                $files[$i]['filetype'] = $filetype;
                $files[$i]['tmpfile'] = $tmp_name;
            } else
                loger_error("Line: ".__LINE__."; Error save file part: $i; name: $filename;");

        }catch (Exception $error) {
            loger_error("Line: ".__LINE__."; Error parse file part $i. Message error: ".$error->getMessage());
        }
        $i ++;
    }

    return $files;
}

loger("The script is is running...");

// Realization
send_to_app(parse_source_mail_data());
