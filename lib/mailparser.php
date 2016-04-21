<?php

namespace OCA\Owncollab_Talks;

use OC\User\Session;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Owncollab_Talks\Helper;

class MailParser
{
    public function checkMail($msg) {
        $message = $this->parseMessage($msg);
        return $message;
    }

    public function parseMessage($msg) {
        $message = array();

        $sender = getenv('SENDER');

        $recipient = getenv('RECIPIENT');

        list($header, $body) = explode("\n\n", $msg, 2);

        $subject = '';
        $from = '';
        $to = '';
        $headerArr = explode("\n", $header);
        foreach ($headerArr as $str) {
            if (strpos($str, 'Subject:') === 0) {
                $subject = substr($str, 8);
            }
            if (strpos($str, 'From:') === 0) {
                $from = $str;
                $author = $this->getFrom($from);
            }
            if (strpos($str, 'To:') === 0) {
                $to = $str;
                $subscribers = $this->getSubscribers($to);
            }
            if (strpos($str, 'Date:') === 0) {
                $date = substr($str, 5);
            }
        }

        $message = array(
            //'rid' => $talkid,
            'date' => $date,
            'title' => trim($subject),
            'text' => trim($msg),
            'attachements' => NULL,
            'author' => $author,
            'subscribers' => implode(',', array_column($subscribers, 'userid')),
            'hash' => $subscribers[0]['hash'],
            'status' => 0
        );


        return $message;
    }

    private function getFrom($from) {
        if (strpos($from, '<') && strpos($from, '>')) {
            preg_match('/<(.*?)>/', $from, $match);
            $address = $match[1];
        }
        return $address;
    }

    private function getSubscribers($address) {
        if (strpos($address, '<') && strpos($address, '>')) {
            preg_match('/<(.*?)>/', $address, $match);
            $address = $match[1];
        }
        $subscribers = array();
        $subscribers[] = $this->getUserIdFromAddress($address);
        return $subscribers;
    }

    private function getUserIdFromAddress($address) {
        if (strpos($address, '<') && strpos($address, '>')) {
            preg_match('/<(.*?)>/', $address, $match);
            $address = $match[1];
        }
        $to = substr($address, 0, strpos($address, '@'));
        if ($delimiter = strpos($to, '+')) {
            $userid = substr($to, 0, $delimiter);
            $hash = substr($to, $delimiter+1);
            return ['userid' => $userid, 'hash' => $hash];
        }
        else {
            return $to;
        }
    }
}
