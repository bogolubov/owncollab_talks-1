<?php

namespace OCA\Owncollab_Talks\MTAServer;


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

//        $this->userManager  = \OC::$server->getUserManager();
//        $this->groupManager = \OC::$server->getGroupManager();
//
//        $this->session      = new \OC\Session\Memory('');
//        $this->userSession  = new \OC\User\Session($this->userManager, $this->session);

        //$this->initListeners($this->userSession, $this->groupManager);
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
            $password = Helper::randomString(8);
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
            $password = Helper::randomString(8);
            $result = $this->mtaConnector->insertVirtualUser($email, $password);

            if (!$result) {
                $errorMessage = 'Error adding user data in the MTA database, ';
                if ($result === false)
                    $errorMessage .= 'the domain specified in the configuration can not be found';
                else
                    $errorMessage .= 'possible SQL query error';
                Helper::mailParserLogerError($errorMessage);
            }else{
                Helper::mailParserLoger('Added new virtual user: '.$email);
            }
        }
    }


    public function onPreDeleteGroup($gid){}

    /**
     * @param \OC\User\Manager|IUserManager $userSession
     * @param \OC\Group\Manager|IGroupManager $groupManager
     */
    private function initListeners($userSession, $groupManager)
    {
        $userSession->listen('\OC\User', 'preCreateUser', [$this, 'onPreCreateUser']);
        $userSession->listen('\OC\User', 'preDelete', [$this, 'onPreDeleteUser']);

        $groupManager->listen('\OC\Group', 'preCreate', [$this, 'onPreCreateGroup']);
        $groupManager->listen('\OC\Group', 'preDelete', [$this, 'onPreDeleteGroup']);
    }


    public function encodeUidToEmail($uid) {
        $mailDomain = $this->configurator->get('mail_domain');
        return strtolower($uid).'@'.$mailDomain;
    }


    public function syncVirtualAliasesWithUsers (array $users, array $groups) {
        $deleteVirtualUsersIds = [];
        $virtualUsers = $this->mtaConnector->getCurrentVirtualUsers(false);
        $groupPrefix = $this->configurator->get('group_prefix');
        $groupPrefixLength = strlen($groupPrefix);

        // Added Users
        array_push($users, 'team');
        array_push($users, 'support');

        // delete fake user
        unset($users[array_search($this->configurator->get('collab_user'), $users)]);

        foreach ($virtualUsers as $virtualUser) {
            $vUid = explode('@',$virtualUser['email'])[0];

            // -group
            if (strlen($vUid) > $groupPrefixLength && substr($vUid, -$groupPrefixLength) === $groupPrefix) {
                $gvUid = substr($vUid, 0, -$groupPrefixLength);
                if (!in_array($gvUid, $groups)) {
                    $deleteVirtualUsersIds[] = $virtualUser['id'];
                } else {
                    unset($groups[array_search($gvUid, $groups)]);
                }
            }
            // -user
            else {
                if (!in_array($vUid, $users)) {
                    $deleteVirtualUsersIds[] = $virtualUser['id'];
                } else {
                    unset($users[array_search($vUid, $users)]);
                }
            }
        }

        // Delete virtual_users . var_dump('Delete: ', $deleteVirtualUsersIds);
        if (!empty($deleteVirtualUsersIds)) {
            $deleteVirtualUsersIdsStr = join(',', $deleteVirtualUsersIds);
            $result = $this->mtaConnector->deleteVirtualUserIn($deleteVirtualUsersIdsStr);
            if ($result)
                Helper::mailParserLoger('Deleted virtual user id: '.$deleteVirtualUsersIdsStr);
        }

        // Add new virtual_users Users . var_dump('Add users:', $users);
        if (!empty($users)) {
            foreach ($users as $user) {
                $this->onPreCreateUser($user, Helper::randomString(8));
            }
        }

        // Add new virtual_users Groups . var_dump('Add groups:', $groups);
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $this->onPreCreateGroup($group);
            }
        }

    }


}