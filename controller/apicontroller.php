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

        if(!$this->mailDomain) return new DataResponse(['error'=>'Email domain is undefined']);

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
            'post'          => $_POST,
            'error'         => null,
            'errorinfo'     => null,
            'insert_id'     => null,
            'mail_is_send'  => false,
        ];

        return new DataResponse($params);

        if(Helper::post('title') && Helper::post('message')) {

            $groupsusers = [];
            $all_users = $users = Helper::post('users', false);
            $groups = Helper::post('groups', false);

            if(!empty($groups)) {
                $groupsusers = $this->connect->users()->getGroupsUsersList();
                foreach($groups as $group) {
                    if(isset($groupsusers[$group]) && $rm_arr = $groupsusers[$group]) {
                        $rm_list = array_map(function($item){ return $item['uid']; }, $rm_arr);
                        $users = array_diff($users, $rm_list);
                    }
                }
            }

            $data['rid'] = 0;
            $data['date'] = date("Y-m-d H:i:s", time());
            $data['title'] = strip_tags(Helper::post('title'));
            $data['text'] = Helper::post('message');
            $data['attachements'] = '';
            $data['author'] = $this->userId;
            $data['subscribers'] = json_encode(['groups'=>$groups, 'users'=>$users]);
            $data['hash'] = TalkMail::createHash($data['title']);
            $data['status'] = TalkMail::SEND_STATUS_CREATED;

            if($params['insert_id'] = $this->connect->messages()->insertTask($data)) {
                $params['resultInsertTask'] = $params['insert_id'];
                $params['mail_is_send'] = $this->mailsendSwitcher($data, $all_users, $groups, $groupsusers);

            }

            $params['data'] = $data;
        }

        if($params['mail_is_send']) {
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
     * @return bool|int|string
     */
    public function mailsendSwitcher($talk = [], $users = [], $groups = [], $groupsusers = [])
    {
        $to = [];
        $fromUser = $this->mailUser ? $this->mailUser : $this->userId;

        if(!$fromUser)
            return false;

        foreach ($users as $user) {
            $_userData = $this->connect->users()->getUserData($user);
            if(!empty($_userData['email']))
                $to[] = [$_userData['email'], $_userData['displayname']];
        }

        $result =  TalkMail::createMail(
            [$fromUser.'@'.$this->mailDomain, $fromUser],
            [$fromUser.'+'.$talk['hash'].'@'.$this->mailDomain, $fromUser],
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
        $mail_domain = Helper::getSysConfig('mail_domain', false);

        $returned = [
            'to' => null,
            'from' => null,
            'type' => 'fail',
        ];

        $params = Helper::post();
        $to = explode('@', $params['to']);
        $idhash = explode('+',$to[0]);

        if (count($idhash) == 1) {

            // Groups emails
            // for the future realization

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
                }
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
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    /*public function uploadfirst($data = [])
    {
        $params = [
            'error'         => null,
            'errorinfo'     => '',
            'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister()
        ];

        return new DataResponse($params);
    }*/



    /**
     * get_file_list
     * @param $fileid
     * @return DataResponse
    public function getFile($fileid) {
    $files = $this->connect->files();
    $file = $files->getById($fileid);
    $params = array(
    'file' => $file,
    'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
    );
    $view = Helper::renderPartial($this->appName, 'api.uploadedfiles', $params);
    //$view = "User files!";

    $params = array(
    'user' => $this->userId,
    'file' => $file,
    'view' => $view,
    'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
    );

    return new DataResponse($params);
    }
     */



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
                    $_to_list['mtime'] = $file['mtime']/1000;
                    $_to_list['path'] = $root_path .'/'. $file['name'];
                    $this->_file_list_tree[] = $_to_list;
                }
            }
        }

        return $this->_file_list_tree;
    }



    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function getfolderfiles($data = [])
    {
        //$files = $this->connect->files();
        //$files->getFolderPath($folderid, $this->userId);
        //, $this->userId
        //$userfiles = \OCA\Files\Helper::getFiles('../'.$path);

        $params = array(
            'data' => $data,
            'user' => $this->userId,
            'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
        );

        $path = $this->connect->files()->getFolderPath($data['id']);
        $params['$path'] = $path;

        //$path = '/Talks/';
        //$file_1 = \OCA\Files\Helper::getFiles($path);
        //$params['$path'] = $path;
        //$params['$file_1'] = $file_1;
        //$userfiles = \OCA\Files\Helper::getFiles('/'.$path);

        /*
                        foreach($userfiles as $f => $file){
                            $userfiles[$f] = \OCA\Files\Helper::formatFileInfo($file);
                            $userfiles[$f]['mtime'] = $userfiles[$f]['mtime']/1000;
                        }

                                $params = array(
                                    'files' => $userfiles,
                                    'folder' => $userfiles,
                                    'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
                                );
                                $view = Helper::renderPartial($this->appName, 'part.folderfiles', $params);
                                //$view = "User files!";

                                $params['files'] = $userfiles;
                                $params['view'] = $view;*/





        return new DataResponse($params);
    }
























}