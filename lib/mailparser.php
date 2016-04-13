<?php

namespace OCA\Owncollab_Talks;

use OC\User\Session;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Owncollab_Talks\Helper;

class MailParser
{
    public function checkMail() {
        $dir = '/var/mail/';
        if (file_exists($dir.'mail2')) {
            $mail = fopen($dir . 'mail2', 'r');
            file($mail);
            fclose($mail);
        }
        return $messages;
    }

    public function parseMessage() {

    }
}
