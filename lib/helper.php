<?php

namespace OCA\Owncollab_Talks;

use OC\User\Session;
use OCA\Owncollab_Chart\Sessioner;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Owncollab_Talks\PHPMailer\PHPMailer;

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
     * Accessor for $_COOKIE when fetching values, or maps directly
     * to setcookie() when setting values.
     * @param $name
     * @param null $value
     * @return mixed|null
     */
    static public function cookies($name, $value = null)
    {
        $argsNum = func_num_args();
        $argsValues = func_get_args();

        if ($argsNum == 1)
            return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;

        return call_user_func_array('setcookie', $argsValues);
    }


    /**
     * Accessor for $_SESSION
     * @param $name
     * @param null $value
     * @return null
     */
    static public function simpleSession($name, $value = null)
    {
        if(!isset($_SESSION))
            session_start();

        # session var set
        if (func_num_args() == 2)
            return ($_SESSION[$name] = $value);

        # session var get
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }


    /**
     * Session worker
     * @param null $key
     * @param null $value
     * @return mixed|null|Sessioner
     */
    static public function session($key = null, $value = null)
    {
        static $sessioner = null;
        if($sessioner === null)
            $sessioner = new Sessioner();
        if(func_num_args() == 0)
            return $sessioner;
        if(func_num_args() == 1)
            return $sessioner->get($key);
        else
            $sessioner->set($key, $value);
    }

    /**
     * @param $routeName
     * @param array $arguments
     * @return string
     */
    static public function linkToRoute($routeName, $arguments = [])
    {
        return \OC::$server->getURLGenerator()->linkToRoute($routeName, $arguments);
    }


    /**
     * @param $appName
     * @param $file
     * @param array $arguments
     * @return string
     */
    static public function linkTo($appName, $file, $arguments = [])
    {
        return \OC::$server->getURLGenerator()->linkTo($appName, $file, $arguments);
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

/*    static public function appName($name=false){
        static $_name = null;
        if($name) $_name = $name;
        return $_name;
    }*/


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
     * @param $key
     * @param string $default
     * @return mixed
     */
    static public function getSysConfig($key, $default = '') {
        return \OC::$server->getSystemConfig()->getValue($key, $default);
    }


    /**
     * @return string
     */
    static public function getUID() {
        return \OC::$server->getUserSession()->getUser()->getUID();
    }


    static public function getFileData($path)
    {
        $view = new \OC\Files\View('/'.self::getUID().'/files');
        $file = null;
        $fileInfo = $view->getFileInfo($path);
        if ($fileInfo) $file = $fileInfo;

        return $file;
    }

    /**
     * Static saved appName value
     * @var null
     */
    static private $_appName = null;

    /**
     * Set to static save appName value
     * @param $name
     * @return string|null
     */
    static public function setAppName($name) {
        return self::$_appName = $name;
    }

    /**
     * Get static saved appName value
     * @return string|null
     */
    static public function getAppName() {
        $res = null;
        if(self::$_appName) $res = self::$_appName;
        else
            if($appName = self::val('appName'))
                $res = self::$_appName = $appName;

        return $res;
    }


    /**
     * @param string $path  If $path string started with slash example: '/path/to..' - its was indicate to absolute path.
     *                          And, without slash is relative path
     * @param array $args   Extracted on include file
     * @return null|array|mixed
     */
    static public function includePHP($path, array $args = [])
    {
        if(is_file($path)) {

            ob_start();
            extract($args);
            $fileResult = include $path;
            $obResult = ob_get_clean();

            if(!empty($fileResult) && is_array($fileResult))
                return $fileResult;
            else
                return $obResult;
        }

        return false;
    }


    /**
     * <pre>
     * 'insecureServerHost'
     * 'httpProtocol'
     * 'requestUri'
     * 'serverHost'
     * 'urlParams'
     * 'pathInfo'
     * 'urlFull'
     * null 'appName'
     * null 'userId'
     * </pre>
     * @param array|string|null $parts
     * @return array|null
     */
    static public function val($parts = null)
    {
        static $data = null;

        if($data == null || (is_array($parts) && !empty($parts)) ) {

            $req = \OC::$server->getRequest();

            $dataDefault = [
                'insecureServerHost' => $req->getInsecureServerHost(),
                'httpProtocol' => stripos($req->getHttpProtocol(), 'https') === false ? 'http' : 'https',
                'requestUri' =>  $req->getRequestUri(),
                'serverHost' => $req->getServerHost(),
                'urlParams' => $req->urlParams,
                'pathInfo' => $req->getPathInfo(),
                'appName' => null,
                'userId' => self::getUID(),
                'urlFull' => \OC::$server->getURLGenerator()->getAbsoluteURL('/'),
            ];

            if(count($parts) > 0) {
                foreach($parts as $key => $value) {
                    $dataDefault[$key] = $value;
                }
            }
            $data = $dataDefault;
        }

        if(is_string($parts) && !empty($parts)) {
            return isset($data[$parts]) ? $data[$parts] : null;
        }else
            return $data;
    }







}
