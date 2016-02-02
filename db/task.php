<?php
/**
 * Created by PhpStorm.
 * User: werd
 * Date: 28.01.16
 * Time: 22:29
 */

namespace OCA\Owncollab_Talks\Db;


class Task
{
    /** @var  Connect */
    private $connect;

    private $tableName;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getById($id) {
        $project = $this->connect->select("*", $this->tableName, "id = :id",[':id' => 1]);
        return $project;
    }

}