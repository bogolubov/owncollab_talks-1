<?php

namespace OCA\Owncollab_Talks\AppInfo;

use OCA\Owncollab_Talks\Helper;
use OCP\IGroupManager;
use OCP\IUserManager;

class Aliaser
{
    /**
     * @var array $appConfig
     * @var string $mailDomain
     * @var IUserManager $userManager
     * @var IGroupManager $groupManager
     * @var \OC\Session\Memory $session
     * @var \OC\User\Session $userSession
     */

    private $appName = null;
    private $appConfig = null;
    private $mailDomain = '';
    private $userManager;
    private $groupManager;
    private $session;
    private $userSession;

    /**
     * Aliaser constructor.
     * @param $appName
     */
    public function __construct($appName)
    {
        $this->appName      = $appName;
        $this->appConfig    = Helper::includePHP(\OC_App::getAppPath($appName) . '/appinfo/config.php');
        $this->userManager  = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();
        $this->session      = new \OC\Session\Memory('');
        $this->userSession  = new \OC\User\Session($this->userManager, $this->session);

        if(!self::$_instanceMTAConnection)
            self::$_instanceMTAConnection = $this->createMTAConnection();

        if(self::$_instanceMTAConnection) {
            $this->mailDomain = self::getMailDomain();
            $this->initListeners($this->userSession, $this->groupManager);
            return true;
        } else {
            // error log
            return false;
        }
    }


    /**
     * [USER_ID]@[MAIL_HOST_NAME] (Example: "uesrid@owncloud.com")
     * @param $uid
     * @param $password
     */
    public function onPreCreateUser($uid, $password)
    {
        if (!empty($uid) && !empty($password)) {
            $this->insertNewAlias(strtolower($uid).'@'.$this->mailDomain, $password);
        }
    }

    public function onPreDeleteUser($uid){}

    /**
     * [GROUP_ID]-group@[MAIL_HOST_NAME] (Example: "developers-group@owncloud.com")
     * @param $gid
     */
    public function onPreCreateGroup($gid)
    {
        if(!empty($gid)) {
            $group_prefix = !empty($this->appConfig['group_prefix'])
                ? $this->appConfig['group_prefix']
                : '-group';

            $this->insertNewAlias(strtolower($gid).$group_prefix.'@'.$this->mailDomain, 'pass'.strtolower($gid));
        }
    }

    public function onPreDeleteGroup($gid){}

    /**
     * @param $userSession
     * @param $groupManager
     */
    private function initListeners($userSession, $groupManager)
    {
        $userSession->listen('\OC\User', 'preCreateUser', [$this, 'onPreCreateUser']);
        $userSession->listen('\OC\User', 'preDelete', [$this, 'onPreDeleteUser']);

        $groupManager->listen('\OC\Group', 'preCreate', [$this, 'onPreCreateGroup']);
        $groupManager->listen('\OC\Group', 'preDelete', [$this, 'onPreDeleteGroup']);
    }


    /** @var \Doctrine\DBAL\Connection */
    static private $_instanceMTAConnection = null;

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createMTAConnection()
    {
        $_instance = null;
        if(!empty($this->appConfig['mta_connection'])){
            $config = new \Doctrine\DBAL\Configuration();
            $_instance = \Doctrine\DBAL\DriverManager::getConnection(
                [
                    'url' => $this->appConfig['mta_connection']
                ],
                $config);
        }
        return $_instance;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    static public function getMTAConnection(){
        return self::$_instanceMTAConnection;
    }


    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    static public function getMailDomain() {
        if(self::$_instanceMTAConnection){
            $stmt = self::$_instanceMTAConnection->prepare('SELECT `name` FROM `mailserver`.`virtual_domains`');
            $stmt->execute();
            $result = $stmt->fetch();
            return is_array($result) ? $result['name'] : false;
        }
    }

    /**
     * @param $email
     * @return array|bool|mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    static public function emailExist($email) {
        if(self::$_instanceMTAConnection){
            $stmt = self::$_instanceMTAConnection->prepare('SELECT * FROM `mailserver`.`virtual_users` WHERE `email` = ?');
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            return is_array($result) ? $result : false;
        }
    }


    /**
     * @param $newemail
     * @param $newpassword
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertNewAlias($newemail, $newpassword)
    {
        if(self::$_instanceMTAConnection && !self::emailExist($newemail)){
            $sql = "INSERT INTO `mailserver`.`virtual_users`
                  (`domain_id`, `password` , `email`) VALUES
                  ('1', ENCRYPT(?, CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))) , ?);";
            $stmt = self::$_instanceMTAConnection->prepare($sql);
            $stmt->bindValue(1, $newpassword);
            $stmt->bindValue(2, $newemail);
            $stmt->execute();
        }

    }



}