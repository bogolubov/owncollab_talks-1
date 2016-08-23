<?php

namespace OCA\Owncollab_Talks;


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
    private $configurator = null;
    private $mtaConnector = null;

    private $userManager;
    private $groupManager;
    private $session;
    private $userSession;


    /**
     * Aliaser constructor.
     * @param $appName
     * @param Configurator $config
     * @param MtaConnector $mtaConnector
     */
    public function __construct($appName, Configurator $config, MtaConnector $mtaConnector)
    {
        $this->appName      = $appName;
        $this->configurator = $config;
        $this->mtaConnector = $mtaConnector;

        $this->userManager  = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();

        $this->session      = new \OC\Session\Memory('');
        $this->userSession  = new \OC\User\Session($this->userManager, $this->session);

        $this->initListeners($this->userSession, $this->groupManager);
    }


    /**
     * [USER_ID]@[MAIL_HOST_NAME] (Example: "uesrid@owncloud.com")
     * @param $uid
     * @param $password
     */
    public function onPreCreateUser($uid, $password)
    {
        if (!empty($uid) && !empty($password)) {
            $email = $this->encodeUidToEmail($uid);
            $this->mtaConnector->insertVirtualUser($email, $password);
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
            $prefix = $this->configurator->get('group_prefix');
            $email = $this->encodeUidToEmail($gid.$prefix);
            $this->mtaConnector->insertVirtualUser($email, 'pass'.strtolower($gid));
        }
    }


    public function onPreDeleteGroup($gid){}

    /**
     * @param IUserManager $userSession
     * @param IGroupManager $groupManager
     */
    private function initListeners($userSession, $groupManager)
    {
        $userSession->listen('\OC\User', 'preCreateUser', [$this, 'onPreCreateUser']);
        $userSession->listen('\OC\User', 'preDelete', [$this, 'onPreDeleteUser']);

        $groupManager->listen('\OC\Group', 'preCreate', [$this, 'onPreCreateGroup']);
        $groupManager->listen('\OC\Group', 'preDelete', [$this, 'onPreDeleteGroup']);
    }


    public function encodeUidToEmail($uid){
        $mailDomain = $this->configurator->get('mail_domain');
        return strtolower($uid).'@'.$mailDomain;
    }


}