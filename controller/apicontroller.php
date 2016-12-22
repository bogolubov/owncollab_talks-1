<?php

namespace OCA\Owncollab_Talks\Controller;


use OCA\Owncollab_Talks\FileManager;
use OCA\Owncollab_Talks\MailManager;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\TalkManager;
use OCA\TemplateEditor\Http\MailTemplateResponse;
use OCP\Activity\IManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

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
    /** @var \OCA\Activity\Data */
    private $activityData;
    /** @var IManager */
    private $manager;
    private $mailDomain;
    public $mailUser = false;
    private $listtree = [];


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
        Configurator $configurator,
        \OCA\Activity\Data $activityData,
        IManager $manager
    ){
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->configurator = $configurator;
        $this->activityData = $activityData;
        $this->manager = $manager;
        $this->mailDomain = $this->configurator->get('mail_domain');
        $this->urlGenerator = \OC::$server->getURLGenerator();
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        $key = Helper::post('key')
            ? Helper::post('key')
            : $_GET['key'];

        $data = Helper::post('data', false)
            ? Helper::post('data', false)
            : $_GET['data'];

        if(!$this->mailDomain && !in_array($key, ['parserlog']))
            return new DataResponse(['error'=>'Email domain is undefined']);

        if(method_exists($this, $key))
            return call_user_func([$this, $key], $data);
        else
            return new DataResponse(['error'=>'Api key not exist']);
    }

    /**
     * Begin talk or Replay talk
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function insert($data)
    {

        $post = Helper::post();
        $data = !empty($post["data"]) ? $post["data"] : [];
        $hash = !empty($data["hash"]) ? $data["hash"] : false;
        $taskParent = null;
        $front = [];
        $UID = $this->userId;
        // bad request
        if ($post['uid'] !== $UID)
            return false;

        $front['post'] = $post;

        $tManager = new TalkManager($UID, $this->connect, $this->configurator);
        $fManager = new FileManager($UID, $this->connect, $this->activityData, $this->manager);
        $mManager = new MailManager($UID, $this->connect, $this->configurator, $tManager, $fManager);

        // Replay talk
        if ($hash && $taskParent = $this->connect->messages()->getByHash($hash)) {

            // change subscribers
            $subscribersChanged = $tManager->subscribersChange (
                $taskParent['subscribers'],
                ['users' => [$UID]],
                ['users' => [$taskParent['author']]]
            );

            // data for db insert replay talk
            $buildData = $tManager->build([
                'rid'           => $taskParent['id'],
                'title'         => 'RE: '.$taskParent['title'],
                'text'          => $data['message'],
                'subscribers'   => $subscribersChanged,
                'attachements'  => [],
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);

            $insertId = $this->connect->messages()->insert($buildData);

            $front['insert_id'] = $insertId;
            $front['parent_id'] = $taskParent['id'];

        }
        // Begin talk
        else {

            // ready upload and shared files
            // text tags
            $textTags = '<br><p><blockquote><h1><h2><h3><h4><strong><em><del><a><ul><ol><li><hr><img>';
            $postShare  = isset($post['share'])  ? array_keys($post['share']) : [];
            $postUsers  = isset($post['users'])  ? array_values($post['users']) : [];
            $postGroups = isset($post['groups']) ? array_values($post['groups']) : [];
            $subscribersChanged = $tManager->subscribersCreate($postGroups, $postUsers);
            $buildData = $tManager->build([
                'title'         => $post['title'],
                'text'          => strip_tags($post['message'], $textTags),
                'subscribers'   => $subscribersChanged,
                'attachements'  => json_encode($postShare),
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);
            $buildData['text'] = addslashes($buildData['text']);

            $insertId = $this->connect->messages()->insert($buildData);
            $front['insert_id'] = $insertId;

            // Share files for all users
            $shared_for = false;
            if ($insertId && !empty($postShare)) {
                $usersids = array_diff($this->connect->users()->getUsersIDs(), [$UID]);

                foreach($usersids as $u) {
                    foreach($postShare as $fid) {

                        if (empty($fid) || empty($u))
                            continue;

                        $shared_for[] = $fManager->shareFileWith($fid, $u);
                    }
                }
            }
            $front['shared_for'] = $shared_for;
        }

        // SEND Emails
//        $taskFiles = [];
//        if (isset($postShare)) {
//            foreach ($postShare as $fid) {
//                $taskFiles[] = $fManager->getFileInformation($fid);
//            }
//        }
        $attachfilesInfo = [];
        if (isset($postShare)) {
            $attachfilesInfo = $fManager->getFilesDataInfo($postShare);
        }

        $usersIds = $mManager->getUsersFromSubscribers($subscribersChanged, $UID);

        // форм. удобный список [['uid'=>,'email'=>,]]
        $owner = $this->connect->users()->getUserData($UID);
        //$usersEmailsData = [];
        $userDataEmptyEmails = [];
        $server_host = $this->configurator->get('server_host');
        $mail_domain = $this->configurator->get('mail_domain');
        foreach ($usersIds as $uid) {
            $ud = $this->connect->users()->getUserData($uid);
            //$htmlBody = $mManager->createTemplate($buildData, $taskFiles, $ud['uid']);

/*            $attachfilesInfoRebuild = [];
            if (!empty($attachfilesInfo)) {
                for ($iau=0; $iau<count($attachfilesInfo); $iau++) {
                    $attachfilesInfoRebuild[$iau] = $attachfilesInfo[$iau];
                    $attachfilesInfoRebuild[$iau]['webdav'] = $this->connect->files()->getFileLink($attachfilesInfo[$iau]['fileid'], $uid);
                }
            }*/
            $links = [];
            if (!empty($attachfilesInfo)) {
                for ($iau=0; $iau<count($attachfilesInfo); $iau++) {
                    $link = $this->connect->files()->getFileLink($attachfilesInfo[$iau]['fileid'], $uid);
                    $attachfilesInfo[$iau]['webdav'] = $link;

                    $links[$attachfilesInfo[$iau]['fileid']] = $uid;
                }
            }

            var_dump($uid);
            var_dump($links);
            var_dump($attachfilesInfo);

            $htmlBody = $mManager->createTemplateStart($ud, $buildData, $attachfilesInfo);

            //todo: need condition to mta virtual users
            if (isset($buildData['rid']) && $taskParent) {
                $ownerEmail = $taskParent['author'] .'+'. $taskParent['hash'] . '@' . $mail_domain;
            } else {
                $ownerEmail = $buildData['author'] .'+'. $buildData['hash'] . '@' . $mail_domain;
            }


            //send mail
            if (false && !empty($ud['email'])) {
                $mManager->send(
                    [
                        'email' => $ud['email'],
                        'name' => $ud['uid'],
                    ],
                    [
                        'email' => $ownerEmail,
                        'name' => $owner['uid'],
                    ],
                    $server_host . ' // ' . $buildData['title'],
                    $htmlBody,
                    $attachfilesInfo
                );
            } else {
                $userDataEmptyEmails[] = $ud;
            }
            //$usersEmailsData[] = $ud;
        }

        // UsersData with empty emails
        // Create email to ???
        if (!empty($userDataEmptyEmails)) {

        }

        die;

        if($front['insert_id'] && !$taskParent) {
            Helper::cookies('goto_message', $front['insert_id']);
            header("Location: /index.php/apps/owncollab_talks/started");
            exit;
        } else
            return new DataResponse($front);

    }


    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * cat mails/bogdan.mail | php -f mailparser.php
     * cat mails/team.mail | php -f mailparser.php
     * cat mails/group.mail | php -f mailparser.php
     *
     * @param $data
     * @return DataResponse
     */
    public function parser($data)
    {
        $result = [];
        $post = Helper::post();
        $to = explode('@', $post['to']);
        $toPart = $to[0];
        $subject = $post['subject'];
        $content = empty($post['content']) ? strip_tags($post['content_html']) : $post['content'];
        $groupPrefix = $this->configurator->get('group_prefix');

        // Owner. Key: userid
        $userfrom = $this->connect->users()->getByEmail(trim($post['from']));
        $userfromData = $this->connect->users()->getUserData($userfrom['userid']);

        if (!$userfromData) {
            $result['error'] = 'User not found';
            return new DataResponse($result);
        }

        // вычисление назначения
        $itReplay       = false;
        $itGroup        = false;
        $toGroup        = false;
        $itTeam         = false;
        $itUser         = false;
        $toUser         = false;
        $messageParent  = false;

        if ($parts = explode('+', $toPart) AND count($parts) === 2) {
            $itReplay       = true;
            $hash           = trim($parts[1]);
            $messageParent  = $this->connect->messages()->getByHash($hash);
        }
        else if ($toGroup = substr($toPart, -strlen($groupPrefix)) AND $toGroup === $groupPrefix) {
            $toGroup = substr($toPart, 0, -(strlen($groupPrefix)));
            $groupsList = $this->connect->users()->getGroupsUsersList();
            if (isset($groupsList[$toGroup]))
                $itGroup = true;
        }
        else if ($toPart === 'team') {
            $itTeam = true;
        }
        else if ($toUser = $this->connect->users()->getUserData($toPart)) {
            $itUser = true;
        }

        // save files
        $files = [];
        $shared_for = [];

        // Owner. Mail from user
        $UID = $userfrom['userid'];

        // work libs
        $tManager = new TalkManager($UID, $this->connect, $this->configurator);
        $fManager = new FileManager($UID, $this->connect, $this->activityData, $this->manager);
        $mManager = new MailManager($UID, $this->connect, $this->configurator, $tManager, $fManager);

        // обработка файлов
        if ((int) $post['files_count'] > 0 && is_array($post['files']) ) {

            // Insert file to local dir and write it to database
            foreach($post['files'] as $f) {

                $tmpfileName = substr($f['tmpfile'], strrpos($f['tmpfile'], '/') + 1);
                $insertId = $fManager->insertTpmFile( $tmpfileName, $f['filename']);

                if ($insertId) {
                    $files[$insertId] = [
                        'fileid'=>$insertId,
                        'filename'=>$f['filename']
                    ];
                } else {
                    $result['error'] .= "upload file to user failed: $tmpfileName; ";
                }
            }

            if ((int) Helper::post('files_count') == count($files)) {

                // Share file to all users
                $usersids = array_diff($this->connect->users()->getUsersIDs(), [$UID]);
                foreach($usersids as $u) {foreach($files as $fs) {
                    $shared_for[] = $fManager->shareFileWith($fs['fileid'], $u);
                }}

            } else if ((int) Helper::post('files_count') > 0) {
                // error with save
                $result['error'] = "Error: Insert files to user";
            }
        }

        $insertId = false;
        $buildData = [];
        $subscribers = [];

        // оброботка входящих ответов c $hash
        if ($itReplay && $messageParent) {

            $subscribers = $tManager->subscribersChange(
                $messageParent['subscribers'],
                ['users' => [$UID]],
                ['users' => [$messageParent['author']]]
            );

            // insert message
            $buildData = $tManager->build([
                'rid'           => $messageParent['id'],
                'title'         => 'Re: ' . $messageParent['title'],
                'text'          => $content,
                'subscribers'   => $subscribers,
                'attachements'  => json_encode(array_keys($files)),
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);

            $insertId = $this->connect->messages()->insert($buildData);


        } else if ($itTeam) {
            //team@domain.com
            $uids = $this->connect->users()->getUsersIDs();
            $subscribers = $tManager->subscribersCreate([], $uids);
            $subscribers = $tManager->subscribersChange($subscribers, ['users' => [$UID]]);
            // insert message
            $buildData = $tManager->build([
                'title'         => $subject,
                'text'          => $content,
                'subscribers'   => $subscribers,
                'attachements'  => json_encode(array_keys($files)),
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);

            $insertId = $this->connect->messages()->insert($buildData);

        } else if ($itGroup) {
            //somegroup-group@domain.com
            $subscribers = $tManager->subscribersCreate([$toGroup], []);
            // insert message
            $buildData = $tManager->build([
                'title'         => $subject,
                'text'          => $content,
                'subscribers'   => $subscribers,
                'attachements'  => json_encode(array_keys($files)),
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);

            $insertId = $this->connect->messages()->insert($buildData);

        } else if ($itUser) {
            //userid@domain.com
            $subscribers = $tManager->subscribersCreate([], [$toUser]);
            // insert message
            $buildData = $tManager->build([
                'title'         => $subject,
                'text'          => $content,
                'subscribers'   => $subscribers,
                'attachements'  => json_encode(array_keys($files)),
                'author'        => $UID,
                'hash'          => $tManager->createhash(),
            ]);

            $insertId = $this->connect->messages()->insert($buildData);

        }

        // Send mail to subscribers false &&
        if ($insertId) {
            $result['success'] = $insertId;
//            $taskFiles = [];
//            if (isset($files) && is_array($files)) {
//                foreach ($files as $fid) {
//                    $taskFiles[] = $fManager->getFileInformation($fid['fileid']);
//                }
//            }

            $attachfilesInfo = [];
            if (!empty($files))
                $attachfilesInfo = $fManager->getFilesDataInfo(array_keys($files));

            $usersIds = $mManager->getUsersFromSubscribers($subscribers);
            $server_host = $this->configurator->get('server_host');
            $mail_domain = $this->configurator->get('mail_domain');

            foreach($usersIds as $uid) {
                $ud = $this->connect->users()->getUserData($uid);

                if (!empty($attachfilesInfo)) {
                    for ($iau=0;$iau<count($attachfilesInfo);$iau++) {
                        $attachfilesInfo[$iau]['webdav'] = $this->connect->files()->getFileLink($attachfilesInfo[$iau]['fileid'], $uid);
                    }
                }

                //$htmlBody = $mManager->createTemplate($buildData, $taskFiles, $ud['uid']);
                $htmlBody = $mManager->createTemplateStart($ud, $buildData, $attachfilesInfo);

                //todo: need condition to mta virtual users
                if ($messageParent) {
                    $ownerEmail = $messageParent['author'] .'+'. $messageParent['hash'] . '@' . $mail_domain;
                } else {
                    $ownerEmail = $userfromData['uid'] .'+'. $buildData['hash'] . '@' . $mail_domain;
                }

                //send mail
                if (!empty($ud['email'])) {
                    $mManager->send(
                        [
                            'email' => $ud['email'],
                            'name' => $ud['uid'],
                        ],
                        [
                            'email' => $ownerEmail,
                            'name' => $userfromData['uid'],
                        ],
                        $server_host . ' // ' . $buildData['title'],
                        $htmlBody,
                        $attachfilesInfo
                    );
                }
            }


        } else {
            $result['error'] = 'Error Insert new talk is failed';
        }

        return new DataResponse($result);
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
            'error'             => null,
            'errorinfo'         => '',
            'lastlinkid'        => null,
            'attachedfiles'     => null,
        ];

        if(isset($data['parent_id'])) {
            $id = (int) $data['parent_id'];
            $params['parent'] = $this->connect->messages()->getById($id);
            $params['children'] = $this->connect->messages()->getChildren($id);
            $params['messageslist'] = Helper::renderPartial($this->appName,'part.messageslist',[
                'parent'    =>  $params['parent'],
                'children'  =>  $params['children'],
            ]);

            // Add Attached files
            $allfilesids = [];

            try {
                $allfilesids = json_decode($params['parent']['attachements'], true);
            }catch (\Exception $e){}

            if (!empty($params['children'])) {
                foreach ($params['children'] as $child) {
                    if(!empty($child['attachements'])) {
                        try {
                            $childfilesids = json_decode($child['attachements'], true);
                            $allfilesids = array_merge($childfilesids, $allfilesids);
                        }catch (\Exception $e){}
                    }
                }
            }
            if (!empty($allfilesids)) {

                $attachfilesInfo = $this->connect->files()->getInfoByIds($allfilesids);

                //$params['f'] = $attachfilesInfo[0];
                //$params['flink'] = $this->connect->files()->getFileLink($attachfilesInfo[0]['fileid'], $this->userId);

                if (!empty($attachfilesInfo)) {
                    for ($iau=0;$iau<count($attachfilesInfo); $iau++) {
                        $attachfilesInfo[$iau]['webdav'] = $this->connect->files()->getFileLink($attachfilesInfo[$iau]['fileid'], $this->userId);
                    }
                }

                $params['attachedfiles'] = Helper::renderPartial($this->appName,'part.attachlist',[
                    'attachfiles' => $attachfilesInfo
                ]);
            }
        }

        return new DataResponse($params);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse
     */
    public function getuserfiles()
    {
        $params = ['user' => $this->userId];
        $fileList = $this->createFileListTree();
        $params['file_list'] = $fileList;
        $params['view'] = Helper::renderPartial($this->appName, 'part.userfilelist', $params);
        return new DataResponse($params);
    }

    public function createFileListTree($path = '/')
    {
        $files = \OCA\Files\Helper::getFiles($path);
        if (is_array($files)) {
            foreach($files as $f => $file) {
                if($file['type'] == 'dir') {
                    $this->createFileListTree(substr($file['path'],6));
                }
                else {
                    $fdata = \OCA\Files\Helper::formatFileInfo($file);
                    $fdata['mtime'] = $file['mtime']/1000;
                    $fdata['path'] = substr($file['path'],6);
                    array_push($this->listtree, $fdata);
                }
            }
        }
        return $this->listtree;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function parserlog($data)
    {
        $post = Helper::post();
        $file = $post['data']['file'];
        $data['log'] = 'Error. can`t read log file';
        $path = dirname(__DIR__) .'/'. $file;

        if (is_file($path) && is_readable($path)) {
            $data['log'] = file_get_contents($path);
        }

        return new DataResponse($data);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function test($data)
    {

/*        $UID = 'bogdan';
        $messageParent = $this->connect->messages()->getById(1);

        // work libs
        $tManager = new TalkManager($UID, $this->connect, $this->configurator);
        //$fManager = new FileManager($UID, $this->connect, $this->activityData, $this->manager);
        //$mManager = new MailManager($UID, $this->connect, $this->configurator, $tManager, $fManager);

        $subscribers = $tManager->subscribersChange(
            $messageParent['subscribers'],
            ['users' => [$UID]],
            ['users' => [$messageParent['author']]]
        );

        var_dump($subscribers);

        die;
        return new DataResponse($data);*/


/*
        // Testing email template
        $UID = 'admin';
        $UIDTO = 'bogdan';

        $tManager = new TalkManager($UID, $this->connect, $this->configurator);
        $fManager = new FileManager($UID, $this->connect, $this->activityData, $this->manager);
        $mManager = new MailManager($UID, $this->connect, $this->configurator, $tManager, $fManager);

        $talkmessage = $this->connect->messages()->getById(2);
        $attachementsFilesIds = json_decode($talkmessage['attachements'], true);
        $attachfilesInfo = $fManager->getFilesDataInfo($attachementsFilesIds);
        $userDataTo = $this->connect->users()->getUserData($UIDTO);

        $email  = $mManager->createTemplateStart($userDataTo, $talkmessage, $attachfilesInfo);

        exit($email);*/

        //var_dump($attachfilesInfo);

        /*
        // Parse mail content
        $groupPrefix = $this->configurator->get('group_prefix');
        $toPart = 'developers-group';
        $itGroup = false;
        if ($toGroup = substr($toPart, -strlen($groupPrefix)) AND $toGroup === $groupPrefix) {
            $toGroup = substr($toPart, 0, -(strlen($groupPrefix)));
            $groupsList = $this->connect->users()->getGroupsUsersList();
            if (isset($groupsList[$toGroup]))
                $itGroup = true;
        }
        var_dump($toGroup);
        var_dump($itGroup);*/


/*
        // Link
        $siteurl = $this->configurator->get('site_url');
        $file = $this->connect->files()->getInfoByIds(27)[0];
        $filelink = $siteurl . 'remote.php/webdav' . $file['file'];
        var_dump($file);
        var_dump($filelink);*/

        // Link TRUE
        $fown = $this->connect->files()->getFileLink(973, 'werd');
        $fshr = $this->connect->files()->getFileLink(973, 'dev1');

        var_dump($fown);
        var_dump($fshr);
        die;
        //$htmlBody = $mManager->createTemplate($buildData, $taskFiles, $ud['uid']);
        //$data = [ ];
        //$email = Helper::renderPartial($this->appName, 'emails/start', $data);
        //return new TemplateResponse($this->appName, 'emails/start', $data);
        //return new MailTemplateResponse($this->appName, 'emails/start', $data);
    }

}