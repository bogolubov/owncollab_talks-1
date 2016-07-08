<?php

namespace OCA\Owncollab_Talks\AppInfo;

use OCP\IUserManager;

class Aliaser
{
    private $appConfig = '';
    private $serverHost = '';
    private $mailDomain = '';
    /** @var IUserManager  */
    private $userManager;
    private $groupManager;
    private $session;
    private $userSession;

    /**
     * Aliaser constructor.
     */
    public function __construct()
    {
        $this->appConfig = include __DIR__ . 'app_config.php';
        $this->serverHost = \OC::$server->getRequest()->getServerHost();
        $this->userManager = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();
        $this->session = new \OC\Session\Memory('');
        $this->userSession = new \OC\User\Session($this->userManager, $this->session);

        $connect = $this->connectToMTA();

        if($connect) {
            $this->mailDomain = $this->selectDomain();
            $this->initListeners($this->userSession, $this->groupManager);

            return [
                'domain' => $this->mailDomain,
            ];
        } else {
            // error log

            return false;
        }
    }

    public function selectDomain()
    {
        $stmt = self::$connectionMTA->prepare('SELECT `name` FROM `mailserver`.`virtual_domains`');
        $result = $stmt->fetch();
        return is_array($result) ? $result['name'] : false;
    }


    /**
     * [USER_ID]@[MAIL_HOST_NAME] (Example: "uesrid@owncloud.com")
     * @param $uid
     * @param $password
     */
    public function onPreCreateUser($uid, $password)
    {
        if(!empty($uid) && !empty($password)){
            $this->insertNewAlias(strtolower($uid).'@'.$this->serverHost, $password);
        }
    }

    public function onPreDeleteUser($uid){}

    /**
     * [GROUP_ID]-group@[MAIL_HOST_NAME] (Example: "developers-group@owncloud.com")
     * @param $gid
     */
    public function onPreCreateGroup($gid)
    {
        if(!empty($gid)){
            $this->insertNewAlias(strtolower($gid).'-group@'.$this->serverHost, 'pass'.strtolower($gid));
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
    static private $connectionMTA = null;

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function connectToMTA()
    {
        if(!self::$connectionMTA && !empty($this->appConfig['mta_connection'])){
            $config = new \Doctrine\DBAL\Configuration();
            self::$connectionMTA = \Doctrine\DBAL\DriverManager::getConnection(
                [
                    //'url' => 'mysql://mailuser:aMq3PFWsGpvGd2Ja@localhost/mailserver'
                    'url' => $this->appConfig['mta_connection']
                ],
                $config);
        }
        return self::$connectionMTA;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    static public function getConnectToMTA(){
        return self::$connectionMTA;
    }

    /**
     * @param $newemail
     * @param $newpassword
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertNewAlias($newemail, $newpassword)
    {
        if(self::$connectionMTA){
            $sql = "INSERT INTO `mailserver`.`virtual_users`
                  (`domain_id`, `password` , `email`) VALUES
                  ('1', ENCRYPT(?, CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))) , ?);";
            $stmt = self::$connectionMTA->prepare($sql);
            $stmt->bindValue(1, $newpassword);
            $stmt->bindValue(2, $newemail);
            $stmt->execute();
        }
    }

}