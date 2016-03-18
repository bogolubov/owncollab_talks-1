<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Talks\Db;


class Users
{
    /** @var  Connect */
    private $connect;

    private $tableName;

    private $user;

    public function __construct($connect, $tableName) {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }

    public function getAll() {
        $users = $this->connect->queryAll("SELECT * FROM ".$this->tableName." ORDER BY displayname, uid");
        return $users;
    }

    public function getById($id) {
        $user = $this->connect->select("*", $this->tableName, "id = :id",[':id' => 1]);
        return $user;
    }

    public function save($data) {
        $user = $this->connect->insert($this->tableName, $data);
        return $user;
    }

    /**
     * Retrieve all registered resource
     *
     * @return array|null
     */
    public function getGroupsUsersList(){

        $records = $this->getGroupsUsers();
        $result = [];

        // Operation iterate and classify users into groups
        foreach($records as $record){
            $result[$record['gid']][] = [
                'gid'=> $record['gid'],
                'uid'=> $record['uid'],
                'displayname'=> ($record['displayname'])?$record['displayname']:$record['uid']
            ];
        }
        return $result;
    }


    /**
     * Retrieve all records from Users
     *
     * @return mixed
     */
    public function getGroupsUsers() {

        $sql = "SELECT gu.uid, gu.gid, u.displayname
                FROM *PREFIX*group_user gu
                LEFT JOIN ".$this->tableName." u ON (u.uid = gu.uid)";

        return $this->connect->queryAll($sql);
    }

    /**
     * Get user by name and group
     * @param $user string
     * @param $group string
     * @return array
     */
    public function getByGroup($user, $group) {

        $sql = "SELECT uid, gid
                FROM *PREFIX*group_user gu
                WHERE uid = '".$user."' AND gid = '".$group."'";

        return $this->connect->queryAll($sql);
    }

    /**
     * Get deatails of user
     * @param $user string
     * @return array
     */
    public function getUserDetails($user) {

        $sql = "SELECT *
                FROM *PREFIX*preferences
                WHERE userid = '".$user."'";

        $res = $this->connect->queryAll($sql);
        $userinfo = array();
        foreach ($res as $r => $row) {
            $userinfo[$row['appid']][] = [$row['configkey'] => $row['configvalue']];
        }
        return $userinfo;
    }
}