<?php

namespace OCA\Owncollab_Talks\Objects;

use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;

class TalkManager
{
    /*
    id	bigint(20) UN AI PK
    rid	int(11)
    date	datetime
    title	varchar(1024)
    text	tinytext
    attachements	varchar(2024)
    author	varchar(64)
    subscribers	varchar(2048)
    hash	varchar(32)
    status	int(11)
    */

    private $data = [
        'id' => null,
        'rid' => null,
        'date' => null,
        'title' => null,
        'text' => null,
        'attachements' => null,
        'subscribers' => null,
        'author' => null,
        'hash' => null,
        'status' => null,
    ];

    /** @var Connect*/
    private $connect;
    /** @var Configurator */
    private $configurator;
    /** @var string */
    private $userId;
    /** @var MtaConnector */
    private $mtaConnector;

    /**
     * Talk constructor.
     * @param Configurator $configurator
     * @param Connect $connect
     * @param $userId
     */
    public function __construct($configurator, $connect, $userId)
    {
        $this->configurator = $configurator;
        $this->connect = $connect;
        $this->userId = $userId;
        $this->mtaConnector = new MtaConnector($configurator);
    }


    public function field($key, $value)
    {
        $this->data[$key] = $value;
    }


    public function build()
    {
//        $requireFields = ['text', 'subscribers'];
        $defaultFields = [
            'date' => date("Y-m-d h:i:s"),
            'author' => $this->userId,
        ];

        foreach ($this->data as $key => $value) {
//            if (empty($value) && in_array($key, $requireFields))
//                return false;
            if (empty($value) && isset($defaultFields[$key]))
                $this->data[$key] = $defaultFields[$key];
        }

        return $this->data;
    }

    public function createhash($salt = null) {
        $salt = !$salt ? $this->data['title'] : 'salt';
        return substr(md5(date("Y-m-d h:i:s").$salt),0,10);
    }

}