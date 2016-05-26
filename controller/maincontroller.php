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

			$talk = $this->connect->talks();
			$talk->getTalk($talks[0]['messageid']);
			$answers = $this->connect->answers();
			$firsttalk = $answers->talkAnswerList($talk->talkId);

			//$firsttalk = $messages->getByParent($talks[0]['messageid'], 'date ASC');
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
		$users = $this->connect->users();

		$message = $messages->getByReply($id);
		//$message = $messages->getById($id)[0];
		$usermessages = $this->getUserMessages($this->userId);
		if (!$usermessage = $usermessages->getMessageById($message['mid'])) {
			$usermessages->createStatus($message['mid'], $this->userId);
			$usermessage = $usermessages->getMessageById($message['mid']);
		}
		if (!$userstatus = $usermessages->getUserStatus($message['mid'])) {
			$usermessages->createStatus($message['mid'], $this->userId);
			$userstatus = $usermessages->getUserStatus($message['mid']);
		}
		$subscribers = $this->getUsers();
		//$helper = new Helper();
		$allusers = $users->getAllUsers($message['subscribers']);
		if ($messages->canRead($message, $this->userId, $allusers)) {
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
	public function saveTalk() {
		$talks = $this->connect->talks();

		if ($_POST['replyid']) {
			$talks->setTalkId($_POST['replyid']);
			$talks->setReplyId($_POST['replyid']);
			$talks->setReply(true);
			$messages = $this->connect->messages();
			$thetalk = $messages->getById($_POST['replyid'])[0];
			$hash = $thetalk['hash'];
		}
		$talks->setTitle($_POST['title']);
		$talks->setText(Helper::checkTxt($_POST['message-body']));
		$talks->setAuthor($this->userId);
		if (isset($_POST['users']) && !empty($_POST['users'])) {
			$talks->setSubscriberPersons($_POST['users']);
		}
		if (isset($_POST['groups']) && !empty($_POST['groups'])) {
			$talks->setSubscriberGroups($_POST['groups']);
		}
		$talks->setHash(isset($_POST['talkhash']) && !empty($_POST['talkhash']) ? $_POST['talkhash'] : isset($hash) && !empty($hash) ? $hash : md5(date("Y-m-d h:i:s").''.$_POST['title']));
		$talks->setProjectName($this->projectname);

		//Prepare subscribers lists
		$talks->prepareSubscribers();

		//Share files
		if (!empty($_POST['select-files'])) {
			$talks->selectedFiles($_POST['select-files']);
		}
		if (!empty($_POST['upload-files'])) {
			$talks->uploadedFiles($_POST['upload-files']);
		}
		if (!empty($talks->files)) {
			$talks->shareFiles();
		}

		//Prepare data for saving
		$talks->prepareForSave();
		$talkid = $talks->save();
		foreach ($talks->subscriberToSave as $s => $item) {
			$this->setUserMessageStatus($item, $talks->talkId);
		}
		$this->setUserMessageStatus($talks->author, $talks->talkId);

		$talks->prepareForSend();
		foreach ($talks->forSend['emails'] as $e => $email) {
			//$this->setUserMessageStatus($email['name'], $talks->forSend['talkid']);

			if (!empty($talks->forSend['data'])) {
				$sent = Helper::messageSend($email, $talks->forSend['data']);
			}
		}

		$canwrite = true; //TODO: Створити перевірку на право починати бесіди

		if ($canwrite) {
			header('Location: /index.php/apps/'.$this->appName.'/all');
			exit();
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
	public function saveDirectAnswer() {
		$answers = $this->connect->answers();

		$answers->setTalkId($_POST['replyid']);
		$answers->setTitle($_POST['title']);
		$answers->setText(Helper::checkTxt($_POST['message-body']));
		$answers->setAuthor($this->userId);
		$answers->setSubscriberPersons($_POST['users']);
		$answers->setSubscriberGroups($_POST['groups']);
		$answers->setHash(isset($_POST['talkhash']) && !empty($_POST['talkhash']) ? $_POST['talkhash'] : md5(date("Y-m-d h:i:s").''.$_POST['title']));
		$answers->setProjectName($this->projectname);

		//Prepare subscribers lists
		$answers->prepareSubscribers();

		//Prepare data for saving
		$answers->prepareForSave();
		$answers->save();

		$forSend = $answers->send();

		foreach ($forSend['emails'] as $e => $email) {
			$this->setUserMessageStatus($email['name'], $forSend['answerid']);

			if (!empty($messagedata)) {
				$sent = Helper::messageSend($email, $forSend['data']);
			}
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	public function saveEmailAnswer() {
		//file_put_contents('/tmp/inb.log', "\nsaveEmailAnswer\n", FILE_APPEND);
		//die();
		echo "saveEmailAnswer";
		$talk = $this->connect->talks();
		$answers = $this->connect->answers();

		$answers->setAuthor('olexiy');
		$answers->saveFiles(array(['contentType' => 'image/png', 'encoding' => 'base64', 'filename' => "banana.png", 'contents' => 'iVBORw0KGgoAAAANSUhEUgAAAZAAAAELCAYAAAD3HtBMAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ']));

		/* $author = $this->getUserByExternalEmail($_POST['from']);
		file_put_contents('/tmp/inb.log', "author : ".$author."\n", FILE_APPEND);

		$talk->getByHash($_POST['hash']);
		file_put_contents('/tmp/inb.log', "talkId : ".$talk->talkId."\n", FILE_APPEND);

		$answers->setTalkId($talk->talkId);
		$answers->setReply(true);
		$answers->setTitle(Helper::checkTxt($_POST['subject']));
		$answers->setText(Helper::checkTxt($_POST['contents']));
		$answers->setAuthor($author, $talk->author);
		$answers->setSubscribers($talk->subscribers);
		$answers->setHash($talk->hash);
		$answers->setProjectName($this->getProjectName()); */

		//Prepare subscribers lists
		/* $answers->devideSubscribers();
		$answers->prepareSubscribers();

		//Share files
		if (!empty($_POST['attachments'])) {
			$answers->saveFiles($_POST['attachments']);
			$answers->shareFiles();
		}

		//Prepare data for saving
		$answers->prepareForSave();
		file_put_contents('/tmp/inb.log', "subscribersToSave : \n".print_r($answers->subscriberToSave, true)."\n", FILE_APPEND);
		$answerId = $answers->save();

		foreach ($answers->subscriberToSave as $s => $item) {
			$this->setUserMessageStatus($item, $answerId);
		} */
		//Send replies to all subscribers
		/* $answers->prepareForSend();
		foreach ($answers->forSend['emails'] as $e => $email) {
			$this->setUserMessageStatus($email['name'], $answers->forSend['talkid']);

			if (!empty($answers->forSend['data'])) {
				$sent = Helper::messageSend($email, $answers->forSend['data']);
			}
		} */
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
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	//TODO: Використовувати метод з застосуванням засобів безпеки
	public function savemail() {

		echo "Hello";
		print_r($_POST);
		/* $messages = $this->connect->messages();
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
		} */
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

	private function checkUserEmailAlias($user) {
		return false;
	}

	private function setUserEmailAlias($user) {
		return false;
	}
}