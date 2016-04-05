<?php

namespace OCA\Owncollab_Talks\Db;


use \OCP\IDBConnection;

class Connect
{
    /** @var IDBConnection  */
    public $db;
    /** @var Messages  database table */
    private $messages;
    /** @var UserMessage  database table */
    private $user_message;
    /** @var Users  database table */
    private $users;
    /** @var Files  database table */
    private $files;

    /**
     * Connect constructor.
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db) {
        $this->db = $db;

        // Register tables models
        $this->messages = new Messages($this, 'collab_messages');
        $this->user_message = new UserMessages($this, 'collab_user_message');
        $this->users = new Users($this, 'users');
        $this->files = new Files($this, 'filecache');
    }

    /**
     * Execute prepare SQL string $query with binding $params, and return one record
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function query($query, array $params = []) {
        return $this->db->executeQuery($query, $params)->fetch();
    }

    /**
     * Execute prepare SQL string $query with binding $params, and return all match records
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function queryAll($query, array $params = []) {
        return $this->db->executeQuery($query, $params)->fetchAll();
    }

    /**
     * Quick selected records
     * @param $fields
     * @param $table
     * @param null $where
     * @param null $params
     * @return mixed
     */
    public function select($fields, $table, $where = null, $params = null) {
        $sql = "SELECT " . $fields . " FROM " . $table . ($where ? " WHERE " . $where : "") . ";";
        return  $this->queryAll($sql, $params);
    }

    /**
     * Quick insert record
     * @param $table
     * @param array $columnData
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function insert($table, array $columnData) {
        $columns = array_keys($columnData);
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s);",
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columnData), '?'))
        );
        $this->db->executeQuery($sql, array_values($columnData));
        return $this->db->lastInsertId($table);
    }

    /**
     * Quick delete records
     * @param $table
     * @param $where
     * @param null $bind
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function delete($table, $where, $bind=null) {
        $sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
        return $this->db->executeQuery($sql, $bind);
    }

    /**
     * Quick update record
     * @param $table
     * @param $where
     * @param null $bind
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function update($table, array $columnData, $where, $bind=null) {
        $columns = array_keys($columnData);
        $where = preg_replace('|:\w+|','?', $where);
        if(empty($bind)) $bind = array_values($columnData);
        else $bind = array_values(array_merge($columnData, (array) $bind));
        $sql = sprintf("UPDATE %s SET %s WHERE %s;", $table, implode('=?, ', $columns) . '=?', $where);
        return $this->db->executeQuery($sql, $bind);
    }

    /**
     * Access to tables
     */

    /**
     * Retry instance of class working with database
     * Table of collab_task_messages
     * @return Messages
     */
    public function messages() {
        return $this->messages;
    }

    /**
     * Retry instance of class working with database
     * Table of collab_task_user_message
     * @return UserMessages
     */
    public function userMessage() {
        return $this->user_message;
    }

    /**
     * Retry instance of class working with database
     * Table of collab_users
     * @return Users
     */
    public function users() {
        return $this->users;
    }

    /**
     * Retry instance of class working with database
     * Table of share
     * @return Files
     */
    public function files() {
        return $this->files;
    }
}