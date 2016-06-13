<?php 
//$msg = file_get_contents("php://stdin");
$handle=fopen("php://stdin", "r"); 

	$head = "\n\n\n================================================================\n".date("Y.m.d H:i:s")." MSG: \n";
	file_put_contents('/tmp/inb.log', $head, FILE_APPEND);
	//file_put_contents('/tmp/inb.log', "\n DIR : ".__DIR__, FILE_APPEND);

include __DIR__."/ZBateson/MailMimeParser/MailMimeParser.php"; 

function __autoload($classname) {
    if (strpos($classname, "ZBateson") !== false) { 
      $filename = __DIR__."/". str_replace("\\", "/", $classname) .".php"; 
    } 
    include_once($filename);
}

$path = realpath(dirname(dirname(dirname(__DIR__))));
	//file_put_contents('/tmp/inb.log', "\n".$path, FILE_APPEND);

// we've called a class ***
//$obj = new myClass();

	//file_put_contents('/tmp/inb.log', "\nInit parser!", FILE_APPEND);
$mailParser = new ZBateson\MailMimeParser\MailMimeParser();
	//file_put_contents('/tmp/inb.log', "\nParser loaded!", FILE_APPEND);

//$handle = fopen(__DIR__.'/olexiy2.mail', 'r');

	//file_put_contents('/tmp/inb.log', "\nstrlen(msg) : ".(is_resource($handle)?"True":"False"), FILE_APPEND);

//if (strlen($msg) > 10) {
	//file_put_contents('/tmp/inb.log', "\n!!!!!!!!--------------", FILE_APPEND);
    $message = $mailParser->parse($handle);         // returns a ZBateson\MailMimeParser\Message
    //$message = $mailParser->parse($msg);         // returns a ZBateson\MailMimeParser\Message
    fclose($handle);

	//file_put_contents('/tmp/inb.log', "\nTrying to parse!--------------\n", FILE_APPEND);
    try { 
	//$headerTo = $message->getHeader('to'); 
	//$headerFrom = $message->getHeader('from'); 
	$to = $message->getHeaderValue('to');
	$toName = $message->getHeader('to')->getPersonName();
	$from = $message->getHeaderValue('from');                       // user@example.com
	$fromName = $message->getHeader('from')->getPersonName();       // Person Name
	$subject = $message->getHeaderValue('subject');                 // The email's subject
	
	$res = $message->getTextStream();                               // or getHtmlStream
	$content = stream_get_contents($res);

	$pluspos = strpos($to, '+'); 
	if ($pluspos > 0) { 
		$hash = substr($to, strpos($to, '+') + 1, 16); 
	} 

	//file_put_contents('/tmp/inb.log', "\nTry successful!", FILE_APPEND);
    } 
    catch (Exaption $e) {
	$error = $e->getMessages(); 
	//file_put_contents('/tmp/inb.log', "\nCatched error : ".$error, FILE_APPEND);
    } 

    $attachescount = $message->getAttachmentCount(); 
    if ($attachescount > 0) {
	$attachedFiles = array(); 
	for ($i=0; $i<$attachescount; $i++) { 
	    $att = $message->getAttachmentPart($i);                         // first attachment

	    $attachedFiles[$i]['contentType'] = $att->getHeaderValue('Content-Type');            // text/plain for instance
	    $attachedFiles[$i]['encoding'] = $att->getHeaderValue('content-transfer-encoding'); 
	    $attachedFiles[$i]['filename'] = $att->getHeaderParameter('Content-Disposition', 'filename'); 
	    $attachedFiles[$i]['contents'] = stream_get_contents($att->getContentResourceHandle()); 
	} 
    } 


    /* foreach ($attachedFiles as $a => $file) {
	if (!empty($file['contents'])) { 
	    if ($file['contentType'] == 'image/png' && $file['encoding'] == 'base64') { 
		base64_to_jpeg($file['contents'], $file['filename']); 
	    } 
	} 
    } */ 
    
	//file_put_contents('/tmp/inb.log', "\npath : " . $path . "\n", FILE_APPEND);

    include $path . '/config/config.php';
    $projectname = 'owncollab';
    //$projectmail = 'team@'.$projectname.'.'.$CONFIG['trusted_domains'][0];
    $projectmail = 'team@'.$CONFIG['trusted_domains'][0];
	//file_put_contents('/tmp/inb.log', "\nurl : " . $url . "\n", FILE_APPEND);
	//file_put_contents('/tmp/inb.log', "\ncurl!\n", FILE_APPEND);

    $messageParams = [
        'to' => $to,
        'toName' => $toName,
        'from' => $from,
        'fromName' => $fromName,
        'subject' => $subject,
        'contents' => $content,
        'hash' => $hash
    ];

    if (!empty($attachedFiles)) { 
	    $messageParams['attachments'] = serialize($attachedFiles);
    } 
 
    if (!$hash || $to == $projectmail) {
        $url = $CONFIG['overwrite.cli.url'] . '/index.php/apps/owncollab_talks/savemailtalk';
    }
    else {
        $url = $CONFIG['overwrite.cli.url'] . '/index.php/apps/owncollab_talks/savemail';
    }
   
	//file_put_contents('/tmp/inb.log', "\nMessage parsed!".print_r($messageParams, true), FILE_APPEND);

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

/* function base64_to_file($base64_string, $output_file) {
    $ifp = fopen($output_file, "wb"); 
    fwrite($ifp, $base64_string); 
    fclose($ifp); 
    return $output_file; 
} */ 

?>
