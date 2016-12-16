<?php

namespace OCA\Owncollab_Talks;

use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;
use OCA\Owncollab_Talks\PHPMailer\PHPMailer;

class MailManager
{
    /** @var Connect*/
    private $connect;
    /** @var Configurator */
    private $configurator;
    /** @var string */
    private $userId;
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
    /** @var \OCP\IURLGenerator */
    private $urlGenerator;
    private $tmpDir = '/tmp/';
    private $usrDir = 'files/';

    /**
     * Constructor.
     */
    public function __construct($userId, $connect, $activity, $manager)
    {
        $this->userId = $userId;
        $this->connect = $connect;
        $this->activity = $activity;
        $this->manager = $manager;
        $this->view = new \OC\Files\View('');
        $this->user = new \OC\User\User($this->userId, new \OC\User\Database());
        $this->homeStorage = new \OC\Files\Storage\Home(['user' => $this->user]);
        $this->homeStorageRoot = $this->homeStorage->getSourcePath('');
        $this->cache = new \OC\Files\Cache\Cache($this->homeStorage);
        $this->urlGenerator = \OC::$server->getURLGenerator();
    }

    public function createTemplate($view, $data)
    {

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