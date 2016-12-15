<?php

namespace OCA\Owncollab_Talks;

use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;

class FileManager
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

    public function getFile($fileid)
    {
        return  $this->connect->files()->getById($fileid);
    }

    public function getFileInformation($fileid)
    {
        return $this->connect->files()->getInfoById($fileid);
    }


    public function insertTpmFile($tmpfile, $newname = null)
    {
        $name = $newname ? $newname : $tmpfile;
        $tmppath = "/tmp/$tmpfile";
        $filepath = "{$this->userId}/files/$name";
        $isReplaced = $this->view->fromTmpFile($tmppath, $filepath);

        if ($isReplaced && $insertId = $this->insertCacheFile($name))
            return $this->insertActivityFile($name, $insertId);
        else
            return false;
    }

    public function insertCacheFile($fileName)
    {
        $insertData = $this->homeStorage->getMetaData("files/$fileName");
        $insertId = $this->cache->insert("files/$fileName", $insertData);

        return (int) $insertId;
    }


    public function insertActivityFile($fileName, $fileId)
    {
        $filePathFiles  = "files/$fileName";
        $link = $this->urlGenerator->linkToRouteAbsolute('files.view.index', array(
            'dir' => dirname($filePathFiles) === 'files' ? '/' : dirname($filePathFiles),
        ));
        $event = $this->manager->generateEvent();
        $event->setApp('files')
            ->setType('file_created')
            ->setAffectedUser($this->userId)
            ->setAuthor($this->userId)
            ->setTimestamp(time())
            ->setSubject('created_self', [[$fileId => '/'.$fileName]])
            ->setObject('files', $fileId, '/'.$fileName)
            ->setLink($link);

        return $this->activity->send($event) ? $fileId : false;
    }


    /**
     * @param $fileid
     * @param $uidwith
     * @return bool|int
     */
    public function shareFileWith($fileid, $uidwith)
    {
        $fileInfo = $this->getFileInformation($fileid);

        $token = \OC::$server->getSecureRandom()->generate(\OC\Share\Constants::TOKEN_LENGTH,
            \OCP\Security\ISecureRandom::CHAR_LOWER . \OCP\Security\ISecureRandom::CHAR_UPPER .
            \OCP\Security\ISecureRandom::CHAR_DIGITS);

        $shareData = [
            'itemType'			=> 'file',
            'itemSource'		=> $fileInfo['fileid'],
            'itemTarget'		=> '/'.$fileInfo['fileid'],
            'shareType'			=> 0,
            'shareWith'			=> $uidwith,
            'uidOwner'			=> $this->userId,
            'permissions'		=> $fileInfo['permissions'] ? $fileInfo['permissions'] : 27,
            'shareTime'			=> time(),
            'fileSource'		=> $fileInfo['fileid'],
            'fileTarget'		=> substr($fileInfo['path'],0,5) === 'files' ? substr($fileInfo['path'], 5) : $fileInfo['path'],
            'token'				=> $token,
            'parent'			=> null,
            'expiration'		=> null
        ];

        $insertId = $this->connect->files()->insertShare($shareData);

        return $insertId;
    }

}