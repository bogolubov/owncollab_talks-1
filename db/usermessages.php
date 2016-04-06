<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Talks\Db;


class UserMessages
{
    /** @var  Connect */
    private $connect;

    private $tableName;

    private $user;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getById($id) {
        $message = $this->connect->select("*", $this->tableName, "id = :id",[':id' => 1]);
        return $message;
    }

    public function getAll() {
        $sql = "SELECT um.id as id, m.id as messageid, m.date, m.title, m.text, m.attachements, m.author, m.subscribers, um.status".
            " FROM " . $this->tableName . " um".
            " INNER JOIN oc_collab_messages m ON m.id = um.mid".
            " WHERE m.rid = 0".
            " GROUP BY um.mid".
            " ORDER BY m.date DESC";
        $messages = $this->connect->queryAll($sql);
        return $messages;
    }

    public function getMessageById($id, $user = NULL) {
        $userid = !empty($user) ? $user : $this->user;
        $sql = "SELECT um.id as id, m.date, m.title, m.text, m.attachements, m.author, m.subscribers, um.mid as mid, um.status".
                " FROM oc_collab_user_message um".
                " INNER JOIN oc_collab_messages m ON m.id = um.mid".
                " WHERE um.mid = " . $id . " AND um.uid = '" . $userid . "'".
                " ORDER BY m.date DESC";
        $message = $this->connect->query($sql);
        return $message;
    }

    public function getBySubscriber($subscriber = NULL, $parent = NULL) {
        $userid = !empty($subscriber) ? $subscriber : $this->user;
        if ($userid) {
            $sql = "SELECT um.id as id, m.id as messageid, m.date, m.title, m.text, m.attachements, m.author, m.subscribers, um.status".
                    " FROM " . $this->tableName . " um".
                    " INNER JOIN oc_collab_messages m ON m.id = um.mid".
                    " WHERE um.uid = '" . $userid . "' AND NOT (m.author = '" . $userid . "')";
                if (!($parent == NULL)) {
                    $sql .= " AND m.rid = ".$parent;
                }
                    $sql .= " GROUP BY m.id".
                    " ORDER BY m.date DESC";
            $messages = $this->connect->queryAll($sql);
            return $messages;
        }
        else {
            return false;
        }
    }

    public function getByAuthorOrSubscriber($subscriber = NULL, $parent = NULL) {
        $userid = !empty($subscriber) ? $subscriber : $this->user;
        if ($userid) {
            $sql = "SELECT um.id as id, m.id as messageid, m.date, m.title, m.text, m.attachements, m.author, m.subscribers, um.status".
                " FROM " . $this->tableName . " um".
                " INNER JOIN oc_collab_messages m ON m.id = um.mid".
                " WHERE (m.author = '" . $userid . "' OR m.subscribers LIKE '%" . $userid . "%')";
            if (!($parent == NULL)) {
                $sql .= " AND m.rid = ".$parent;
            }
            $sql .= " GROUP BY m.id".
                " ORDER BY m.date DESC";
            $messages = $this->connect->queryAll($sql);
            return $messages;
        }
        else {
            return false;
        }
    }

    public function getUserStatus($mid) {
        if ($mid) {
            $users = array();
            $userlist = $this->connect->select('uid, status', $this->tableName, 'mid = '.$mid);
            foreach ($userlist as $u => $user) {
                $users[$user['uid']] = $user['status'];
            }
            return $users;
        }
        else {
            return false;
        }
    }

    public function canRead($message) {
        $subscribers = explode(',', $message['subscribers']);
        if (in_array($this->user, $subscribers)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function createStatus($message, $user) {
        $status = $this->connect->insert($this->tableName, ['uid' => $user, 'mid' => $message, 'status' => 0]);
        return $status;
    }

    public function setStatus($message, $status = NULL) {
        $id = is_array($message) && $message['id'] ? $message['id'] : $message;
        if (isset($status) && $status) {
            $mstatus = $status;
        }
        elseif (is_array($message) && $message['status'] >= 0) {
            $mstatus = $message['status'];
        }
        else {
            return;
        }
        $message = $this->connect->update($this->tableName, ['status' => $mstatus], 'id = '.$id);
        return $message;
    }

    public function save($data) {
        $message = $this->connect->insert($this->tableName, $data);
        return $message;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function delete($where, $and = NULL) {
        $message = $this->connect->delete($this->tableName, $where.' AND '.$and);
        return $message;
    }
}