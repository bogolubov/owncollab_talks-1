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
        if (is_array($file) && count($file)) {
            $file = $file[0];
            $file['id'] = $file['fileid'];
        }
        return is_array($file) ? $file : null;
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
            $file['icon'] = \OC::$server->getMimeTypeDetector()->mimeTypeIcon($file['mimetype']);
        }

        return  $file;
    }

    public function getInfoByIds($ids)
    {
        $ids = is_array($ids) ? $ids : (is_numeric($ids) ? [$ids] : false );

        if (!is_array($ids))
            return [];

        $sql = "SELECT * FROM oc_filecache f
                LEFT JOIN *PREFIX*activity a ON (a.object_id = f.fileid)
                LEFT JOIN *PREFIX*mimetypes m ON (m.id = f.mimetype)
                WHERE f.fileid IN (". join(',',array_fill(0, count($ids), '?')) .")";

        $files = $this->connect->queryAll($sql, $ids);

        if ($files)
            for ($i=0; $i < count($files); $i ++)
                if (is_array($files[$i])) {
                    $files[$i]['fullpath'] = \OC::$SERVERROOT.'/data/'.$files[$i]['user'].'/'.$files[$i]['path'];
                    $files[$i]['icon'] = \OC::$server->getMimeTypeDetector()->mimeTypeIcon($files[$i]['mimetype']);
                }

        return  $files;
    }


    /*

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
    */

    /*
        public function getFolderPath($folderId) {

            $folderId = !is_numeric($folderId) ? substr($folderId, 7) : $folderId;
            return $folrId;

            /*$sql = "SELECT path FROM ".$this->tableName." WHERE fileid = ?";
            $folder = $this->connect->query($sql, [$folderId]);

            $path = explode('/', $folder['path']);

            unset($path[0]);
            return implode('/', $path);
        }*/




/*    public function getByIdList($idlist, $user) {
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
    */
/*
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
    }*/


    /**
     * @param $fid
     * @param $uid
     * @param $uidwith
     * @param $permission
     * @return bool|string
     * @throws \Exception
     * @throws \OC\HintException
     */
    public function shareFile($uid, $uidwith, $fid, $permission = 1)
    {
        $isEnabled = \OCP\Share::isEnabled();
        $isAllowed = \OCP\Share::isResharingAllowed();
        $sharedWith = \OCP\Share::getUsersItemShared('file', $fid, $uid, false, true);
        $sharedUsers = is_array($sharedWith) ? array_values($sharedWith) : [];

        if($isEnabled && $isAllowed && !in_array($uidwith, $sharedUsers)) {

            // \OCP\Constants::PERMISSION_READ
            // \OCP\Constants::PERMISSION_ALL
            // \OCP\Share::SHARE_TYPE_LINK
            // \OCP\Share::SHARE_TYPE_USER,

            $shareIsSuccess = \OC\Share\Share::shareItem(
                'file',
                $fid,
                \OCP\Share::SHARE_TYPE_USER,
                $uidwith,
                $permission
            );

            if($shareIsSuccess) {

                $this->connect->update('*PREFIX*share', ['uid_initiator' => $uid],
                    'share_with = :share_with AND uid_owner = :uid_owner AND file_source = :file_source', [
                        ':share_with' => $uidwith,
                        ':uid_owner' => $uid,
                        ':file_source' => $fid,
                    ]);

                $token = \OC\Share\Share::shareItem(
                    'file',
                    $fid,
                    \OCP\Share::SHARE_TYPE_LINK,
                    $uidwith,
                    $permission
                );

                $this->connect->update('*PREFIX*share', ['uid_initiator' => $uid, 'share_with' => null],
                    'uid_owner = :uid_owner AND file_source = :file_source AND token = :token', [
                        ':uid_owner' => $uid,
                        ':file_source' => $fid,
                        ':token' => $token,
                    ]);

                return $token;
            }
        }

        return false;
    }



    public function insertShare(array $shareData)
    {
        $query = \OC_DB::prepare('INSERT INTO `*PREFIX*share` ('
            .' `item_type`, `item_source`, `item_target`, `share_type`,'
            .' `share_with`, `uid_owner`, `uid_initiator`, `permissions`, `stime`, `file_source`,'
            .' `file_target`, `token`, `parent`, `expiration`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

        $query->bindValue(1, $shareData['itemType']);
        $query->bindValue(2, $shareData['itemSource']);
        $query->bindValue(3, $shareData['itemTarget']);
        $query->bindValue(4, $shareData['shareType']);
        $query->bindValue(5, $shareData['shareWith']);
        $query->bindValue(6, $shareData['uidOwner']);
        $query->bindValue(7, $shareData['uidOwner']);
        $query->bindValue(8, $shareData['permissions']);
        $query->bindValue(9, $shareData['shareTime']);
        $query->bindValue(10, $shareData['fileSource']);
        $query->bindValue(11, $shareData['fileTarget']);
        $query->bindValue(12, $shareData['token']);
        $query->bindValue(13, $shareData['parent']);
        $query->bindValue(14, $shareData['expiration'], 'datetime');

        try {

            $result = $query->execute();

            $id = false;
            if ($result) {
                $id =  \OC::$server->getDatabaseConnection()->lastInsertId('*PREFIX*share');
            }

            return $id;

        } catch (\Exception $e) {

            return $e;
        }

    }

    public function getFileLink($fid, $uid)
    {
        $link = '';
        $file = $this->getInfoById($fid);
        if ($file['user'] == $uid) {
            $sql = "SELECT *
                FROM owncloud91.oc_share s
                WHERE s.item_type = 'file' AND s.item_source = ? AND s.uid_owner = ?";

            $file = $this->connect->query($sql, [$fid, $uid]);
            if ($file)
                $link = $file['file_target'];
        } else
            $link = $file['file'];

        return 'remote.php/webdav' . $link;
    }







}