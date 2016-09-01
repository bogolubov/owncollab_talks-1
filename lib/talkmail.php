<?php

namespace OCA\Owncollab_Talks;

use OC\User\Session;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Owncollab_Talks\PHPMailer\PHPMailer;

class TalkMail
{

    const SEND_STATUS_CREATED = 0;
    const SEND_STATUS_REPLY = 1;
    const SEND_STATUS_CLOSE = 2;
    const SEND_STATUS_DELETED = 3;

    static private $mailDomain = null;

    static public function createHash($salt) {
        return substr(md5(date("Y-m-d h:i:s").$salt),0,10);
    }

    static public function createAddress($uid) {
        $address = $uid.'@';
        if(self::$mailDomain) {
            $address .= self::$mailDomain;
            return $address;
        }
        return false;
    }

    static public function registerMailDomain($domain) {
        self::$mailDomain = $domain;
        return true;
    }

    /**
     * @param array $from
     * @param array $reply
     * @param array $to
     * @param $subject
     * @param $body
     * @return bool|string
     * @throws PHPMailer\phpmailerException
     */
    static public function createMail(array $from, array $reply, array $to, $subject, $body)
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->setFrom($from[0], $from[1]);
        $mail->addReplyTo($reply[0], $reply[1]);

        foreach($to as $_to) {
            $mail->addAddress($_to[0], $_to[1]);
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML();

        if (!$mail->send())
            return $mail->ErrorInfo;
        else
            return true;
    }

    /**
     * <pre>
     * TalkMail::send(
     *     // From
     *     ['address' => '', 'name' => ''],
     *     // Reply
     *     ['address' => '', 'name' => ''],
     *     // To
     *     ['address' => '', 'name' => ''],
     *     $subject, $body
     * );
     * </pre>
     * @param array $from
     * @param array $reply
     * @param array $to
     * @param $subject
     * @param $body
     * @return bool|string
     * @throws PHPMailer\phpmailerException
     */
    static public function send(array $from, array $reply, array $to, $subject, $body)
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->setFrom($from['address'], $from['name']);
        $mail->addReplyTo($reply['address'], $reply['name']);
        $mail->addAddress($to['address'], $to['name']);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML();

        if (!$mail->send())
            return $mail->ErrorInfo;
        else
            return true;
    }




}
