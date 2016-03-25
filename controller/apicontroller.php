<?php

namespace OCA\Owncollab_Talks\Controller;

use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\Db\Connect;
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

        if(method_exists($this, $key))
            return $this->$key($data);
        else return new DataResponse();
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
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function begintalk() {
		$subscribers = $this->getUsers();
		$canwrite = true; //TODO: Створити перевірку на право починати бесіди

		if ($canwrite) {
			$params = array(
				'user' => $this->userId,
				'subscribers' => $subscribers,
				'mode' => 'begin',
				'menu' => 'begin'
			);
		$view = Helper::renderPartial($this->appName, 'api.talk', $params);
		}
		else {
			return;
		}

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function alltalks() {
		$params = ['user' => $this->userId];

		if ($usermessages = $this->getUserMessages()) {
			$files = $this->connect->files();
			$messages = $usermessages->getByAuthorOrSubscriber($this->userId);
			$params = array(
				'user' => $this->userId,
				'messages' => $messages,
				'files' => $files,
				'menu' => 'all'
			);
		}
		$view = Helper::renderPartial($this->appName, 'api.talk', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function selectSubscribers() {
		$subscribers = $this->getUsers();
		$canwrite = true; //TODO: Створити перевірку на право починати бесіди
		if ($canwrite) {
			$params = array(
				'user' => $this->userId,
				'subscribers' => $subscribers,
				'mode' => 'subscribers',
				'menu' => 'subscribers'
			);
			$view = Helper::renderPartial($this->appName, 'api.talk', $params);
		}
		else {
			return;
		}

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function mytalks() {
		$messages = $this->connect->messages();
		$talks = $messages->getByAuthor($this->userId);
		$params = array(
			'user' => $this->userId,
			'talks' => $talks,
			'mode' => 'list',
			'menu' => 'mytalks'
		);
		$view = Helper::renderPartial($this->appName, 'api.talk', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function attachments() {
		$files = $this->connect->files();
		$userfiles = $files->getByUser($this->userId);

		$params = array(
			'user' => $this->userId,
			'files' => $userfiles,
			'mode' => 'attachments',
			'menu' => 'attachments'
		);
		$view = Helper::renderPartial($this->appName, 'api.talk', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function getuserfiles() {

		$files = $this->connect->files();
		$userfiles = $files->getByUser($this->userId);

		$params = array(
			'files' => $userfiles,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);
		$view = Helper::renderPartial($this->appName, 'api.userfiles', $params);
		//$view = "User files!";

		$params = array(
			'user' => $this->userId,
			'files' => $userfiles,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function getfolderfiles($folderid) {

		$files = $this->connect->files();
		$userfiles = $files->getByFolder($folderid, $this->userId);

		$params = array(
			'files' => $userfiles,
			'folder' => $userfiles,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);
		$view = Helper::renderPartial($this->appName, 'api.folderfiles', $params);
		//$view = "User files!";

		$params = array(
			'user' => $this->userId,
			'files' => $userfiles,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * Get list of users to build
	 * an array of subscribers
	 */
	public function getUsers() {
		$users = $this->connect->users();
		//$userlist = $users->getAll();
		$userlist = $users->getGroupsUsersList();

		return $userlist;
	}

	/**
	 * @param string $userid
	 * Get an object of UserMessages
	 *
	 */
	public function getUserMessages($userid = NULL) {
		$usermessages = $this->connect->userMessage();
		if ($userid) {
			$usermessages->setUser($userid);
		}
		return $usermessages;
	}

	/**
	 * @param int $talkid
	 * Get selected talk with answers
	 *
	 */
	public function getTalk($talkid) {
		$messages = $this->connect->messages();
		$talk = $messages->getById($talkid)[0];
		$answers = $messages->getByParent($talk['id']);
		$params = array(
			'user' => $this->userId,
			'talk' => $talk,
			'answers' => $answers,
			'appname' => $this->appName
		);

		$view = Helper::renderPartial($this->appName, 'api.talkanswers', $params);

		$params = array(
			'user' => $this->userId,
			//'talk' => $talk,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}
}