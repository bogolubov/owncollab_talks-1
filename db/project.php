<?php

namespace OCA\Owncollab_Talks\Db;


class Project
{
    /** @var  Connect */
    private $connect;
    /** @var string table name */
    private $tableName;

    /**
     * Project constructor.
     * @param $connect
     * @param $tableName
     */
    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    /**
     * Get one record by id
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        $project = $this->connect->select("*", $this->tableName, "id = :id",[':id' => $id]);
        return $project;
    }

    /**
     * Get one record by name
     * @param $name
     * @return mixed
     */
    public function findByName($name){
        $sql = "SELECT * , DATE_FORMAT( `start_date`, '%d-%m-%Y %H:%i:%s') as start_date
                FROM `{$this->tableName}` WHERE `name` = ?";
        return $this->connect->query($sql,[$name]);
    }

}