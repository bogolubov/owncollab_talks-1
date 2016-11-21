<?php
/**
 * ownCloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Your Name <mail@example.com>
 * @copyright Your Name 2016
 */

namespace OCA\Owncollab_Talks\Controller;

use OC\Files\Filesystem;
use OCA\Owncollab_Talks\Configurator;
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\MtaConnector;
use OCP\Files;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Share;

class MainController extends Controller
{

    /** @var string current auth user id */
    private $userId;
    private $l10n;
    private $isAdmin;
    private $connect;
    private $configurator;
    private $mailDomain;

    /**
     * MainController constructor.
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
    )
    {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->configurator = $configurator;
        $this->mailDomain = $this->configurator->get('mail_domain');
    }

    /**
     * CAUTION: the @Stuff turns off security checks; for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {

        if (!$this->mailDomain) {
            $error = "<p>Failed to get a domain name</p>";

            //if (!Aliaser::getMTAConnection())
            //    $error .= "<p>Do not connect to an MTA database</p>";

            return $this->pageError($error);
        } else
            return $this->begin();
    }

    /**
     * Blocked application and show error message
     * @param $error_message
     * @return TemplateResponse
     */
    public function pageError($error_message)
    {

        $data = [
            'menu' => '',
            'content' => 'error',
            'user_id' => $this->userId,
            'error_message' => $error_message,
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function begin()
    {

        $data = [
            'menu' => 'begin',
            'content' => 'begin',
            'user_id' => $this->userId,
            'groupsusers' => $this->connect->users()->getGroupsUsersList(),
            'nogroup' => $this->connect->users()->getUngroupUsers(),
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function started()
    {

        $data = [
            'menu' => 'started',
            'content' => 'list',
            'messages' => $this->connect->messages()->getStarted($this->userId),
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function my()
    {
        $messages = $this->connect->messages()->getMy($this->userId);
        $data = [
            'menu' => 'my',
            'content' => 'list',
            'messages' => $messages,
        ];
        return new TemplateResponse($this->appName, 'main', $data);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function all()
    {

        $data = [
            'menu' => 'all',
            'content' => 'list',
            'messages' => $this->connect->messages()->getAll(),
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $id
     * @return TemplateResponse
     */
    public function read($id)
    {
        $attachements_info = [];
        $message = $this->connect->messages()->getById((int)$id);
        $parent = $this->connect->messages()->getById((int) $message['rid']);

        if(!empty($message['attachements'])) {

            $attach = [];

            try{
                $attach = json_decode($message['attachements'], true);
            }catch(\Exception $e){
                var_dump('Exception: '.$e->getMessage());
            }

            foreach ($attach as $at) {

                $file = $this->connect->files()->getInfoById($at);
                $fileInfo = false;
                $filePath = str_replace('files/', '', $file['path']);

                try {
                    $fileInfo = \OC\Files\Filesystem::getFileInfo($filePath);
                } catch ( \Exception $e) {}

                //var_dump($file);
                if(!$fileInfo){
                    $itemSource = \OCP\Share::getItemSharedWithBySource('file', $at);
                    if(is_array($itemSource) && !empty($itemSource)){
                        $fileInfo = \OC\Files\Filesystem::getFileInfo($itemSource['file_target']);
                        $filePath = $itemSource['file_target'];
                    }
                }

                if(!$fileInfo) continue;
                $icon = '/core/img/filetypes/image.svg'; // \OCA\Files\Helper::determineIcon($fileInfo);

                $attachements_info[] = [
                    'preview' => $icon,
                    'link' => "/remote.php/webdav/$filePath",
                    'file' => $file,
                    'info' => \OCA\Files\Helper::formatFileInfo($fileInfo),
                ];

            }

        }


        Helper::cookies('goto_message', ($message['rid'] == 0 ? $message['id'] : $parent['id']), 0, '/');

        $data = [
            'menu' => 'all',
            'content' => 'read',
            'message' => $message,
            'parent' => $parent,
            'attachements_info' => $attachements_info,
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function test()
    {
        $data = [];

//        $allUsers = $this->connect->users()->getAll();
//        $shareUIds = array_map(function($item){return $item['uid'];}, $allUsers);

        $allUsers = $this->connect->users()->getAll();
        $shareUIds = array_map(function($item){
            if ($item['uid'] !== $this->configurator->get('collab_user')) {
                return $item['uid'];
            }
        }, $allUsers);
        $shareUIds = array_unique($shareUIds);
        //$shareUIds = array_diff(array_unique($shareUIds), ['',null,false]);

        return new DataResponse($shareUIds);
    }

    static public function getGlobalFileById(){}

    /**
     * Return attachements ids in array
     * Decode JSON string
     *
     * @param $json
     * @return mixed|null
     */
    static public function decodeAttachements ($json)
    {
        $attach = null;
        try {
            $attach = json_decode($json, true);
        } catch ( \Exception $e) {}
        return $attach;
    }


}