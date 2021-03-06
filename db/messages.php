<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 08.02.16
 * Time: 14:08
 */

namespace OCA\Owncollab_Talks\Db;


class Messages
{
    /** @var  Connect */
    protected $connect;

    protected $tableName;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getById($id) {
        $message = $this->connect->select("*", $this->tableName, "id = :id",[':id' => $id]);
        return $message;
    }

    public function getTalkByHash($hash) {
        $talk = $this->connect->query("SELECT * FROM ".$this->tableName." WHERE hash LIKE '".$hash."%' AND rid = 0");
        return $talk;
    }

    public function getByReply($id) {
        $sql = "SELECT um.id as id, um.mid as mid, m.date, m.title, m.text, m.attachements, m.author, m.subscribers, um.status".
            " FROM oc_collab_user_message um".
            " INNER JOIN oc_collab_messages m ON m.id = um.mid".
            " WHERE um.mid = " . $id .
            " ORDER BY m.date DESC";
        $message = $this->connect->query($sql);
        return $message;
    }

    public function getBySubscriber($subscriber) {
        $sql = "SELECT * FROM ". $this->tableName ." WHERE subscribers like '%".$subscriber."%'";
        $messages = $this->connect->queryAll($sql);
        return $messages;
    }

    public function getByAuthor($author, $parent = NULL, $orderby = false) {
        $sql = "SELECT * FROM ". $this->tableName ." WHERE author = '".$author."'";
        if (isset($parent)) {
            $sql .= " AND rid = ".$parent;
        }
        if ($orderby) {
            $sql .= " ORDER BY ".$orderby;
        }
        $messages = $this->connect->queryAll($sql);
        return $messages;
    }

    public function getByParent($parent, $order = NULL) {
        $sql = "SELECT * FROM ". $this->tableName ." WHERE rid = '".$parent."'";
        if ($order) {
            $sql .= " ORDER BY ".$order;
        }
        else {
            $sql .= " ORDER BY date DESC";
        }
        $messages = $this->connect->queryAll($sql);
        return $messages;
    }

    public function getMessageTopParent($id) {
        $rid = $id;
        if ($rid > 0) {
            while ($rid > 0) {
                $parent = $rid;
                $res = $this->connect->select("rid", $this->tableName, "id = :id", [':id' => $rid]);
                $rid = $res[0]['rid'];
            }
        }
        return $parent;
    }

    public function setStatus($id, $status) {
        $message = $this->connect->update($this->tableName, ['status' => $status], 'id = '.$id);
        return $message;
    }

    public function canRead($message, $user, $subscribers = NULL) {
        if (empty($subscribers)) {
            $subscribers = explode(',', $message['subscribers']);
        }
        if (in_array($user, $subscribers) || $message['author'] == $user) {
            return true;
        }
        else {
            return false;
        }
    }

    public function canAnswer($message, $user) {
        if (stristr($message['subscribers'], $user) || $message['author'] == $user) {
            return true;
        }
        else {
            return false;
        }
    }

    public function save($data) {
        $message = $this->connect->insert($this->tableName, $data);
        return $message;
    }

    public function update($data) {
        $this->connect->update($this->tableName, $data, 'id = '.$data['id']);
    }
}