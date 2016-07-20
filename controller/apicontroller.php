<?php

namespace OCA\Owncollab_Talks\Controller;

use OC\Files\Filesystem;
use OCA\Owncollab_Talks\AppInfo\Aliaser;
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

    private $userId;
    private $l10n;
    private $isAdmin;
    private $connect;
    private $projectname = "Base project";
    /**
     * @var \OCP\IURLGenerator
     */
    private $urlGenerator;
    private $mailDomain;


    /**
     * ApiController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param $userId
     * @param $isAdmin
     * @param \OC_L10N $l10n
     * @param Connect $connect
     */
    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        $isAdmin,
        \OC_L10N $l10n,
        Connect $connect
    ){
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->urlGenerator = \OC::$server->getURLGenerator();
        $this->mailDomain = Aliaser::getMailDomain();

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
        Helper::val([
            'userId'  => $this->userId,
            'appName' => $this->appName,
            'mailDomain' => $this->mailDomain,
        ]);

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
            //'data'          => $data,
            'error'         => null,
            'errorinfo'     => '',
            'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
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
            //'post'          => $_POST,
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
                    $owner = \OC\Files\Filesystem::getOwner($file['path']);
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

                            $_result_token = \OCP\Share::shareItem($shareType, $_fid, \OCP\Share::SHARE_TYPE_USER, $_uid, 1);
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

            /**/
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
            'data'     => $data,
            'error'     => null,
            'errorinfo'     => '',
            'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
            'lastlinkid'    => null
        ];

        if(isset($data['parent_id'])) {
            $id = (int) $data['parent_id'];
            $params['parent'] = $this->connect->messages()->getById($id);
            $params['children'] = $this->connect->messages()->getChildren($id);
            $params['messageslist'] = Helper::renderPartial($this->appName,'part.messageslist',[
                'parent'    =>  $params['parent'][0],
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
        //$mail_domain = Helper::getSysConfig('mail_domain', false);
        //$this->mailDomain = $this->mailDomain ? $this->mailDomain : $params['mail_domain'];

        $returned = [
            'to' => null,
            'from' => null,
            'type' => 'fail',
        ];

        if(!$this->mailDomain) {
            $returned['error'] = 'mailDomain not find!';
            return new DataResponse($returned);
        }

        $params = Helper::post();
        $to = explode('@', $params['to']);
        $idhash = explode('+',$to[0]);
        $userDataFrom = $this->connect->users()->getByEmail($params['from']);

        if(!is_array($userDataFrom)) {
            $returned['error'] = "User with email '{$params['from']}' not find!";
            return new DataResponse($returned);
        }

        $params['author'] = $userDataFrom['userid'];

        if (count($idhash) == 1) {

            // Groups emails
            if(strpos($to[0], '-group') !== false) {

                $group = explode('-group', $to[0])[0];

                $resultTaskBuilder = false;
                $usersGroup = false;
                $usersGroupList = $this->connect->users()->getGroupsUsersList();

                foreach($usersGroupList as $_group => $_users) {
                    if (strtolower($_group) == strtolower($group)) {
                        $usersGroup = $_users;
                        break;
                    }
                }

                $returned['trigger'] = $userDataFrom;
                $returned['trigger2'] = $usersGroup;

                if(is_array($usersGroup)) {

                    $users = array_map(function ($item) { return $item['uid']; }, $usersGroup);
                    $subscribers = ['groups' => [$group], 'users' => $users];

                    if(!empty($users))
                        $resultTaskBuilder = $this->saveTaskBuilder($params, $subscribers, $group.'-group');
                    else
                        $returned['error'] = "Users in group '{$group}' not find.";

                };

                if(is_numeric($resultTaskBuilder)) {
                    $returned['type'] = 'ok';
                    $returned['count_mails'] = $resultTaskBuilder;
                } else
                    $returned['type'] = 'error_team';

            }else{

                // Static emails
                switch ($idhash[0]) {
                    case 'team':
                        $count_mails = $this->saveTaskTeam($params);
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

                $userSender = $this->connect->users()->getByEmail($params['from']);

                if ($userSender) {

                    $data['rid'] = $message['id'];
                    $data['date'] = date("Y-m-d H:i:s", time());
                    $data['title'] = 'RE: ' . $message['title'];
                    $data['text'] = $params['content'];
                    $data['attachements'] = '';
                    $data['author'] = $userSender['userid'];
                    $data['subscribers'] = json_encode(['groups' => [], 'users' => []]);
                    $data['hash'] = TalkMail::createHash($data['date']);
                    $data['status'] = TalkMail::SEND_STATUS_REPLY;

                    $insertResult = $this->connect->messages()->insertTask($data);

                    if ($insertResult)
                        $returned['type'] = 'ok';
                    else
                        $returned['type'] = 'error_insert';
                }else
                    $returned['error'] = "User sender not find '{$userSender}' not find.";
            }
        }

        return new DataResponse($returned);
    }

    /**
     * @param $post
     * @return bool|int|string
     */
    public function saveTaskTeam($post)
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
        $data['attachements'] = '';
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
     * @return bool|int|string
     */
    public function saveTaskBuilder($post, $subscribers, $mailUser = null)
    {
        try {
            $mailUser = $mailUser ? $mailUser : 'root';
            $author = $post['author'];
            $title = $post['subject'];
            $message = $post['content'];
        } catch(\Exception $e) {
            return false;
        }

        $users = array_values(array_unique(array_diff($subscribers['users'],[$mailUser])));

        $data['rid'] = 0;
        $data['date'] = date("Y-m-d H:i:s", time());
        $data['title'] = strip_tags($title);
        $data['text'] = $message;
        $data['attachements'] = '';
        $data['author'] = $author;
        $data['subscribers'] = json_encode([
            'groups'    => isset($subscribers['groups']) ? $subscribers['groups'] : false,
            'users'     => $users
        ]);
        $data['hash'] = TalkMail::createHash($title);
        $data['status'] = TalkMail::SEND_STATUS_CREATED;

        if($insert_id = $this->connect->messages()->insertTask($data)) {
            $this->mailUser = $mailUser;
            $count_mails = $this->mailsendSwitcher($data, $users);
            return $count_mails;
            //return 1;
        }
        return false;
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
            'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
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
        if($clean) {$this->_file_list_tree = [];}

        $_files = \OCA\Files\Helper::getFiles($path);

        if(is_array($_files)){
            foreach($_files as $f => $file) {

                if($file['type'] == 'dir') {
                    $this->createFileListTree('/'.$file['name'], '/'.$file['name'], false);
                } else {

                    $_to_list = \OCA\Files\Helper::formatFileInfo($file);

                    if(!$file->isShared()) {
                        $_to_list['mtime'] = $file['mtime']/1000;
                        $_to_list['path'] = $root_path .'/'. $file['name'];

                        $this->_file_list_tree[] = $_to_list;
                    }
                }
            }
        }

        return $this->_file_list_tree;
    }
























}