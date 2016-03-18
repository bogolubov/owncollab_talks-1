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
    /** @var  Connect
     * share_type - (int) ‘0’ = user; ‘1’ = group; ‘3’ = public link
     * share_with - (string) user / group id with which the file should be shared
     * permissions - (int) 1 = read; 2 = update; 4 = create; 8 = delete; 16 = share; 31 = all (default: 31, for public shares: 1)
     */
    private $connect;

    private $tableName;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getAll() {
        $files = $this->connect->queryAll("SELECT * FROM ".$this->tableName." ORDER BY displayname, uid");
        return $files;
    }

    public function getById($id) {
        $file = $this->connect->select("*", $this->tableName, "fileid = :id",[':id' => $id]);
        return $file;
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

    public function getByIdList($idlist, $user) {
        if (is_array($idlist)) {
            $sql = "SELECT fileid, path, name, mimetype, size".
                    " FROM oc_filecache fc".
                    " INNER JOIN oc_share s ON s.file_source = fc.fileid".
                    " WHERE (s.share_with = '".$user."' OR s.uid_owner = '".$user."') AND fileid IN (".implode(',',$idlist).")";
            $files = $this->connect->queryAll($sql);
            return $files;
        }
        else {
            $sql = "SELECT fileid, path, name, mimetype, size".
                " FROM oc_filecache fc".
                " INNER JOIN oc_share s ON s.file_source = fc.fileid".
                " WHERE (s.share_with = '.$user.' OR s.uid_owner = '.$user.') AND fileid = ".$idlist;
            $file = $this->connect->query($sql);
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
}