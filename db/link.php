<?php
/**
 * Tables models
 */

namespace OCA\Owncollab_Talks\Db;


class Link
{
    /** @var  Connect */
    private $connect;

    private $tableName;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getById($id) {
        $project = $this->connect->select("*", $this->tableName, "id = :id",[':id' => $id]);
        return $project;
    }

}