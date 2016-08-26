<?php

namespace OCA\Owncollab_Talks\Controller;

use OC\Files\Filesystem;
use OCA\Owncollab_Talks\AppInfo\Aliaser;
use OCA\Owncollab_Talks\Configurator;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\PHPMailer\PHPMailer;
use OCA\Owncollab_Talks\TalkMail;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Template;

class ApiController extends Controller {

    /**
     * @var \OCP\IURLGenerator $urlGenerator
     * @var \OCP\IURLGenerator $configurator
     */
    private $userId;
    private $l10n;
    private $isAdmin;
    private $connect;
    private $urlGenerator;
    private $configurator;
    private $mailDomain;


    /**
     * ApiController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param $userId
     * @param $isAdmin
     * @param $l10n
     * @param Connect $connect
     * @param Configurator $configurator
     */
    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        $isAdmin,
        $l10n,
        Connect $connect,
        Configurator $configurator
    ){
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->configurator = $configurator;
        $this->mailDomain = $this->configurator->get('mail_domain');
        $this->urlGenerator = \OC::$server->getURLGenerator();
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {

        $key = Helper::post('key');
        $data = Helper::post('data',false);
        $pid = Helper::post('pid');
        $uid = Helper::post('uid');

        if(!$this->mailDomain)
            return new DataResponse(['error'=>'Email domain is undefined']);

        // added base needed params global static object
/*        Helper::val([
            'userId'  => $this->userId,
            'appName' => $this->appName,
            'mailDomain' => $this->mailDomain,
        ]);*/

        if(method_exists($this, $key)) {
            TalkMail::registerMailDomain($this->mailDomain);
            return $this->$key($data);
        } else
            return new DataResponse(['error'=>'Api key not exist']);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function getproject($data) {
        return new DataResponse($data);
    }


    /**
     * Saved talk reply from inside application
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function save_reply($data) {

        $params = [
            'error'         => null,
            'errorinfo'     => '',
            'mail_is_send'  => false,
            'insert_id'     => null,
            'parent_id'     => null,
        ];

        if( $data['hash'] && $data['message'] && $message = $this->connect->messages()->getByHash(trim($data['hash'])) ){

            $saveData['rid'] = $message['id'];
            $saveData['date'] = date("Y-m-d H:i:s", time());
            $saveData['title'] = 'RE: '.$message['title'];
            $saveData['text'] = trim($data['message']);
            $saveData['attachements'] = '';
            $saveData['author'] = $this->userId;
            $saveData['subscribers'] = '';
            $saveData['hash'] = '';
            $saveData['status'] = TalkMail::SEND_STATUS_REPLY;

            if($params['insert_id'] = $this->connect->messages()->insertTask($saveData)) {
                $params['parent_id'] = $message['id'];
                //$params['mail_is_send'] = $this->mailsendSwitcher($data, $all_users, $groups, $groupsusers);
            }

            $params['data'] = $saveData;
        }

        return new DataResponse($params);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse
     */
    public function saveTalk()
    {
        $params = [
            'error'         => null,
            'errorinfo'     => null,
            'insert_id'     => null,
            'mail_is_send'  => false,
        ];


        if(Helper::post('title') && Helper::post('message')) {

            $groupsusers = [];
            $all_users = $users = Helper::post('users', false);
            $groups = Helper::post('groups', false);
            $share_files = Helper::post('share', false);
            $attachements = [];
            $attachements_info = [];

            if(!empty($groups)) {
                $groupsusers = $this->connect->users()->getGroupsUsersList();
                foreach($groups as $group) {
                    if(isset($groupsusers[$group]) && $rm_arr = $groupsusers[$group]) {
                        $rm_list = array_map(function($item){ return $item['uid']; }, $rm_arr);
                        $users = array_diff($users, $rm_list);
                    }
                }
            }

            if(!empty($share_files)) {

                $files_id_list = array_keys($share_files);

                foreach ($share_files as $_fid => $_file) {

                    $file = $this->connect->files()->getById($_fid);
                    $owner = $this->userId; // \OC\Files\Filesystem::getOwner($file['path']);
                    $shareType = $file['mimetype'] == 2 ? 'folder' : 'file';
                    $sharedWith = \OCP\Share::getUsersItemShared($shareType, $file['fileid'], $owner, false, true);
                    $isEnabled = \OCP\Share::isEnabled();
                    $isAllowed = \OCP\Share::isResharingAllowed();

                    $attachements_info[] = [
                        'info' => \OCA\Files\Helper::formatFileInfo(\OC\Files\Filesystem::getFileInfo(substr($file['path'],6))),
                        'file' => $file,
                    ];

                    if($isEnabled && $isAllowed) {

                        $sharedUsers = is_array($sharedWith) ? array_values($sharedWith) : [];

                        foreach ($all_users as $_uid) {

                            if ($owner == $_uid || in_array($_uid, $sharedUsers)) {
                                continue;
                            }

                            $this->connect->files()->shareFile($this->userId, $_uid, $_fid);

                            // \OCP\Share::SHARE_TYPE_USER - 0
                            // \OCP\Constants::PERMISSION_READ - 1
                            // \OCP\Constants::PERMISSION_ALL - 31

//                            $itemType = $shareType;
//                            $itemSource = $_fid;
//                            $shareType = \OCP\Share::SHARE_TYPE_USER;
//                            $shareWith = $_uid;
//                            $permissions = \OCP\Constants::PERMISSION_READ;
//
//                            $isShared = \OC\Share\Share::shareItem($itemType, $itemSource, $shareType, $shareWith, $permissions);

                            //$_result_token = \OCP\Share::shareItem($shareType, $_fid, \OCP\Share::SHARE_TYPE_USER, $_uid, \OCP\Constants::PERMISSION_READ);
                        }
                    }

                }

                $attachements = $files_id_list;
            }

            $data['rid'] = 0;
            $data['date'] = date("Y-m-d H:i:s", time());
            $data['title'] = strip_tags(Helper::post('title'));
            $data['text'] = addslashes(Helper::post('message'));
            $data['attachements'] = json_encode((array) $attachements);
            $data['author'] = $this->userId;
            $data['subscribers'] = json_encode(['groups'=>$groups, 'users'=>$users]);
            $data['hash'] = TalkMail::createHash($data['title']);
            $data['status'] = TalkMail::SEND_STATUS_CREATED;

            if($params['insert_id'] = $data['id'] = $this->connect->messages()->insertTask($data)) {
                $params['mail_is_send'] = $this->mailsendSwitcher($data, $all_users, $groups, $groupsusers, $attachements_info);
            }

            $params['data'] = $data;
        }

        if($params['insert_id']) {
            Helper::cookies('goto_message', $params['insert_id']);
            header("Location: /index.php/apps/owncollab_talks/started");
            exit;
        }

        return new DataResponse($params);
    }


    public $mailUser = false;

    /**
     * @param $talk
     * @param $users
     * @param $groups
     * @param $groupsusers
     * @param $attaches
     * @return bool|int|string
     */
    public function mailsendSwitcher($talk = [], $users = [], $groups = [], $groupsusers = [], $attaches = [])
    {
        $to = [];
        $mailUser = $this->mailUser ? $this->mailUser : $this->userId;

        if(!$mailUser)
            return false;

        foreach ($users as $user) {
            $_userData = $this->connect->users()->getUserData($user);
            if(!empty($_userData['email']))
                $to[] = [$_userData['email'], $_userData['displayname']];
        }

        $talk['text'] = Helper::renderPartial($this->appName, 'emails/begin', [
            'user_id' => $this->userId ? $this->userId : 'root',
            'message' => $talk,
            'mail_domain' => $this->mailDomain,
            'attachements_info' => $attaches,
        ]);

        $result = TalkMail::createMail(
            [$mailUser.'@'.$this->mailDomain, $mailUser],
            [$mailUser.'+'.$talk['hash'].'@'.$this->mailDomain, $mailUser],
            $to,
            $talk['title'],
            $talk['text']
        );

        if($result === true) {
            return count($to);
        }

        return $result;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function message_children($data)
    {
        $params = [
            'error'     => null,
            'errorinfo'     => '',
            'lastlinkid'    => null
        ];

        if(isset($data['parent_id'])) {
            $id = (int) $data['parent_id'];
            $params['parent'] = $this->connect->messages()->getById($id);
            $params['children'] = $this->connect->messages()->getChildren($id);
            $params['messageslist'] = Helper::renderPartial($this->appName,'part.messageslist',[
                'parent'    =>  $params['parent'],
                'children'  =>  $params['children'],
            ]);
        }

        return new DataResponse($params);
    }


    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse
     */
    public function parseManager()
    {
        $returned = [
            'to' => null,
            'from' => null,
            'type' => 'fail',
            'error' => null,
        ];
        $shareUIds = [];
        $insertResult = false;

        if(!$this->mailDomain) {
            $returned['error'] = 'mailDomain not find!';
            return new DataResponse($returned);
        }

        $params = Helper::post();
        $to = explode('@', $params['to']);
        $idhash = explode('+',$to[0]);
        $userDataFrom = $this->connect->users()->getByEmail(trim($params['from']));

        if(!is_array($userDataFrom)) {
            $returned['error'] = "User with email '{$params['from']}' not find!";
            return new DataResponse($returned);
        }

        $shareUIds[] = $params['author'] = $userDataFrom['userid'];

        // Work with static mail name and groups
        if (count($idhash) == 1) {

            // Groups emails
            if(strpos($to[0], '-group') !== false) {
                $group = trim(explode('-group', $to[0])[0]);
                $usersGroup = false;
                $usersGroupList = $this->connect->users()->getGroupsUsersList();

                foreach($usersGroupList as $_group => $_users) {
                    if (strtolower($_group) == strtolower($group)) {
                        $usersGroup = $_users;
                        break;
                    }
                }

                if(is_array($usersGroup)) {
                    $users = array_map(function ($item) { return $item['uid']; }, $usersGroup);

                    $shareUIds = array_merge($shareUIds, $users);
                    $subscribers = ['groups' => [$group], 'users' => $users];

                    if(!empty($users)) {
                        $builder = $this->saveTalkBuilder($params, $subscribers, $group.'-group');
                        if($builder === true) $returned['type'] = 'ok';
                        else $returned['error'] = 'Save task failed!';
                    }
                    else  $returned['error'] = "Users in group '{$group}' not find.";
                }else
                    $returned['error'] = "Group {$group} not find!";

            }else{
                // Static emails
                switch ($idhash[0]) {
                    case 'team':
                        $count_mails = $this->saveTalkTeam($params);

                        $users = $this->connect->users()->getAll();
                        $shareUIds = array_merge($shareUIds, array_map(function ($item) { return $item['uid']; }, $users));

                        if(is_numeric($count_mails)) {
                            $returned['type'] = 'ok';
                            $returned['count_mails'] = $count_mails;
                        } else
                            $returned['type'] = 'error_team';
                        break;

                    case 'support':
                        // for the future realization
                        break;
                }
            }

        } else {

            // Users emails
            $uid = $idhash[0];
            $hash = $idhash[1];

            // checked message by hash key
            if ($message = $this->connect->messages()->getByHash(trim($hash))) {

                // added users for shared file
                if (!empty($message['subscribers'])) {
                    try {
                        $subscribers = json_decode($message['subscribers'], true);
                        if ($subscribers['groups']) {
                            $users = [];
                            $_groupsUsers = $this->connect->users()->getGroupsUsers();
                            foreach ($subscribers['groups'] as $group) {
                                if (!empty($_groupsUsers[$group])) {
                                    $users = array_merge($users, array_map(function ($item) { return $item['uid']; }, $_groupsUsers[$group]));
                                }
                            }
                            $shareUIds = array_merge($shareUIds, $users);
                        }
                        if ($subscribers['users']) {
                            $shareUIds = array_merge($shareUIds, $subscribers['users']);
                        }

                    } catch (\Exception $e) {
                    }
                }


                $userSender = $this->connect->users()->getByEmail($params['from']);

                if ($userSender) {

                    $data['rid'] = $message['id'];
                    $data['date'] = date("Y-m-d H:i:s", time());
                    $data['title'] = 'RE: ' . $message['title'];
                    $data['text'] = $params['content'];
                    $data['attachements'] = '';
                    $data['author'] = $userSender['userid'];
                    $data['subscribers'] = $message['subscribers'];
                    $data['hash'] = TalkMail::createHash($data['date']);
                    $data['status'] = TalkMail::SEND_STATUS_REPLY;

                    $insertResult = $this->connect->messages()->insertTask($data);

                    if ($insertResult)
                        $returned['type'] = 'ok';

                }else
                    $returned['error'] = "User sender '{$userSender}' not find.";
            }
        }

        // Work with files
        if(isset($params['files']) && is_array($params['files']) &&!empty($shareUIds) && $insertResult) {
            $saveFiles = $this->parserFileHandler($params['files'], $shareUIds);

            if(!empty($saveFiles)) {
                $returned['shared_with'] = $saveFiles['shared_with'];
                $returned['file_fileid'] = $saveFiles['file_fileid'];

                if(!empty($saveFiles['file_fileid'])) {
                    $this->connect->update('*PREFIX*collab_messages', ['attachements' => json_encode($saveFiles['file_fileid'])], 'id = ?', [$insertResult]);
                }
            } else
                $returned['shared'] = 'failed';

        }

        return new DataResponse($returned);
    }


    /**
     * @param $post
     * @return bool|int|string
     */
    public function saveTalkTeam($post)
    {
        try {
            $from = $post['from'];
            $title = $post['subject'];
            $message = $post['content'];
        } catch(\Exception $e) {
            return false;
        }

        $author = 'root';
        $groupsusers = $this->connect->users()->getGroupsUsers();

        $users = array_map(function ($item) use (&$author, $from) {
            if($from == $item['email'])
                $author = $item['uid'];
            return $item['uid'];
        }, $groupsusers);

        $users = array_values(array_unique(array_diff($users,[$author])));

        $data['rid'] = 0;
        $data['date'] = date("Y-m-d H:i:s", time());
        $data['title'] = strip_tags($title);
        $data['text'] = $message;
        $data['attachements'] = '[]';
        $data['author'] = $author;
        $data['subscribers'] = json_encode(['groups'=>false, 'users'=>$users]);
        $data['hash'] = TalkMail::createHash($title);
        $data['status'] = TalkMail::SEND_STATUS_CREATED;

        if($insert_id = $this->connect->messages()->insertTask($data)) {
            $this->mailUser = $author;
            $count_mails = $this->mailsendSwitcher($data, $users);
            return $count_mails;
        }
        return false;
    }


    /**
     * @param $post ['from'=>null,'subject'=>null,'content'=>null] , $users = [], 'mailUser'=>content,
     *                  from - author
     *                  subject - title
     *                  content - message
     * @param $subscribers ['groups' => false, 'users' => false, ]
     * @param $mailUser
     * @return array
     */
    public function saveTalkBuilder($post, $subscribers, $mailUser = null)
    {
        $inserted = false;
        $result = [
            'insert' => false,
            'emails' => false,
            'error' => false,
        ];

        try {
            $mailUser = $mailUser ? $mailUser : 'root';
            $author = $post['author'];
            $title = $post['subject'];
            $message = $post['content'];

        } catch(\Exception $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }

        $users = array_values(array_unique(array_diff($subscribers['users'],[$mailUser])));

        $data['rid']            = 0;
        $data['date']           = date("Y-m-d H:i:s", time());
        $data['title']          = strip_tags($title);
        $data['text']           = $message;
        $data['attachements']   = '';
        $data['author']         = $author;
        $data['subscribers']    = json_encode([
                                    'groups'    => isset($subscribers['groups']) ? $subscribers['groups'] : false,
                                    'users'     => $users
                                ]);
        $data['hash']           = TalkMail::createHash($title);
        $data['status']         = TalkMail::SEND_STATUS_CREATED;

        if($insert_id = $this->connect->messages()->insertTask($data)) {
            $inserted = true;
            $this->mailUser = $mailUser;
            $this->mailsendSwitcher($data, $users);
        }

        return $inserted;
    }


    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse
     */
    public function getuserfiles()
    {
        $params = array(
            'user' => $this->userId,
        );

        $fileList = $this->createFileListTree('/', '', true);

        $params['file_list'] = $fileList;
        $params['view'] = Helper::renderPartial($this->appName, 'part.userfilelist', $params);

        return new DataResponse($params);
    }


    /**
     * @var array
     */
    private $_file_list_tree = [];

    /**
     * @param string $path
     * @param string $root_path
     * @param bool $clean
     * @return array
     */
    public function createFileListTree($path = '/', $root_path = '', $clean = false)
    {
        if($clean) {
            $this->_file_list_tree = [];
        }

        $_files = \OCA\Files\Helper::getFiles($path);

        if (is_array($_files)) {
            foreach($_files as $f => $file) {

                if($file['type'] == 'dir') {
                    $this->createFileListTree('/'.$file['name'], '/'.$file['name'], false);
                } else {

                    $_to_list = \OCA\Files\Helper::formatFileInfo($file);

                    if(!$file->isShared()) {
                        $_to_list['mtime'] = $file['mtime']/1000;
                        $_to_list['path']  = $root_path .'/'. $file['name'];

                        $this->_file_list_tree[] = $_to_list;
                    }
                }
            }
        }

        return $this->_file_list_tree;
    }

/*$returned['shared_with'] = $saveFiles['shared_with'];
$returned['file_info'] = $saveFiles['file_info'];*/
    private function parserFileHandler($files, $userForSharing)
    {
        $saveFiles = ['file_fileid'=>[],'shared_with'=>[]];

        if($this->loginVirtualUser()) {

            foreach($files as $file){

                if (!\OC\Files\Filesystem::is_dir('/Talks'))
                    \OC\Files\Filesystem::mkdir('/Talks');

                if (is_file($file['tmpfile'])) {

                    try {
                        chmod($file['tmpfile'], 0755);
                    } catch (\Exception $e) {}

                    $filePathTo = '/Talks/'.$file['filename'];

                    $fileInfoExist = \OC\Files\Filesystem::getFileInfo($filePathTo, false);
                    if($fileInfoExist){
                        $filePathTo = '/Talks/'.time().'-'.$file['filename'];
                    }

                    $saved = \OC\Files\Filesystem::file_put_contents($filePathTo, file_get_contents($file['tmpfile']));

                    if($saved) {
                        unlink($file['tmpfile']);
                        $saveFilesInfo = \OC\Files\Filesystem::getFileInfo($filePathTo);


                        Helper::mailParserLoger('FILES INFO: '.json_encode($saveFilesInfo));


                        $saveFiles['file_fileid'][] = $saveFilesInfo['fileid'];
                        $saveFiles['shared_with'][] = $this->shareFileToUsers($saveFilesInfo, $userForSharing);

                        //todo mails sands
                    }
                }
            }
        }
        return $saveFiles;
    }


    private function loginVirtualUser()
    {
        $secureRandom = new \OC\Security\SecureRandom();
        $user = $this->configurator->get('collab_user');
        $userPassword = $this->configurator->get('collab_user_password');

        if (!\OC_User::userExists($user)) {
            # create user if not exist
            $userManager = \OC::$server->getUserManager();
            $userManager->createUser($user, $userPassword);

            $user = new \OC\User\User($user, null);
            $group =\OC::$server->getGroupManager()->get('admin');
            $group->addUser($user);
        }

        $granted = self::login($user, $userPassword);

        if ($granted) {
            \OC_User::setUserId($user);
            \OC_Util::setupFS($user);
        }

        return $granted;
    }


    private function shareFileToUsers(\OC\Files\FileInfo $file, array $uids)
    {
        $user       = $this->configurator->get('collab_user');
        $result     = [];
        $owner      = $user;
        $shareType  = $file['mimetype'] == 2 ? 'folder' : 'file';
        $sharedWith = \OCP\Share::getUsersItemShared($shareType, $file['fileid'], $owner, false, true);
        $isEnabled  = \OCP\Share::isEnabled();
        $isAllowed  = \OCP\Share::isResharingAllowed();

        if($isEnabled && $isAllowed) {
            $sharedUsers = is_array($sharedWith) ? array_values($sharedWith) : [];
            foreach ($uids as $uid) {
                if ($owner == $uid || in_array($uid, $sharedUsers)) continue;

                // \OCP\Share::SHARE_TYPE_USER
                // \OCP\Constants::PERMISSION_ALL
                //$resultToken = \OCP\Share::shareItem($shareType, $file['fileid'], 0, $uid, 31);

                $resultToken = $this->connect->files()->shareFile($owner, $uid, $file['fileid'], \OCP\Constants::PERMISSION_ALL);
                $result[$uid] = ['uid' => $uid, 'file' => $file['path'], 'file_token' => $resultToken];
            }
        }
        return $result;
    }


    /**
     * Try to login a user
     *
     * @param string $loginname The login name of the user to log in
     * @param string $password The password of the user
     * @return boolean|null
     *
     * Log in a user and regenerate a new session - if the password is ok
     */
    public static function login($loginname, $password) {
        $result = \OC_User::getUserSession()->login($loginname, $password);
        if ($result) {
            // Refresh the token
            \OC::$server->getCsrfTokenManager()->refreshToken();
            //we need to pass the user name, which may differ from login name
            $user = \OC_User::getUserSession()->getUser()->getUID();
            \OC_Util::setupFS($user);
            //trigger creation of user home and /files folder
            \OC::$server->getUserFolder($user);
        }
        return $result;
    }


}