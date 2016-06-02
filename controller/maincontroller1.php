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
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\MailParser;
use OCA\Owncollab_Talks\ParseMail;
use OCP\Files;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Share;

class MainController extends Controller {

	/** @var string current auth user id */
	private $userId;
	private $l10n;
	private $isAdmin;
	private $connect;
	private $projectname = "Base project";

	/**
	 * MainController constructor.
	 * @param string $appName
	 * @param IRequest $request
	 * @param $userId
	 * @param $isAdmin
	 * @param $l10n
	 * @param Connect $connect
	 */
	public function __construct(
		$appName,
		IRequest $request,
		$userId,
		$isAdmin,
		$l10n,
		Connect $connect

    ){
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->isAdmin = $isAdmin;
		$this->l10n = $l10n;
		$this->connect = $connect;
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
	public function index() {
		$params = ['user' => $this->userId];

		if ($usermessages = $this->getUserMessages()) {
			$files = $this->connect->files();
			$messages = $this->connect->messages();
			$talks = $usermessages->getAll();
			$firsttalk = $messages->getByParent($talks[0]['messageid'], 'date ASC');
			$params = array(
				'user' => $this->userId,
				//'talks' => $talks,
				'messages' => $talks,
				'answers' => $firsttalk,
				'cananswer' => $messages->canAnswer($messages->getById($talks[0]['messageid'])[0], $this->userId),
				'appname' => $this->appName,
				'files' => $files,
				'menu' => 'all'
			);
		}

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function talk($id) {
		$messages = $this->connect->messages();
		$talk = $messages->getById($id)[0];
		if ($messages->canRead($talk, $this->userId)) {
		$params = array(
			'user' => $this->userId,
			'message' => $talk,
			'mode' => 'read'
		);

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
		}
		else {
			return;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function read($id) {
		$usermessages = $this->getUserMessages($this->userId);
		$message = $usermessages->getMessageById($id);
		$talks = $this->connect->messages();
		$talk = $talks->getById($id)[0];
		$subscribers = explode(',', $talk['subscribers']);
		$files = $this->connect->files();
		if (!($talk['author'] == $this->userId) && !(in_array($this->userId, $subscribers))) {
			$usermessages = $this->getUserMessages($this->userId);
			$message = $usermessages->getMessageById($id, $talk['author']);
			//print_r($message);
			//TODO: Створити темплейт з повідомленням про відсутність прав на читання
			//return;
		}
		if ($talk['author'] == $this->userId) { // If it's author
			$usermessages = $this->getUserMessages($subscribers[0]);
			$message = $usermessages->getMessageById($id);
		}
		if (in_array($this->userId, $subscribers)) { // If it's subscriber
			$usermessages = $this->getUserMessages($this->userId);
			$message = $usermessages->getMessageById($id, $talk['author']);
			if ($message['status'] == 0) {
				$message['status'] = 1;
				$usermessages->setStatus($message);
			}
		} 
		if (!empty($message)) {
			$params = array(
				'user' => $this->userId,
				'message' => $message,
				'talk' => $talk,
				'subscribers' => $subscribers,
				'files' => $files,
				'mode' => 'read'
			);

			return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
		}
		else {
			return;
		} 
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function reply($id) {
		$messages = $this->connect->messages();
		$message = $messages->getByReply($id);
		//$message = $messages->getById($id)[0];
		$usermessages = $this->getUserMessages($this->userId);
		if (!$usermessage = $usermessages->getMessageById($message['id'])) {
			$usermessages->createStatus($message['id'], $this->userId);
			$usermessage = $usermessages->getMessageById($message['id']);
		}
		if (!$userstatus = $usermessages->getUserStatus($message['id'])) {
			$usermessages->createStatus($message['id'], $this->userId);
			$userstatus = $usermessages->getUserStatus($message['id']);
		}
		$subscribers = $this->getUsers();
		//$helper = new Helper();
		if ($messages->canRead($message, $this->userId)) {
			if ($message['status'] < 2) {
				$message['status'] = 2;
				$messages->setStatus($message['mid'], 2);
			}
			if ($usermessage && $usermessage['status'] < 2) {
				$usermessage['status'] = 2;
				$usermessages->setStatus($usermessage);
			}
			$params = array(
				'user' => $this->userId,
				'talk' => $message,
				'replyid' => $messages->getMessageTopParent($message['mid']),
				'subscribers' => $subscribers,
				'userstatus' => $userstatus,
				'mode' => 'reply'
			);

			return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
		}
		else {
			return;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function begin() {
		$errors = array();
		$subscribers = $this->getUsers();
		$canwrite = true; //TODO: Створити перевірку на право починати бесіди
		$permissions = $this->checkUserDirPermissions();
		if (substr($permissions, -1) < 7) {
			$setPermissions = $this->setUserDirPermissions(7, $permissions);
			if (!($setPermissions == 'success')) {
				$errors[] = $setPermissions;
			}
		}
		if ($canwrite) {
			$params = array(
				'user' => $this->userId,
				'subscribers' => $subscribers,
				'mode' => 'begin',
				'menu' => 'begin',
				'errors' => !empty($errors) ? $errors : NULL
			);

			return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
		}
		else {
			return;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
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

			return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
		}
		else {
			return;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
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

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function save() {
		$files = $this->connect->files();
		$users = $this->connect->users();
		//$user = $users->getById($this->userId)[0];
		//Helper::uploadFile($_FILES['uploadfile'], $this->userId);
		foreach ($_POST['users'] as $s => $subscriber) {
			if (!($subscriber == $this->userId)) {
				$selectedSubscribers[$subscriber] = $users->getUserDetails($subscriber);
			}
		}
		$allusers = array_keys($selectedSubscribers);

		// Get subscribers group
		if (count($_POST['groups']) > 0) {
			$tomessages = array();
			$groupspref = array();
			foreach ($_POST['groups'] as $group) {
				if (!empty($group)) {
					foreach ($users->getUsersFromGroup($group) as $gu => $groupuser) {
						$groupusers[$groupuser['uid']] = $users->getUserDetails($groupuser['uid']);
						if (!in_array($groupuser['uid'], $allusers)) {
							$allusers[] = $groupuser;
						}
					}
					$tomessages[$group] = ['groupid' => $group, 'grouppref' => $group.'-group', 'groupusers' => $groupusers];
					$groupspref[] = $group.'-group';
                }
            }
		}
		$tomessages['ungroupped'] = ['groupusers' => $selectedSubscribers];

		//Фільтрація унікальних користувачів
		/* foreach ($tomessagesgroupusers as $gr => $group) {
			foreach ($group as $gu => $groupuser) {
				if (!in_array($groupuser['uid'], $tousers) && !($groupuser['uid'] == $this->userId)) {
					$tousers[$groupuser['uid']] = $users->getUserDetails($groupuser['uid']);
				}
			}
		} */
		//print_r($tousers);

		//$from = count($groupsid) > 0 ? $groupsid : $this->userId;
		$from = $this->userId;

		// Share selected files with selected users
		$filesid = array();
		foreach ($_POST['upload-files'] as $id) {
			$file = $files->getById($id)[0];
			$fileOwner = \OC\Files\Filesystem::getOwner($file['path']);
			$sharetype = $file['mimetype'] == 2 ? 'folder' : 'file';
			$sharedWith = \OCP\Share::getUsersItemShared($sharetype, $file['fileid'], $fileOwner, false, true);
			foreach ($allusers as $userid => $user) {
				if (isset($file['fileid']) && is_array($file) && isset($file['fileid']) && !in_array($userid, $sharedWith) && !($userid == $this->userId)) {
					\OCP\Share::shareItem($sharetype, $file['fileid'], \OCP\Share::SHARE_TYPE_USER, $userid, 1);
					$filesid[] = $id;
				}
			}
		}
		foreach ($_POST['select-files'] as $id => $on) {
			if ($on == 'on') {
				$file = $files->getById($id)[0];
				$fileOwner = \OC\Files\Filesystem::getOwner($file['path']);
				$sharetype = $file['mimetype'] == 2 ? 'folder' : 'file';
				$sharedWith = \OCP\Share::getUsersItemShared($sharetype, $file['fileid'], $fileOwner, false, true);
				foreach ($allusers as $userid => $user) {
					if (isset($file['fileid']) && is_array($file) && isset($file['fileid']) && !in_array($userid, $sharedWith) && !($userid == $this->userId)) {
						//Helper::shareFile($file['name'], $user, $userid);
						\OCP\Share::shareItem($sharetype, $file['fileid'], \OCP\Share::SHARE_TYPE_USER, $userid, 1);
						$filesid[] = $id;
					}
				}
			}
		}

		if (count($tomessages) > 0) {
			$messageSubscribers = implode(',', $groupspref).','.implode(',', array_keys($tomessages['ungroupped']['groupusers']));
		}
		else {
			$messageSubscribers = implode(',', array_keys($tomessages['ungroupped']['groupusers']));
		}
		$messagedata = array(
			'rid' => $_POST['replyid'],
			'date' => date("Y-m-d h:i:s"),
			'title' => $_POST['title'],
			'text' => Helper::checkTxt($_POST['message-body']),
			'attachements' => implode(',', $filesid),
			'author' => $this->userId,
			'subscribers' => implode(',', array_keys($tomessages['ungroupped']['groupusers'])),
			'hash' => isset($_POST['talkhash']) && !empty($_POST['talkhash']) ? $_POST['talkhash'] : md5(date("Y-m-d h:i:s").''.$_POST['title']),
			'status' => 0
		);

		$messages = $this->connect->messages();
		$saved = $messages->save($messagedata);
		//$saved = 1;
		if ($saved) {
			$messagedata['attachlinks'] = Helper::makeAttachLinks($filesid, $files);
			foreach ($tomessages as $m => $tm) {
				if ($m == 'ungroupped') {
					//$messagedata['subscribers'] = implode(',', array_keys($tomessages['ungroupped']['groupusers']));
					$this->sendMessage($saved, $tm['groupusers'], $this->userId, $messagedata);
				}
				else {
					//$messagedata['subscribers'] = $tm['grouppref'];
					$messagedata['groupsid'] = $tm['grouppref'];
					$this->sendMessage($saved, $tm['groupusers'], $tm['grouppref'], $messagedata);
				}
			}
		}

		$canwrite = true; //TODO: Створити перевірку на право починати бесіди

		$usermessages = $this->getUserMessages();
		$talks = $usermessages->getByAuthorOrSubscriber($this->userId, '0');
		$firsttalk = $messages->getByParent($talks[0]['messageid'], 'date ASC');
		if ($canwrite) {
			header('Location: /index.php/apps/'.$this->appName.'/all');
			exit();
			/* $params = array(
				'user' => $this->userId,
				'message' => $_POST,
				'messages' => $talks,
				'answers' => $firsttalk,
				'appname' => $this->appName,
				'files' => $files,
				'mode' => 'list',
				'menu' => 'all'
			);

			return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
			 */
		}
		else {
			return;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function startedtalks() {
		$messages = $this->connect->messages();
		$talks = $messages->getByAuthor($this->userId, 0, 'date DESC');
		$firsttalk = $messages->getByParent($talks[0]['id'], 'date ASC');
		$files = $this->connect->files();
		$params = array(
			'user' => $this->userId,
			'talks' => $talks,
			'answers' => $firsttalk,
			'cananswer' => true,
			'files' => $files,
			'appname' => $this->appName,
			'mode' => 'list',
			'menu' => 'startedtalks'
		);

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function mytalks() {
		$messages = $this->connect->messages();
		$users = $this->connect->users(); 
		$userGroups = $users->getUserGroups($this->userId); 

		//$talks = $messages->getByAuthor($this->userId, 0, 'date DESC');
		$usermessages = $this->getUserMessages();
		$talks = $usermessages->getBySubscriber($this->userId, '0');
		//if (count($userGroups) > 0)) { 
		//	$talks = $usermessages->getBySubscriber($this->userId, '0', $userGroups); 
		//} 
		
		$firsttalk = $messages->getByParent($talks[0]['messageid'], 'date ASC');
		$files = $this->connect->files();
		$params = array(
			'user' => $this->userId,
			//'talks' => $talks,
			'messages' => $talks,
			'answers' => $firsttalk,
			'cananswer' => true,
			'files' => $files,
			'appname' => $this->appName,
			'mode' => 'list',
			'menu' => 'mytalks'
		);

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param $talk int
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function addUser($talk) {
		//TODO: Створити випадаюче меню з користувачами
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param $talk int
	 * @param $user string
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function removeUser($talk, $user = NULL) {
		if (!$user) {
			$user = $this->userId;
		}
		$messages = $this->connect->messages();
		$message = $messages->getById($talk)[0];

		$subscribers = explode(',', $message['subscribers']);
		unset($subscribers[array_search($user, $subscribers)]);
		$message['subscribers'] = implode(',',$subscribers);
		$messages->update($message);

		$usermessages = $this->getUserMessages($user);
		$usermessage = $usermessages->getMessageById($talk);
		$usermessage['status'] = 3;
		$usermessages->setStatus($usermessage);

		$params = array(
			'user' => $this->userId,
			'messages' => $usermessages->getBySubscriber($this->userId),
			'mode' => 'all'
		);

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}

	/**
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function parseMail() {

		$messages = $this->connect->messages();
		$usermessages = $this->connect->userMessage();
		$message = $_POST['message'];

		//$checkMail = new MailParser();
		$checkMail = new ParseMail();
		$message = $checkMail->checkMail($message);
		//file_put_contents('/tmp/inb.log', "\nhash : ".$message['hash']."\n", FILE_APPEND);

		$talk = $messages->getTalkByHash($message['hash']);
		$messageid = $talk['id'];
		//file_put_contents('/tmp/inb.log', "\nmessageid : ".$messageid."\n", FILE_APPEND);

		$author = $this->getUserByExternalEmail($message['author']);
		//file_put_contents('/tmp/inb.log', "\nauthor : ".$author."\n", FILE_APPEND);

		//file_put_contents('/tmp/inb.log', "\nMessageSubscribers : ".$message['subscribers']."\t", FILE_APPEND);

		if (strpos($message['subscribers'], '-group')) { // If group conversation 
			//file_put_contents('/tmp/inb.log', "\nGroup conversation!\n", FILE_APPEND);
			$groupname = substr($message['subscribers'], 0, strpos($message['subscribers'], '-')); 
			//file_put_contents('/tmp/inb.log', "\ngroupname : ".$groupname."\n", FILE_APPEND);
			$subscribers = $this->getCorrectGroupId($groupname).'-group'; 
			//file_put_contents('/tmp/inb.log', "\nSubscribers : ".$subscribers."\n", FILE_APPEND);
		} 
		else { // If private conversation 
			$talksubscribers = explode(',', $talk['subscribers']);
			$talksubscribers[] = $talk['author'];
			if (is_array($message['subscribers'])) {
				$messagesubscribers = array();
				foreach($message['subscribers'] as $s => $subscriber) {
					$messagesubscribers[] = $this->getCorrectUserId($subscriber); 
				}
				array_merge($messagesubscribers, array_diff($talksubscribers, $messagesubscribers));
				unset($messagesubscribers[array_search($author, $messagesubscribers)]);
				$subscribers = implode(',', $messagesubscribers);
			} 
			else {
				if (is_string($message['subscribers'])) { 
					//file_put_contents('/tmp/inb.log', "\nMessageSubscribers is string!\n", FILE_APPEND);
					$subscribers = $this->getCorrectUserId($message['subscribers']);
					//file_put_contents('/tmp/inb.log', "\nuserid : ".$subscribers."\t", FILE_APPEND);
					if (!in_array($subscribers, $talksubscribers)) {
						$talksubscribers[] = $subscribers;
					}
					unset($talksubscribers[array_search($author, $talksubscribers)]);
					$subscribers = implode(',', $talksubscribers);
				}
			} 
		} 
		//file_put_contents('/tmp/inb.log', "\nSubscribers : ".$subscribers."\n", FILE_APPEND);

		$messagedata = array(
			'rid' => $messageid,
			'date' => date("Y-m-d h:i:s", strtotime($message['date'])),
			'title' => $message['title'],
			'text' => Helper::checkTxt($message['text']),
			//'attachements' => implode(',', $filesid),
			'author' => $author,
			'subscribers' => $subscribers,
			'hash' => $message['hash'],
			'status' => 0
		);

		if (!$usermessage = $usermessages->getMessageById($messageid)) {
			$usermessages->createStatus($messageid, $author);
			$usermessage = $usermessages->getMessageById($messageid);
		}
		if ($messageid && $author && $message['title'] && $message['subscribers']) {
			$saved = $messages->save($messagedata);
		}
		/* else {
			$error = "MessageID : ".$messageid."\n".
				"Author : ".$author."\n".
				"Title : ".$message['title']."\n".
				"Subscribers : ".$messagedata['subscribers']."\n";
		}
		if ($saved) {
			file_put_contents('/tmp/inb.log', "Message saved!\n", FILE_APPEND);
		}
		else {
			file_put_contents('/tmp/inb.log', "Message not saved! Database error! \n", FILE_APPEND);
		} */ 
		die;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param $id int
	 * @param $action string
	 * @return TemplateResponse
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function markMessage($id, $flag) {
		//echo "Hello";
		$usermessages = $this->getUserMessages($this->userId);
		$message = $usermessages->getMessageById($id);
		switch ($flag) {
			case 'read':
				$status = 1;
				if ($this->userId == $message['uid'] || !($message['status'] == $status)) {
					$message['status'] = $status;
					$usermessages->setStatus($message);
				}
				break;
			case 'unread':
				$status = 0;
				if ($this->userId == $message['author'] || !($message['status'] == $status)) {
					$message['status'] = $status;
					$usermessages->setStatus($message);
				}
				break;
			case 'finished':
				echo "Finished";
				 $status = 3;
				$messages = $this->connect->messages();
				$message = $messages->getById($id)[0];
				if ($this->userId == $message['author'] || $this->isUserAdmin()) {
					$messages->setStatus($id, $status);
				}
				break;
			default: //unread
				if ($this->userId == $message['uid'] || !($message['status'] == 0)) {
					$message['status'] = 0;
					$usermessages->setStatus($message);
				}
				break;
		}

		$params = array(
			'user' => $this->userId,
			'messages' => $usermessages->getBySubscriber($this->userId),
			'menu' => 'all',
			'mode' => 'all'
		);

		return new TemplateResponse($this->appName, 'talk', $params);  // templates/talk.php
	}


	/**
	 * Get an object of Messages
	 *
	 */
	public function getMessages() {
		$messages = $this->connect->messages();
		return $messages;
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
	 * @param array $message
	 * @param array $subscribers
	 * Send the message to each user
	 * in subscribers list
	 */
	public function sendMessage($message, $subscribers, $from = '', $messagedata = NULL) {
		$um = $this->connect->userMessage();
		$users = $this->connect->users();
		//$isgroup = $users->isGroupSelected($subscribers);
		//$groups = $messagedata['groupsid'];
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
		//print_r($messagedata);
		if (!empty($messagedata)) {
			foreach ($subscribers as $s => $subscriber) {
				$sent = Helper::messageSend($subscriber, $from, $messagedata, $this->getProjectName());
            }
		}
	}

	/**
	 * Get list of users to build
	 * an array of subscribers
	 */
	public function getUsers() {
		$users = $this->connect->users();
		//$userlist = $users->getAll();
		$groupusers = $users->getGroupsUsersList();
		$ungroupusers = $users->getUngroupUserList();
		$userlist = array_merge($groupusers, $ungroupusers);
		return $userlist;
	}

	/**
	 * Check if User belongs
	 * Admins group
	 */
	public function isUserAdmin($user = NULL) {
		if (!$user) {
			$user = $this->userId;
		}
		$users = $this->connect->users();
		$userlist = $users->getByGroup($user, 'admin');
		if (count($userlist) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Simply method that posts back the payload of the request
	 * @NoAdminRequired
	 */
	public function doEcho($echo) {
		return new DataResponse(['echo' => $echo]);
	}

	public function getProjectName() {
		return $this->projectname;
	}

	public function getUserByExternalEmail($email) {
		$users = $this->connect->users();
		$userid = $users->getByExternalEmail($email);
		if ($userid && !empty($userid)) {
			return $userid;
		}
		else {
			return false;
		}
	}

	public function getCorrectUserId($user) {
		$users = $this->connect->users();
		$userid = $users->getCaseInsensitiveId($user);
		if ($userid && !empty($userid)) {
			return $userid;
		}
		else {
			return false;
		}
	}

	public function getCorrectGroupId($user) {
		$users = $this->connect->users();
		$groupid = $users->getCaseInsensitiveGroupId($user);
		if ($groupid && !empty($groupid)) {
			return $groupid;
		}
		else {
			return false;
		}
	}

	private function checkUserDirPermissions() {
		$cwd = getcwd();
		$fileperms = fileperms($cwd.'/data/'.$this->userId);
		return sprintf('%o', $fileperms);
	}

	private function setUserDirPermissions($permission, $current = NULL) {
		if (!$current) {
			$current = $this->checkUserDirPermissions();
		}
		$current = substr($current, 2, 2);
		$cwd = getcwd();
		try {
			chmod($cwd . '/data/' . $this->userId, octdec($current.''.$permission));
			return 'success';
		}
		catch (\Exception $e) {
			$error = 'You have no rights to upload files!';
			return $error;
		}
	}

	private function checkUserEmailAlias($user) {
		return false;
	}

	private function setUserEmailAlias($user) {
		return false;
	}
}