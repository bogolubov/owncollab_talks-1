<?php

namespace OCA\Owncollab_Talks\Controller;

use OC\Files\Filesystem;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\Db\Connect;
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

		//$files = $this->connect->files();
		//$userfiles = $files->getByUser($this->userId);
		$userfiles = \OCA\Files\Helper::getFiles('/');
		foreach($userfiles as $f => $file){
			$userfiles[$f] = \OCA\Files\Helper::formatFileInfo($file);
			$userfiles[$f]['mtime'] = $userfiles[$f]['mtime']/1000;
		}

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
		$path = $files->getFolderPath($folderid, $this->userId);
		//$userfiles = \OCA\Files\Helper::getFiles('../'.$path);
		$userfiles = \OCA\Files\Helper::getFiles('/'.$path);
		foreach($userfiles as $f => $file){
			$userfiles[$f] = \OCA\Files\Helper::formatFileInfo($file);
			$userfiles[$f]['mtime'] = $userfiles[$f]['mtime']/1000;
		}

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
		$answers = $messages->getByParent($talk['id'], 'date ASC');
		$params = array(
			'user' => $this->userId,
			'talk' => $talk,
			'answers' => $answers,
			'cananswer' => $messages->canAnswer($talk, $this->userId),
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

	/**
	 * @param int $talkid
	 * Get files attached to selected talk
	 *
	 */
	public function getTalkFiles($talkid) {
		$messages = $this->connect->messages();
		$talk = $messages->getById($talkid)[0];
		$files = $this->connect->files();
		if (!empty($talk['attachements'])) {
			$filenames = $files->getByIdList(explode(',', $talk['attachements']), $this->userId);
			$params = array(
				'filenames' => $filenames,
				'files' => $files,
				'user' => $this->userId
			);
		}
		else {
			$params = array(
				'talk' => $talk
			);
		}

		$view = Helper::renderPartial($this->appName, 'api.talkfiles', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @param int $fileid
	 * Get file by id
	 *
	 */
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

	/**
	 * @param int $talkid
	 * @param string $text
	 * Save an answer to the talk
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function saveDirectAnswer($args) {
		date_default_timezone_set('Europe/Berlin'); 

		$talkid = $args['talkid'];
		$users = $this->connect->users();
		$messages = $this->connect->messages();
		$message = $messages->getByReply($talkid);
		$talk = $messages->getById($talkid)[0];
		$usermessages = $this->connect->userMessage();

		$answers = $this->connect->answers();

		$answers->setTalkId($args['talkid']);
		$answers->setReply(true);
		$answers->setTitle(Helper::checkTxt($args['text']));
		$answers->setDate();
		$answers->setText(Helper::checkTxt($args['text']));
		$answers->setAuthor($this->userId, $talk['author']);
		$answers->setSubscribers($talk['subscribers']);
		$answers->setHash($talk['hash']);
		$answers->setProjectName($this->projectname);

		//Prepare subscribers lists
		$answers->devideSubscribers();
		$answers->prepareSubscribers();

		//Prepare data for saving
		$answers->prepareForSave();
		$answerId = $answers->save();
		foreach ($answers->subscriberToSave as $s => $item) {
			$this->setUserMessageStatus($item, $answers->answerId);
		}
		$this->setUserMessageStatus($answers->author, $answerId->talkId);

		$forSend = $answers->send();

		foreach ($answers->forSend['emails'] as $e => $email) {
			$this->setUserMessageStatus($email['name'], $forSend['answerid']);

			if (!empty($answers->forSend['data'])) {
				$sent = Helper::messageSend($email, $forSend['data'], $this->appName, $answers->subscriberToSend);
			}
		}

		//$answerid = 1;
		if ($answerId) {
			$params = array(
				'answerid' => $answerId,
				'author' => $this->userId,
				'date' => date("Y-m-d h:i:s"),
				'title' => Helper::checkTxt($answers->title),
				'sent' => $sent,
				'appname' => $this->appName
			);
		}
		else {
			$params = array(
				'title' => Helper::checkTxt($answers->title)
			);
		}

		$view = Helper::renderPartial($this->appName, 'api.addanswer', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @param int $talkid
	 * @param string $text
	 * Save an answer to the talk
	 */
	public function answerTalk($args) {
		$talkid = $args['talkid'];
		$text = $args['text'];
		$users = $this->connect->users();
		$messages = $this->connect->messages();
		$message = $messages->getByReply($talkid);
		$talk = $messages->getById($talkid)[0];
		$usermessages = $this->connect->userMessage();

		//$usermessages = $this->getUserMessages($this->userId);
		if (!$usermessage = $usermessages->getMessageById($message['id'])) {
			$usermessages->createStatus($message['id'], $this->userId);
			$usermessage = $usermessages->getMessageById($message['id']);
		}
		if ($message['status'] < 2) {
			$message['status'] = 2;
			$messages->setStatus($message['mid'], 2);
		}

		$subscribers = explode(',', $talk['subscribers']);
		if (!in_array($this->userId, $subscribers)) {
			$subscribers[] = $this->userId;
		}
		else {
			unset($subscribers[array_search($this->userId, $subscribers)]);
			$talk['subscribers'] = $subscribers;
			$subscribers[] = $talk['author'];
			//$subscribers[] = $this->userId;
			$talk['subscribers'] = $subscribers;
		}

		$mailsubscribers = array();
		$groupspref = array();
		foreach ($subscribers as $s => $subscriber) {
			if (strstr($subscriber, "-group")) {
				$group = substr($subscriber, 0, strpos($subscriber, "-group"));
				if ($group && is_string($group)) {
					foreach ($users->getUsersFromGroup($group) as $gu => $groupuser) {
						$user = $users->getUserDetails($groupuser['uid']);
						$groupusers[$groupuser['uid']] = $user;
					}
					$mailsubscribers[$group] = ['groupid' => $group, 'grouppref' => $group . '-group', 'groupusers' => $groupusers];
					$groupspref[] = $group.'-group';
				}
			}
			else if (!($subscriber == $this->userId)) {
				$allusers[$subscriber] = $users->getUserDetails($subscriber);
			}
		}
		$mailsubscribers['ungroupped'] = ['groupusers' => $allusers];

		if (count($mailsubscribers) > 0 && count($groupspref) > 0) {
			$messageSubscribers = implode(',', $groupspref);
			if (count($mailsubscribers['ungroupped']['groupusers']) > 0) {
				$messageSubscribers .= ',' . implode(',', array_keys($mailsubscribers['ungroupped']['groupusers']));
			}
		}
		else {
			$messageSubscribers = implode(',', array_keys($mailsubscribers['ungroupped']['groupusers']));
		}

		$messagedata = array(
			'rid' => $talkid,
			'date' => date("Y-m-d h:i:s"),
			'title' => Helper::checkTxt($text),
			'text' => '',
			'author' => $this->userId,
			//'subscribers' => is_array($talk['subscribers']) ? implode(',', $talk['subscribers']) : $talk['subscribers'],
			//'subscribers' => is_array($subscribers) ? implode(',', $subscribers) : $subscribers,
			'subscribers' => $messageSubscribers,
			'hash' => isset($talk['hash']) && !empty($talk['hash']) ? $talk['hash'] : md5(date("Y-m-d h:i:s").''.$text),
			'status' => 0
		);

		$messages = $this->connect->messages();
		$saved = $messages->save($messagedata);
		//$saved = 1;
		if ($saved) {
			foreach ($subscribers as $s => $subscriber) {
				if (is_string($subscriber) && !empty($subscriber)) {
					$usermessagedata = [
						'uid' => $subscriber,
						'mid' => $saved,
						'status' => 0
                    ];
					$usermessages->save($usermessagedata);
				}
			}

			$sent = $this->sendMessage($saved, $mailsubscribers, $this->userId, $messagedata);
			foreach ($mailsubscribers as $m => $ms) {
				if ($m == 'ungroupped') {
					$sent = $this->sendMessage($saved, $ms['groupusers'], $this->userId, $messagedata);
				}
				else {
					$messagedata['groupsid'] = $ms['grouppref'];
					$sent = $this->sendMessage($saved, $ms['groupusers'], $ms['grouppref'], $messagedata);
				}
			}

			$params = array(
				'answerid' => $saved,
				'author' => $this->userId,
				'date' => date("Y-m-d h:i:s"),
				'title' => Helper::checkTxt($text),
				'sent' => $sent,
				'appname' => $this->appName
			);
		}
		else {
			$params = array(
				'title' => Helper::checkTxt($text)
			);
		}

		$view = Helper::renderPartial($this->appName, 'api.addanswer', $params);

		$params = array(
			'user' => $this->userId,
			'view' => $view,
			'requesttoken'  => (!\OC_Util::isCallRegistered()) ? '' : \OC_Util::callRegister(),
		);

		return new DataResponse($params);
	}

	/**
	 * @param array $message
	 * @param array $subscribers
	 * Send the message to each user
	 * in subscribers list
	 */
	public function sendMessage($message, $subscribers, $from = '', $messagedata = NULL) {
		//if (!is_array($subscribers) && is_string($subscribers)) {
		//	$subscribers = explode(',', $subscribers);
		//}
		$um = $this->connect->userMessage();
		$users = $this->connect->users();
		//$isgroup = $users->isGroupSelected($subscribers);
		foreach ($subscribers as $s => $subscriber) {
			if (is_string($s) && !empty($s)) {
				$data = [
					'uid' => $s,
					'mid' => $message,
					'status' => 0
				];
				$um->save($data);
			}
		}
		if (!empty($messagedata)) {
			foreach ($subscribers as $s => $subscriber) {
				$sent = Helper::messageSend($subscriber, $from, $messagedata, $this->getProjectName(), true);
			}
		}
		return $sent;
	}

	private function setUserMessageStatus($userid, $messageid) {
		$um = $this->connect->userMessage();
		if (is_string($userid) && !empty($userid) && !empty($messageid)) {
			$data = [
				'uid' => $userid,
				'mid' => $messageid,
				'status' => 0
			];
			$um->save($data);
		}
	}

	public function getProjectName() {
		//return $this->appName;
		return "OwnCollab";
	}
}