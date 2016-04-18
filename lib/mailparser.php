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
        if (file_exists($dir.'testmail.txt')) {
            $mail = fopen("/var/mail/testmail.txt", 'r+');
            $message = $this->parseMessage("/var/mail/testmail.txt");
            fclose($mail);
        }
        return $message;
    }

    public function parseMessage($file) {
        $message = array();

        $msg = file_get_contents($file);

        $sender = getenv('SENDER');

        $recipient = getenv('RECIPIENT');

        list($header, $body) = explode("\n\n", $msg, 2);

        $subject = '';
        $from = '';
        $to = '';
        $headerArr = explode("\n", $header);
        foreach ($headerArr as $str) {
            if (strpos($str, 'Subject:') === 0) {
                $subject = $str;
            }
            if (strpos($str, 'From:') === 0) {
                $from = $str;
                $author = $this->getUserIdFromAddress($from);
            }
            if (strpos($str, 'To:') === 0) {
                $to = $str;
                $subscribers = $this->getSubscribers($from);
            }
            if (strpos($str, 'Delivery-date:') === 0) {
                $date = $str;
            }
        }

//        $logMsg = "=== MSG ===\n";
//        $logMsg .= "SENDER: $sender\n";
//        $logMsg .= "RECIPIENT: $recipient\n";
//        $logMsg .= "$from\n";
//        $logMsg .= "$to\n";
//        $logMsg .= "$subject\n\n";
//        $logMsg .= "$msg\n";
//        file_put_contents('/var/mail/inb.log',$logMsg, FILE_APPEND);
        $message['sender'] = $sender;
        $message['recipient'] = $recipient;
        $message['from'] = $from;
        $message['author'] = $author;
        $message['subscribers'] = $subscribers;
        $message['date'] = $date;
        $message['to'] = $to;
        $message['subkect'] = $subject;
        $message['message-body'] = $msg;

        return $message;
    }

    private function getSubscribers($address) {
        $subscribers = array();
        $addr = is_array($address) ? $address : explode(', ', $address);
        foreach ($addr as $a => $addres) {
            $subscribers[] = $this->getUserIdFromAddress($addres);
        }
        return $subscribers;
    }

    private function getUserIdFromAddress($address) {
        return substr($address, 0, strpos($address, '@'));
    }
}
