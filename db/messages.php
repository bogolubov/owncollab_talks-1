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
    private $connect;

    private $tableName;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getById($id) {
        $message = $this->connect->select("*", $this->tableName, "id = :id",[':id' => $id]);
        return $message;
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

    public function getByAuthor($author) {
        $sql = "SELECT * FROM ". $this->tableName ." WHERE author = '".$author."'";
        $messages = $this->connect->queryAll($sql);
        return $messages;
    }

    public function setStatus($id, $status) {
        $message = $this->connect->update($this->tableName, ['status' => $status], 'id = '.$id);
        return $message;
    }

    public function canRead($message, $user) {
        $subscribers = explode(',', $message['subscribers']);
        if (in_array($user, $subscribers)) {
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