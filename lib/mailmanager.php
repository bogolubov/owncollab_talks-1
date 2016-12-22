<?php

namespace OCA\Owncollab_Talks;

use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;
use OCA\Owncollab_Talks\PHPMailer\PHPMailer;

class MailManager
{
    /** @var string */
    private $userId;
    /** @var Connect*/
    private $connect;
    /** @var TalkManager */
    private $talkManager;
    /** @var FileManager */
    private $fileManager;
    /** @var \OCP\IURLGenerator */
    private $urlGenerator;
    /** @var Configurator */
    private $configurator;

    /** @var \OCA\Activity\Data */
    private $activity;
    /** @var \OCP\Activity\IManager */
    private $manager;
    /** @var MtaConnector */
    private $mtaConnector;
    /** @var \OC\Files\View */
    private $view;
    /** @var \OC\User\User*/
    private $user;
    /** @var \OC\Files\Storage\Home */
    private $homeStorage;
    /** @var string */
    private $homeStorageRoot;
    /** @var \OC\Files\Cache\Cache */
    private $cache;

    /**
     * Constructor.
     */
    public function __construct($userId, $connect, $configurator, $talkManager, $fileManager)
    {
        $this->userId = $userId;
        $this->connect = $connect;
        $this->configurator = $configurator;
        $this->talkManager = $talkManager;
        $this->fileManager = $fileManager;
//        $this->activity = $activity;
//        $this->manager = $manager;
//        $this->view = new \OC\Files\View('');
//        $this->user = new \OC\User\User($this->userId, new \OC\User\Database());
//        $this->homeStorage = new \OC\Files\Storage\Home(['user' => $this->user]);
//        $this->homeStorageRoot = $this->homeStorage->getSourcePath('');
//        $this->cache = new \OC\Files\Cache\Cache($this->homeStorage);
        $this->urlGenerator = \OC::$server->getURLGenerator();
    }

    // email_begin
    public function createTemplate($talk, $files, $uid_to = '')
    {
        $data = [
            'uid_to'        => $uid_to,
            'uid'           => $this->userId,
            'talk'          => $talk,
            'files'         => $files,
            'subscribers'   => $this->talkManager->subscribers2Array($talk['subscribers']),
            'sitehost'      => $this->configurator->get('server_host'),
            'siteurl'       => $this->configurator->get('site_url'),
            'logoimg'       => '/apps/owncollab_talks/img/logo_oc_collab.png',
        ];

        return Helper::renderPartial('owncollab_talks', 'emails/default', $data);
    }


    public function createTemplateStart($userDataTo, array $talkmessage, array $attachfilesInfo = [])
    {
        $subscribersData = [];
        $subscribers = $this->talkManager->subscribers2Array($talkmessage['subscribers']);
        if (!empty($subscribers['users']))
            foreach ($subscribers['users'] as $su)
                $subscribersData[] = $this->connect->users()->getUserData(trim($su));

        $renderData = [
            'user_to'       => $userDataTo,
            'user_from'     => $this->connect->users()->getUserData($talkmessage['author']),
            'talkmessage'   => $talkmessage,
            'attachfiles'   => $attachfilesInfo,
            'subscribers'   => $subscribersData,
            'sitehost'      => $this->configurator->get('server_host'),
            'siteurl'       => $this->configurator->get('site_url'),
            'logoimg'       => '/apps/owncollab_talks/img/logo.jpg',
        ];

        return Helper::renderPartial('owncollab_talks', 'emails/start', $renderData);
    }

    public function createTemplateError($userAdminData, $userOwnerData, array $talkmessage, array $userDataEmptyEmails)
    {
        $renderData = [
            'user_data_admin'       => $userAdminData,
            'user_data_owner'       => $userOwnerData,
            'talkmessage'           => $talkmessage,
            'users_data_empty'      => $userDataEmptyEmails,
            'sitehost'              => $this->configurator->get('server_host'),
            'siteurl'               => $this->configurator->get('site_url'),
            'logoimg'               => '/apps/owncollab_talks/img/logo.jpg',
        ];

        return Helper::renderPartial('owncollab_talks', 'emails/error', $renderData);
    }

    public function getUsersFromSubscribers($subscribers, $addUsers = [])
    {
        // get users for mail
        $taskSubscribers = $this->talkManager->subscribers2Array($subscribers);
        $taskUsers = $taskSubscribers['users'];

        if (!empty($addUsers) && is_array($addUsers))
            foreach ($addUsers as $au)
                array_push($taskUsers, trim($au));

        if (!empty($taskSubscribers['groups'])) {
            $groupsUsers = $this->connect->users()->getGroupsUsersList();
            foreach ($taskSubscribers['groups'] as $groupname) {
                if (!empty($groupsUsers[$groupname])) {
                    foreach ($groupsUsers[$groupname] as $groupdata) {
                        array_push($taskUsers, $groupdata['uid']);
                    }
                }
            }
        }

        // users list for mail
        return array_values(array_unique(
            array_diff($taskUsers, [
                '',
                null,
                $this->userId,
                $this->configurator->get('collab_user')
            ])
        ));
    }



    public function send(array $to, array $from, $subject, $body, $attachs = [])
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";

        // From it-is special email address, as 'werd+HESH@DOMAIN'
        $mail->setFrom($from['email'], $from['name']);

        // Send to real email address
        $mail->addAddress($to['email'], $to['name']);

        foreach($attachs as $attach) {
            $mail->addAttachment($attach['fullpath']);
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML();

        if (!$mail->send())
            return $mail->ErrorInfo;
        else
            return true;
    }

}