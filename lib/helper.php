<?php

namespace OCA\Owncollab_Talks;

use OC\User\Session;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;

class Helper
{

    /**
     * Checked is app now
     * @param $appName
     * @return bool
     */
    static public function isAppPage($appName)
    {
        $requestUri = \OC::$server->getRequest()->getRequestUri();
        $uriParts = explode('/',trim($requestUri,'/'));
        if(strtolower($appName) === strtolower($uriParts[array_search('apps',$uriParts)+1]))
            return true;
        else return false;
    }

    /**
     * Current URI address path
     * @param $appName
     * @return bool|string
     */
    static public function getCurrentUri($appName)
    {
        $requestUri = \OC::$server->getRequest()->getRequestUri();
        $subPath = 'apps/'.$appName;
        if(strpos($requestUri, $subPath) !== false){
            $ps =  substr($requestUri, strpos($requestUri, $subPath)+strlen($subPath));
            if($ps==='/'||$ps===false) return '/';
            else return trim($ps,'/');
        }else{
            return false;
        }
    }

    /**
     * Check URI address path
     * @param $appName
     * @param $uri
     * @return bool
     */
    static public function isUri($appName, $uri)
    {
        $requestUri = \OC::$server->getRequest()->getRequestUri();
        if ( strpos($requestUri, $appName."/".$uri) !== false)
            return true;
        else return false;
    }

    /**
     * Render views and transmit data to it
     * @param $appName
     * @param $view
     * @param array $data
     * @return string
     */
    static public function renderPartial($appName, $view, array $data = [])
    {
        $response = new TemplateResponse($appName, $view, $data, '');
        return $response->render();
    }


    /**
     * Session worker
     * @param null $key
     * @param null $value
     * @return mixed|Sessioner
     */
    static public function session($key=null, $value=null)
    {
        static $ses = null;
        if($ses === null) $ses = new Sessioner();
        if(func_num_args() == 0)
            return $ses;
        if(func_num_args() == 1)
            return $ses->get($key);
        else
            $ses->set($key,$value);
    }

    /**
     * @param null $key
     * @param bool|true $clear
     * @return bool|string|array
     */
    static public function post($key=null, $clear = true)
    {
        if(func_num_args() === 0)
            return $_POST;
        else{
            if(isset($_POST[$key])) {
                if($clear)
                    return trim(strip_tags($_POST[$key]));
                else return $_POST[$key];
            }
        }
        return false;
    }

    /**
     * Encode string with salt
     * @param $unencoded
     * @param $salt
     * @return string
     */
    static public function encodeBase64($unencoded, $salt)
    {
        $string = base64_encode($unencoded);
        $encodeStr = '';
        $arr = [];
        $x = 0;
        while ($x++< strlen($string)) {
            $arr[$x-1] = md5(md5($salt.$string[$x-1]).$salt);
            $encodeStr = $encodeStr.$arr[$x-1][3].$arr[$x-1][6].$arr[$x-1][1].$arr[$x-1][2];
        }
        return $encodeStr;
    }

    static public function decodeBase64($encoded, $salt){
        $symbols="qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
        $x = 0;
        while ($x++<= strlen($symbols)-1) {
            $tmp = md5(md5($salt.$symbols[$x-1]).$salt);
            $encoded = str_replace($tmp[3].$tmp[6].$tmp[1].$tmp[2], $symbols[$x-1], $encoded);
        }
        return base64_decode($encoded);
    }

    static public function appName($name=false){
        static $_name = null;
        if($name) $_name = $name;
        return $_name;
    }


    static public function toTimeFormat($timeString){
        return date( "Y-m-d H:i:s", strtotime($timeString) );
    }

    /**
     * @return \OCP\IDBConnection
     */
    static public function getConnection(){
        return \OC::$server->getDatabaseConnection();
    }

    static public function randomString($length = 6, $symbols = ''){
        $abc = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".$symbols;
        $rand = "";
        for($i=0; $i<$length; $i++) {
            $rand .= $abc[rand()%strlen($abc)];
        }
        return $rand;
    }

    static public function checkTxt($text) {
        $text = nl2br($text);
        $text = strip_tags($text, "<p><br><b><strong><a><img><table><tr><td>");
        return $text;
    }

    static public function generateRepliedText($text, $author, $date) {
        $lines = preg_split ('/$\R?^/m', $text);
        $firstline = ">  On ".$date." ".$author." wrote: ";
        foreach($lines as $l => $line) {
            $lines[$l] = ">  ".$line;
        }
        $lastline = ">  ";
        $replied = $firstline."\n".implode("\n", $lines)."\n".$lastline;
        return $replied;
    }

    /**
     * Share file with a user
     * @param $filename string
     * @param $user string
     * @return bool
     */
    static public function shareFile($filename, $fromuser, $touser) {
        $ch = curl_init();
        $host = \OC::$server->getRequest()->getServerHost();
        $path = $fromuser['uid'].'@'.$host.'/ocs/v1.php/apps/files_sharing/api/v1/shares'; //TODO: Змінити абсолютну адресу на динамічну
        //$path = 'http://admin:admin@'.$host.'/ocs/v1.php/apps/files_sharing/api/v1/shares';
        $postfields = array(
            'path' => $filename,
            'shareType' => 0,
            'shareWith' => $touser,
            'publicUpload' => true,
            'password' => '',
            'permissions' => 1
        );

        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resultCurl = curl_exec($ch);
        $errorCurl = curl_error($ch);
        curl_close($ch);
        $returned = false;

        return $returned;
    }

    /**
     * Upload file to share
     * @param $filename string
     * @param $user string
     * @return bool
     */
    static public function uploadFile($filename, $user) {
        $host = \OC::$server->getRequest()->getServerHost();

        $target_url = 'davs://'.$host.'/remote.php/webdav/'.$filename['tmp_name'];
        $postfields = array(
            'extra_info' => '123456',
            'file_contents'=>'@/var/www/webdav/'.$filename['name']
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $user . ":admin"); //TODO Замінити 'admin' на реальний пароль
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }

    static public function time_elapsed_string($ptime) {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return '0 seconds';
        }

        $a = array(
            365 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60  =>  'month',
            24 * 60 * 60  =>  'day',
            60 * 60  =>  'hour',
            60  =>  'minute',
            1  =>  'second'
        );

        $a_plural = array(
            'year'   => 'years',
            'month'  => 'months',
            'day'    => 'days',
            'hour'   => 'hours',
            'minute' => 'minutes',
            'second' => 'seconds'
        );

        foreach ($a as $secs => $str)
        {
            $d = $etime / $secs;
            if ($d >= 1)
            {
                $r = round($d);
                return [$r, ($r > 1 ? $a_plural[$str] : $str) . ' ago'];
            }
        }
    }

    static public function sizeRoundedString($size) {
        if ($size < 1) {
            return '0 bytes';
        }

        $a = array(
            1024 * 1024 * 1024 * 1024  =>  'TB',
            1024 * 1024 * 1024  =>  'GB',
            1024 * 1024  =>  'MB',
            1024  =>  'kB',
            1  =>  'B'
        );

        foreach ($a as $bytes => $str) {
            $d = $size / $bytes;
            if ($d >= 1) {
                $r = round($d, 2);
                return $r . ' ' . $a[$bytes];
            }
        }
    }

    static public function getFileType($file) {
        $types = array(
            'image' => ['jpg','jpeg','gif','bmp','png'],
            'application-pdf' => ['pdf'],
            'x-office-document' => ['doc','dot','docx','docm','dotx','dotm','docb'],
            'x-office-spreadsheet' => ['xls','xlt','xlm','xlsx','xlsm','xltx','xltm','xlsb','xla','xlam','xll','xlw'],
            'x-office-presentation' => ['ppt','pot','pps','pptx','pptm','potx','potm','ppam','ppsx','ppsm','sldx','sldm – PowerPoint macro-enabled slide'],
            'audio' => ['3gp','aa','aac','aax','act','aiff','amr','ape','au','awb','dct','dss','dvf','flac','gsm','iklax','ivs','m4a','m4b','m4p','mmf','mp3','mpc','msv','ogg','oga','opus','ra','rm','raw','sln','tta','vox','wav','wma','wv','webm'],
            'video' => ['aaf','3gp','gif','asf','avchd','avi','cam','dat','dsh','dvr-ms','flv','m1v','m2v','fla','flr','sol','m4v','mkv','wrap','mng','mov','mpeg','mpg','mpe','mxf','roq','nsv','ogg','rm','svi','smi','swf','wmv','wtv','yuv'],
            'text' => ['cnf','conf','cfg','log','asc','txt','a']
        );
        $ext = substr($file['file_target'], strpos($file['file_target'], ".")+1);
        if ($file['item_type'] == 'folder') {
            $filetype = 'folder';
            if ($file['share_with']) {
                $filetype .= '-shared';
            }
        }
        else {
            foreach ($types as $t => $type) {
                if (in_array($ext, $type)) {
                    $filetype = $t;
                    break;
                }
            }
        }
        if (!$filetype) {
            $filetype = 'file';
        }
        return $filetype;
    }

    static public function firstWords($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$limit]) . '...';
        }
        return $text;
    }
}
