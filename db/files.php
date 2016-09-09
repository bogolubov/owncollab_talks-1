<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Talks\Db;


class Files
{
    /** @var  Connect */
    private $connect;

    /** @var  string */
    private $tableName;

    /** @var  \OCA\Owncollab_Talks\Db\Users */
    private $modelUsers;

    /** @var  \OCA\Owncollab_Talks\Db\Messages */
    private $modelMessages;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
        $this->modelUsers = $this->connect->users();
        $this->modelMessages = $this->connect->messages();
        //$this->modelFiles = $this->connect->files();
    }

    /**
     * Get a many entries
     * @return array|null
     */
    public function getAll() {
        $files = $this->connect->queryAll("SELECT * FROM ".$this->tableName." ORDER BY displayname, uid");
        return $files;
    }

    /**
     * Get one record
     * @param $id
     * @return null
     */
    public function getById($id) {
        $file = $this->connect->select("*", $this->tableName, "fileid = :id",[':id' => $id]);
        return is_array($file) ? $file[0] : null;
    }

    public function getInfoById($id)
    {
        $sql = "SELECT * FROM oc_filecache f
                LEFT JOIN *PREFIX*activity a ON (a.object_id = f.fileid)
                LEFT JOIN *PREFIX*mimetypes m ON (m.id = f.mimetype)
                WHERE f.fileid = :id";

        $file = $this->connect->query($sql, [':id' => $id]);

        if (is_array($file)) {
            $file['fullpath'] = \OC::$SERVERROOT.'/data/'.$file['user'].'/'.$file['path'];
        }

        return  $file;
    }


    public function getByUser($user) {
        $sql = "SELECT activity_id, timestamp, priority, type, user, affecteduser, app, subject, subjectparams, message, messageparams, file, link, object_type, object_id,  fileid, storage, path, path_hash, parent, name, f.mimetype as mimeid, m.mimetype as mimetype, mimepart, size, mtime, storage_mtime, encrypted, unencrypted_size, etag, permissions " .
                " FROM oc_activity a" .
                " INNER JOIN oc_filecache f ON f.fileid = a.object_id" .
                " INNER JOIN oc_mimetypes m ON m.id = f.mimetype" .
                " WHERE user = '".$user."' AND f.parent <= 2" .
                " GROUP BY f.path";
        $files = $this->connect->queryAll($sql);
        $filtered = $this->filterDeleted($files, $user);
        return $filtered;
    }


    public function getByFolder($folder, $user) {
        //echo $folder;
        $sql = "SELECT activity_id, timestamp, priority, type, user, affecteduser, app, subject, subjectparams, message, messageparams, file, link, object_type, object_id,  fileid, storage, path, path_hash, parent, name, f.mimetype as mimeid, m.mimetype as mimetype, mimepart, size, mtime, storage_mtime, encrypted, unencrypted_size, etag, permissions " .
            " FROM oc_activity a" .
            " INNER JOIN oc_filecache f ON f.fileid = a.object_id" .
            " INNER JOIN oc_mimetypes m ON m.id = f.mimetype" .
            " WHERE user = '".$user."' AND f.parent = ".substr($folder, 7) .
            //" WHERE user = '".$user."' AND f.parent = 4" .
            " GROUP BY f.path";
        $files = $this->connect->queryAll($sql);
        $filtered = $this->filterDeleted($files, $user);
        return $filtered;
    }



    public function getFolderPath($folderId) {

        $folderId = !is_numeric($folderId) ? substr($folderId, 7) : $folderId;
        return $folderId;

        /*$sql = "SELECT path FROM ".$this->tableName." WHERE fileid = ?";
        $folder = $this->connect->query($sql, [$folderId]);

        $path = explode('/', $folder['path']);

        unset($path[0]);
        return implode('/', $path);*/
    }




    public function getByIdList($idlist, $user) {
        if (is_array($idlist)) {
            $sql = "SELECT fileid, path, name, mimetype, size, storage_mtime".
                    " FROM oc_filecache fc".
                    " INNER JOIN oc_share s ON s.file_source = fc.fileid".
                    " WHERE (s.share_with = '".$user."' OR s.uid_owner = '".$user."') AND fileid IN (".implode(',',$idlist).")".
                    " GROUP BY fileid";
            $files = $this->connect->queryAll($sql);
            return $files;
        }
        else {
            $sql = "SELECT fileid, path, name, mimetype, size, storage_mtime".
                " FROM oc_filecache fc".
                " INNER JOIN oc_share s ON s.file_source = fc.fileid".
                " WHERE (s.share_with = '".$user."' OR s.uid_owner = '".$user."') AND fileid = ".$idlist;
            $file[] = $this->connect->query($sql);
            return $file;
        }
    }

    public function save($data) {
        $file = $this->connect->insert($this->tableName, $data);
        return $file;
    }

    private function filterDeleted($files, $user) {
        $sql = "SELECT * " .
                " FROM oc_activity " .
                " WHERE user = '".$user."' AND type = 'file_deleted'";
        $deletedfiles = $this->connect->queryAll($sql);
        $diff_keys = array();
        foreach ($files as $f => $file) {
            if (in_array($file['file'], array_column($deletedfiles, 'file'))) {
                $diff_keys[] = $f;
            }
        }
        $filtered = array_diff_key($files, $diff_keys);
        return $filtered;
    }

    public function getIcon($mimeid) {
        $mimetype = $this->getMimeType($mimeid)[0]['mimetype'];
        $filetypes = array('httpd' => 'file', 'httpd/unix-directory' => 'folder', 'application' => 'application', 'application/pdf' => 'application-pdf', 'application/vnd.oasis.opendocument.text' => 'text', 'image' => 'image', 'image/jpeg' => 'image', 'application/octet-stream' => 'text-code', 'text' => 'text', 'text/plain' => 'text', 'image/png' => 'image', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'x-office-document', 'application/vnd.oasis.opendocument.spreadsheet' => 'x-office-spreadsheet', 'application/vnd.oasis.opendocument.presentation' => 'x-office-presentation', 'audio' => 'audio', 'audio/mpeg' => 'audio', 'video' => 'video', 'video/x-msvideo' => 'video', 'application/msword' => 'x-office-document');
        return $filetypes[$mimetype];
    }

    public function getMimeType($id) {
        $mimetype = $this->connect->select("mimetype", "oc_mimetypes", "id = :id",[':id' => $id]);
        return $mimetype;
    }

    /**
     * Inserts uploaded file into database
     * @param array $file
     */
    public function newFile($file, $path) {
        $sql = "SELECT id FROM *PREFIX*mimetypes WHERE mimetype = '".$file['mimetype']."'";
        $res = $this->connect->query($sql);
        $mimetype = $res['id'];

        $sql = "SELECT numeric_id FROM *PREFIX*storages WHERE id = 'home::".$file['owner']."'";
        $res = $this->connect->query($sql);
        $storageid = $res['numeric_id'];

        $sql = "SELECT fileid FROM *PREFIX*filecache WHERE path = '".$file['path']."'";
        $res = $this->connect->query($sql);
        $parent = $res['fileid'];

	//$path = '/var/www/owncloud.loc/data/admin/files/Photos/San Francisco.jpg';
	list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath($path.'/'.$file['filename']);
	$etag = $storage->getETag($internalPath);
	
	$data = array(
            'storage' => $storageid,
            'path' => $file['path'].'/'.$file['filename'],
            'path_hash' => md5($file['path'].'/'.$file['filename']),
            'parent' => $parent,
            'name' => $file['filename'],
            'mimetype' => $mimetype,
            'mimepart' => 5,
            'size' => $file['size'],
            'mtime' => $file['mtime'],
            'storage_mtime' => $file['storage_mtime'],
            'etag' => $etag,
            'permissions' => \OCP\Constants::PERMISSION_ALL
            );
        $filecache = $this->save($data);

        /* file_put_contents('/tmp/inb.log', "\nPath : /\n", FILE_APPEND);
        try { 
		$fileInfo = \OC\Files\Filesystem::getFileInfo('/', false); 
        } 
        catch (\Exception $e) {
		file_put_contents('/tmp/inb.log', "\nGet File Info error : " . $e->getMessage() . "\n", FILE_APPEND);
	}
        file_put_contents('/tmp/inb.log', "\nFile info : " . print_r($fileInfo, true) . "\n", FILE_APPEND);
        try { 
		$icon = \OCA\Files\Helper::determineIcon($fileInfo); 
        } 
        catch (\Exception $e) {
		file_put_contents('/tmp/inb.log', "\nDetermine Icon error : " . $e->getMessage() . "\n", FILE_APPEND);
	} */ 
	
        $activity = array(
                    'activity_id' => NULL,
                    'timestamp' => $file['mtime'],
                    'priority' => 30,
                    'type' => 'file_created',
                    'user' => $file['owner'],
                    'affecteduser' => $file['owner'],
                    'app' => 'files',
                    'subject' => 'created_self',
                    'subjectparams' => '["\/'.$file['filename'].'"]',
                    'message' => '',
                    'messageparams' => '[]',
                    'file' => '/'.$file['filename'],
                    'link' => $file['domain'].'/index.php/apps/files?dir=%2F',
                    'object_type' => 'files',
                    'object_id' => $filecache
                    );
        $id = $this->connect->insert('*PREFIX*activity', $activity);
        if ($id) {
            return $filecache;
        }
        else {
            return false;
        }
    }


    /**
     * @param $fid
     * @param $uid
     * @param $wUid
     * @return bool|string
     * @throws \Exception
     * @throws \OC\HintException
     */
    public function shareFile($uid, $wUid, $fid, $permission = 1)
    {
        $isEnabled = \OCP\Share::isEnabled();
        $isAllowed = \OCP\Share::isResharingAllowed();
        $sharedWith = \OCP\Share::getUsersItemShared('file', $fid, $uid, false, true);

        //$file = $this->connect->files()->getInfoById($fid);
        if($isEnabled && $isAllowed && !in_array($wUid, $sharedWith)) {

            // \OCP\Constants::PERMISSION_READ
            // \OCP\Constants::PERMISSION_ALL
            // \OCP\Share::SHARE_TYPE_LINK
            // \OCP\Share::SHARE_TYPE_USER,

            $shareIsSuccess = \OC\Share\Share::shareItem(
                'file',
                $fid,
                \OCP\Share::SHARE_TYPE_USER,
                $wUid,
                $permission
            );

            $shareIsSuccess = \OC\Share\Share::shareItem(
                'file',
                $fid,
                \OCP\Share::SHARE_TYPE_LINK,
                $wUid,
                $permission
            );

            if($shareIsSuccess) {

                $result = $this->connect->update('*PREFIX*share', ['uid_initiator' => $uid],
                    'share_with = :share_with AND uid_owner = :uid_owner AND file_source = :file_source', [
                        ':share_with' => $wUid,
                        ':uid_owner' => $uid,
                        ':file_source' => $fid,
                    ]);

            }

            return $shareIsSuccess;
        }

    }
}