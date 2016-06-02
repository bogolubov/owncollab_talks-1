<?php
/**
 * ownCloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Your Name <mail@example.com>
 * @copyright Your Name 2016
 */

namespace OCA\Owncollab_Talks\AppInfo;

use OC\Files\Filesystem;
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\Helper;

class TempFiles
{

    private $connect;
    private $Messages;
    private $Users;
    private $Files;

    private $userId;
    private $dir;
    private $file = [
        'filename' => '',
        'messageId' => '',
        'author' => '',
        'subscribers' => ''
        ];
    private $fileMessage = NULL;

    private $fileAuthor;
    private $localpath = "files";
    private $datadir;
    private $filesList = array();
    private $sharedList = array();

    /**
     * MainController constructor.
     */
    public function __construct($userid) {
        $this->connect = new Connect(Helper::getConnection());
        $this->Messages = $this->connect->messages();
        $this->Users = $this->connect->users();
        $this->Files = $this->connect->files();

        $this->userId = $userid; 
        $this->datadir = $_SERVER['DOCUMENT_ROOT']."/data";

        $this->readTmpDir('tmp');
        if (!empty($this->filesList)) {
            $this->readFiles();
        }
        if (!empty($this->sharedList)) {
            $this->addFilesToMessage();
        }
    }

    private function readTmpDir($dirname = 'tmp') {
        $dir = realpath(dirname(__DIR__)).'/'.$dirname;
        $this->dir = $dir;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (!($file == "." || $file == "..")) {
                        $this->filesList[] = $file;
                    }
                }
                closedir($dh);
            }
        }
    }

    private function readFiles() {
        foreach ($this->filesList as $fl => $item) {
            $filename = substr($item, 0, strpos($item, '+'));
            $messageId = substr($item, strpos($item, '+')+1);
            if (!$messageId || $messageId < 1 || !is_numeric($messageId)) {
                break;
            }
            if (file_exists($this->dir.'/'.$item)) {
                $file = $this->getFileParams($filename, $messageId);
                if ($file['author'] == $this->userId) { 
		    //file_put_contents('/tmp/inb.log', "File : \n".print_r($file, true)."\n", FILE_APPEND);
		    $this->fileAuthor = $file['author'];
		    $this->renameFile($item, $filename);
		    $uploadedFile = $this->uploadFile($file);
		    $fileid = $this->chownFile($uploadedFile);
		    $uploaddir = $this->datadir."/".$this->fileAuthor."/".$this->localpath;
		    //file_put_contents('/tmp/inb.log', "uploaddir : \n".$uploaddir."\n", FILE_APPEND);
		    $this->shareFile($fileid, $file['subscribers'], $uploaddir);
		    $this->deleteFile($this->dir . '/' . $filename);
                } 
            }
        }
    }

    private function getFileParams($filename, $messageId) {
        $message = $this->Messages->getById($messageId)[0];
        $this->fileMessage = $message;
        $file = array(
            'filename' => $filename,
            'messageId' => $messageId,
            'author' => $message['author'],
            'subscribers' => $message['subscribers']
        );
        return $file;
    }

    private function renameFile($file, $newname) {
        rename($this->dir.'/'.$file, $this->dir.'/'.$newname);
    }

    private function uploadFile($file) {
        $fullPath = $this->datadir."/".$this->fileAuthor."/".$this->localpath;
        $jpgBase64 = base64_encode(file_get_contents($this->dir.'/'.$file['filename']));
        $storage = new \OC\Files\Storage\Local(["datadir" => $fullPath]);
        $result = $storage->file_put_contents($file['filename'], base64_decode($jpgBase64));
        return [
                'storage' => $result,
                'uploadpath' => $fullPath,
                'path' => $this->localpath,
                'filename' => $file['filename'],
                'uid' => $this->fileAuthor
                ];
    }

    private function uploadFileBkp($file) {
        //$token = \OC::$server->getSecureRandom()->getMediumStrengthGenerator()->generate(self::TOKEN_LENGTH,
		//				\OCP\Security\ISecureRandom::CHAR_LOWER.\OCP\Security\ISecureRandom::CHAR_UPPER.
		//				\OCP\Security\ISecureRandom::CHAR_DIGITS
		//			);
        $fd = [
            //'requesttoken' => $_COOKIE['oc_sessionPassphrase'], //TODO Взяти десь requesttoken
            //'requesttoken' => $_SESSION['encripted_session_data'],
            'requesttoken'  => \OC_Util::callRegister(),
            //'requesttoken'  => $token,
            'dir' => '/',
            'file_directory' => 'Talks'
        ];
        //$fd['file'] = '@' . $this->dir.'/'.$file['filename'];
        $filename = $this->dir.'/'.$file['filename'];

        $path = realpath(dirname(dirname(dirname(__DIR__))));
        include $path . '/config/config.php';
        $url = $CONFIG['overwrite.cli.url'] . "/index.php/apps/files/ajax/upload.php";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fd);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $fh_res = fopen($filename, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $fh_res);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filename));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary

        $resultCurl = curl_exec($ch);
        fclose($fh_res);
        //print_r($resultCurl);
        file_put_contents('/tmp/inb.log', "\nCurl result : " . print_r($resultCurl, true) . "\n", FILE_APPEND);
        $errorCurl = curl_error($ch);
        //print_r($errorCurl);
        file_put_contents('/tmp/inb.log', "\nCurl error : " . print_r($errorCurl, true) . "\n", FILE_APPEND);
        curl_close($ch);

        return $resultCurl['id']; //TODO Взнати id файла
    }

    private function chownFile($file) {
        chown($this->dir.'/'.$file['filename'], $file['uid']);
        $mimetype = mime_content_type($file['uploadpath'].'/'.$file['filename']);
        $fp = fopen($file['uploadpath'].'/'.$file['filename'], "r");
        $fstat = fstat($fp);
        fclose($fp);
        $stat = array_slice($fstat, 13);
        $path = realpath(dirname(dirname(dirname(__DIR__)))); 
        $uploadedpath = $file['uploadpath']; 
        include $path . '/config/config.php';
        $domain = $CONFIG['overwrite.cli.url'];
        $file = [
                'storage' => $file['storage'],
                'path' => $file['path'],
                'filename' => $file['filename'],
                'mimetype' => $mimetype,
                'size' => $stat['size'],
                'mtime' => $stat['mtime'],
                'storage_mtime' => $stat['atime'],
                'owner' => $file['uid'],
                'domain' => $domain
                ];
        $fileid = $this->Files->newFile($file, $uploadedpath);
        if ($fileid) {
            return $fileid;
        }
        else {
            return false;
        }
    }

    private function shareFile($id, $subscribers, $path) {
        $file = $this->Files->getById($id)[0];
        if ($file && is_array($file) && !empty($file)) {
            //$fileOwner = \OC\Files\Filesystem::getOwner($file['path']);
            $fileOwner = $this->fileAuthor;
            //$sharetype = $file['mimetype'] == 2 ? 'folder' : 'file';
            foreach (explode(',', $subscribers) as $userid) {
                if (isset($file['fileid']) && is_numeric($file['fileid']) && !($userid == $fileOwner)) {
                    \OCP\Share::shareItem(
                        'file',
                        $file['fileid'],
                        \OCP\Share::SHARE_TYPE_USER,
                        $userid,
                        \OCP\Constants::PERMISSION_ALL
                    );
                    $this->sharedList[] = $file['fileid'];
                }
            }
        }
    }

    private function addFilesToMessage() {
        $updatedata = ['id' => $this->fileMessage['id'], 'attachements' => implode(',', $this->sharedList)];
        $this->Messages->update($updatedata);
    }

    private function deleteFile($file) {
        unlink($file);
    }
}