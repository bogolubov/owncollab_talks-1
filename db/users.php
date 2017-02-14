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
    /**
     * @var  Connect $connect
     */
    private $connect;


    /**
     * @var string
     */
    private $tableName;


    /**
     * Users constructor.
     * @param $connect
     * @param $tableName
     */
    public function __construct($connect, $tableName)
    {
        $this->connect = $connect;
        $this->tableName = '*PREFIX*' . $tableName;
    }


    /**
     * @return mixed
     */
    public function getAll()
    {
        $users = $this->connect->queryAll("SELECT * FROM " . $this->tableName . " ORDER BY displayname, uid");
        return $users;
    }

    public function getUsersIDs()
    {
        $users = $this->connect->queryAll("SELECT * FROM " . $this->tableName . " ORDER BY displayname, uid");
        $result = [];
        for ($i=0; $i<count($users); $i++) {
            $result[] = $users[$i]['uid'];
        }
        return $result;
    }

    /**
     * @return mixed
     */
    public function getAllGroups()
    {
        $groups = $this->connect->queryAll("SELECT * FROM *PREFIX*groups ORDER BY gid");
        return $groups;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $user = $this->connect->select("*", $this->tableName, "uid = :id", [':id' => $id]);
        return $user;
    }


    public function getByEmail($email)
    {
        $sql = "SELECT *
                FROM *PREFIX*preferences
                WHERE appid = 'settings' AND configkey = 'email' AND configvalue = :email";

        return $this->connect->query($sql, [':email'=>$email]);
    }


    /**
     * Retrieve all records from Users
     *
     * @return mixed
     */
    public function getGroupsUsers()
    {
        $sql = "SELECT gu.uid, gu.gid, u.displayname, p.configvalue as email
                FROM *PREFIX*group_user gu
                LEFT JOIN *PREFIX*users u ON (u.uid = gu.uid)
                LEFT JOIN *PREFIX*preferences p ON (p.userid = gu.uid AND p.appid = 'settings' AND p.configkey = 'email')";

        return $this->connect->queryAll($sql);
    }


    /**
     * Retrieve all registered resource
     *
     * @param bool $refresh
     * @return array|null [0][gid]=>[gid,uid,displayname,email]
     */
    public function getGroupsUsersList($refresh = false)
    {
        $result = [];
        static $records = null;
        if($records === null || $refresh)
            $records = $this->getGroupsUsers();

        // Operation iterate and classify users into groups
        foreach ($records as $record) {
            $result[$record['gid']][] = [
                'email' => $record['email'],
                'gid' => $record['gid'],
                'uid' => $record['uid'],
                'displayname' => ($record['displayname']) ? $record['displayname'] : $record['uid']
            ];
        }
        return $result;
    }


    /**
     * @param $uid
     * @param bool $refresh
     * @return bool
     */
    public function getUserData($uid, $refresh = false)
    {
        static $usersData = null;

        if($usersData === null || $refresh)
            $usersData = $this->getGroupsUsers();

        if(is_array($usersData)) {
            for($i=0;$i<count($usersData);$i++) {
                if($usersData[$i]['uid'] == $uid) {
                    if(empty($usersData[$i]['displayname'])) $usersData[$i]['displayname'] = $uid;
                    return $usersData[$i];
                }
            }
        }
        return false;
    }

    /**
     * @param bool $refresh
     * @return mixed|null
     */
    public function getUngroupUsers($refresh = false)
    {
        static $usersData = null;

        if($usersData === null || $refresh) {
            $sql = "SELECT u.uid, u.displayname, p.configvalue as email
                    FROM *PREFIX*users u
                    LEFT OUTER JOIN *PREFIX*group_user gu ON (gu.uid = u.uid)
                    LEFT JOIN *PREFIX*preferences p ON (p.userid = u.uid AND p.appid = 'settings' AND p.configkey = 'email')
                    WHERE gu.uid IS NULL";

            $usersData = $this->connect->queryAll($sql);
        }
        return $usersData;
    }

    public function getUngroupUsersList($refresh = false){}

    public function getUsersDataByIds($ids, $refresh = false)
    {
        static $usersData = null;

        if($usersData === null || $refresh) {
            $ins = '';
            foreach ($ids as $id) {
                if (empty($ins)) $ins .= '?';
                else  $ins .= ',?';
            }

            $sql = "SELECT u.uid, u.displayname, p.configvalue as email
            FROM *PREFIX*users u
            LEFT JOIN *PREFIX*preferences p ON (p.userid = u.uid AND p.appid = 'settings' AND p.configkey = 'email')
            WHERE u.uid IN ($ins)";

            $usersData = $this->connect->queryAll($sql, $ids);
        }
        return $usersData;
    }

}