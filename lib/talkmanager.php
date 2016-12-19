<?php

namespace OCA\Owncollab_Talks;

use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;

class TalkManager
{
    /**
     * @var array
     */
    private $data = [
        'rid'           => null,
        'date'          => null,
        'title'         => null,
        'text'          => null,
        'attachements'  => null,
        'subscribers'   => null,
        'author'        => null,
        'hash'          => null,
        'status'        => null,
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
     * TalkManager constructor.
     * @param $userId
     * @param $connect
     * @param $configurator
     */
    public function __construct($userId, $connect, $configurator)
    {
        $this->userId = $userId;
        $this->connect = $connect;
        $this->configurator = $configurator;
        $this->mtaConnector = new MtaConnector($configurator);
    }



    public function build(array $fields)
    {
        $default = [
            'rid' => 0,
            'date' => date("Y-m-d h:i:s"),
            'status' => 0,
            'author' => $this->userId,
        ];

        foreach ($this->data as $key => $value) {
            if (!empty($fields[$key]))
                $this->data[$key] = $fields[$key];
            else if (empty($value) && isset($default[$key]))
                $this->data[$key] = $default[$key];
        }
        return $this->data;
    }




    /**
     * Create unique hash id for new message
     * @param null $salt
     * @param int $length
     * @return string
     */
    public function createhash($salt = null, $length = 10) {
        $salt = !$salt ? $this->data['title'] : 'salt';
        return substr(md5(date("Y-m-d h:i:s").$salt), 0, $length);
    }



    /**
     * @param $json
     * @return array|mixed
     */
    public function subscribers2Array($json = null) {
        $data = ['groups' => [], 'users' => []];
        if (is_string($json)) {
            try {
                $data = json_decode($json, true);
            } catch ( \Exception $error) {}
        }
        return $data;
    }



    public function subscribersCreate($groups = [], $users = []) {
        $data = $this->subscribers2Array();
        $data['groups'] = $groups;
        $data['users'] = $users;
        return json_encode([
            'groups' => array_unique(array_values(array_diff($data['groups'], [null]))),
            'users' => array_unique(array_values(array_diff($data['users'], [null]))),
        ]);
    }

    /**
     * This example delete from json the user user_id,
     * and insert user user_id2 and group developers
     * Uses: ->subscriberChange(json,
     *                              ['users' => ['user_id']]
     *                              ['users' => ['user_id2'], 'groups' => ['developers']]
     *                          )
     * @param $json
     * @param array $toDelete
     * @param array $toAdd
     * @return string
     */
    public function subscribersChange($json, $toDelete = [], $toAdd = []) {
        $data = is_array($json) ? $json : $this->subscribers2Array($json);

        if (!empty($toDelete)) {
            if (isset($toDelete['groups'])) $data['groups'] = array_diff($data['groups'], $toDelete['groups']);
            if (isset($toDelete['users'])) $data['users'] = array_diff($data['users'], $toDelete['users']);
        }

        if (!empty($toAdd)) {
            $data = array_merge_recursive($data, $toAdd);
        }

        return json_encode([
            'groups' => array_unique(array_values(array_diff($data['groups'], [null]))),
            'users' => array_unique(array_values(array_diff($data['users'], [null]))),
        ]);
    }



/*    public function fileinformer($fid) {

    }*/


    public function fileinformer($fid) {

        $file = $this->connect->files()->getById($fid);
        $owner = 'admin';//$this->userId;


        //$sharedWith = \OCP\Share::getUsersItemShared('file', $file['fileid'], $owner, false, true);
        //\OCP\Share::isEnabled()
        //\OCP\Share::isResharingAllowed()

        $fileInfo = \OC\Files\Filesystem::getFileInfo(substr($file['path'], 6));

        if($fileInfo)
            $formatFileInfo = \OCA\Files\Helper::formatFileInfo($fileInfo);

        $shareItem = \OCP\Share::getItemShared('file', $file['fileid']);


        //
        //...//...//...//
        //...//...//...//
        //...//...//...//

//        var_dump($file);
//        var_dump($sharedWith);
//        var_dump($fileInfo);
//        var_dump($formatFileInfo);
//        var_dump($shareItem);
        //var_dump();
        return $file;
    }



    /**
     * \OCP\Share::SHARE_TYPE_USER
     * \OCP\Constants::PERMISSION_ALL
     * @param \OC\Files\FileInfo $file
     * @param string $owner
     * @param array $uids
     * @return array
     */
    public function shareFileToUsers(\OC\Files\FileInfo $file, $owner, array $uids)
    {
        $result     = [];
        $shareType  = $file['mimetype'] == 2 ? 'folder' : 'file';
        $sharedWith = \OCP\Share::getUsersItemShared($shareType, $file['fileid'], $owner, false, true);
        $isEnabled  = \OCP\Share::isEnabled();
        $isAllowed  = \OCP\Share::isResharingAllowed();

        if($isEnabled && $isAllowed) {
            $sharedUsers = is_array($sharedWith) ? array_values($sharedWith) : [];
            foreach ($uids as $uid) {
                if ($owner == $uid || in_array($uid, $sharedUsers) || !\OC_User::userExists($uid))
                    continue;
                $resultToken = $this->connect->files()->shareFile($owner, $uid, $file['fileid'], \OCP\Constants::PERMISSION_ALL);
                $result[$uid] = ['uid' => $uid, 'file' => $file['path'], 'file_token' => $resultToken];
            }
        }
        return $result;
    }


}