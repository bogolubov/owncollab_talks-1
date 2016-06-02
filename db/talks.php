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

namespace OCA\Owncollab_Talks\Db;

use OCA\Owncollab_Talks\Helper;

class Talks {

	private $connect;
	private $Messages;
	private $Users;
	private $Files;

	public $talkId;
	public $replyId;
	private $isreply;

	public $date;
	public $title;
	public $text;
	public $attachmentIDs;
	public $author;
	public $subscribers;
	public $subscriberGroups;
	public $subscriberPersons;
	public $subscriberToSave;
	public $subscriberToSend;
	public $subscriberToShare;
	public $hash;
	public $status;
	public $projectName;
	private $forSaveData;
	public $forSend;
	public $files;
	private $fileLinks;

	/**
	 * MainController constructor.
	 * @param $talkId
	 */
	public function __construct($connect, $messages){
		$this->connect = $connect;
		$this->Messages = $messages;
		$this->Users = $this->connect->users();
		$this->Files = $this->connect->files();
	}

	public function getTalk($id) {
		$this->talkId = $id;
		//$messages = $this->connect->messages();
		$talk = $this->Messages->getById($this->talkId);

		$this->date = $talk[0]['date'];
		$this->title = $talk[0]['title'];
		$this->text = $talk[0]['text'];
		$this->attachemntIDs = $talk[0]['attachments'];
		$this->author = $talk[0]['author'];
		$this->subscribers = $talk[0]['subscribers'];
		$this->hash = $talk[0]['hash'];
		$this->status = $talk[0]['status'];
	}

	public function getByHash($hash) {
		file_put_contents('/tmp/inb.log', "hash : ".$hash."\n", FILE_APPEND);
		$talk = $this->Messages->getTalkByHash($hash);

		//file_put_contents('/tmp/inb.log', "talk : ".print_r($talk, true)."\n", FILE_APPEND);

		$this->talkId = $talk['id'];
		$this->date = $talk['date'];
		$this->title = $talk['title'];
		$this->text = $talk['text'];
		$this->attachemntIDs = $talk['attachments'];
		$this->author = $talk['author'];
		$this->subscribers = $talk['subscribers'];
		$this->hash = $talk['hash'];
		$this->status = $talk['status'];
	}

	/**
	 * Get all users from group
	 * @param $group string
	 * @return array
	 */
	private function getGroupUsers($groupid) {
		return $this->Users->getUsersFromGroup($groupid);
	}

	/**
	 * Devide all subscribers to Groups and Personal users
	 */
	private function devideSubscribers() {
		foreach ($this->subscribersToArray() as $s => $subscriber) {
			if (strpos($subscriber, '-group')) { // If group conversation
				$groupname = substr($subscriber, 0, strpos($subscriber, '-'));
				$this->subscriberGroups[] = $this->getCorrectGroupId($groupname).'-group';
			}
			else {
				$this->subscriberPersons[] = $this->getCorrectUserId($subscriber);
			}
		}
	}

	private function attachmentsToArray() {
		return explode(',', $this->attachmentIDs);
	}

	private function subscribersToArray() {
		return explode(',', $this->subscribers);
	}

	private function getCorrectUserId($user) {
		$userid = $this->Users->getCaseInsensitiveId($user);
		if ($userid && !empty($userid)) {
			return $userid;
		}
		else {
			return false;
		}
	}

	private function getCorrectGroupId($user) {
		//$users = $this->Users();
		$groupid = $this->Users->getCaseInsensitiveGroupId($user);
		if ($groupid && !empty($groupid)) {
			return $groupid;
		}
		else {
			return false;
		}
	}

	public function setSubscriberPersons($subscriberList) {
		if (is_array($subscriberList)) {
			$this->subscriberPersons = $subscriberList;
		}
		else {
			$this->subscriberPersons = $this->subscribersToArray($subscriberList);
		}
	}

	public function setSubscriberGroups($subscriberList) {
		if (!is_array($subscriberList)) {
			$subscriberList = $this->subscribersToArray($subscriberList);
		}
		foreach ($subscriberList as $sl => $subscriber) {
			if (strpos($subscriber, '-group')) {
				$this->subscriberGroups[] = $subscriber;
			}
			else {
				$this->subscriberGroups[] = $subscriber.'-group';
			}
		}
	}

	/**
	 * Prepare data for saving
	 */
	public function prepareForSave() {
		if (!$this->subscriberGroups && $this->subscriberPersons) {
			$this->subscriberToSave = $this->subscriberPersons;
		}
		elseif (!$this->subscriberPersons && $this->subscriberGroups) {
			$this->subscriberToSave = $this->subscriberGroups;
		}
		else {
			$this->subscriberToSave = array_merge($this->subscriberGroups, $this->subscriberPersons);
		}
//		if ($this->isreply) {
//			$this->swingSubscribers($this->subscriberToSave, $this->author);
//		}
		$this->forSaveData = array(
			'rid' => isset($this->replyId) && !($this->replyId == 0) ? $this->replyId : 0,
			'date' => $this->date,
			'title' => $this->title,
			'text' => $this->text,
			'attachements' => is_array($this->files) ? implode(',', $this->files) : $this->files,
			'author' => $this->author,
			'subscribers' => is_array($this->subscriberToSave) ? implode(',', $this->subscriberToSave) : $this->subscriberToSave,
			'hash' => $this->hash,
			'status' => 0
		);
	}

	/**
	 * Prepare lists of subscribers
	 */
	public function prepareSubscribers() {
		foreach ($this->subscriberGroups as $sg => $group) {
			if (!empty($group)) {
				$groupusers = array();
				if (strpos($group, '-group')) { // If group conversation
					$group = substr($group, 0, strpos($group, '-'));
				}
				foreach ($this->getGroupUsers($group) as $gu => $groupuser) {
					if (in_array($groupuser, $this->subscriberPersons)) {
						$this->subscriberPersons = $this->removeFromList($this->subscriberPersons, $groupuser);
					}
					$user = $this->Users->getUserDetails($groupuser['uid']);
					if (!($user == $this->author)) {
						$groupusers[$groupuser['uid']] = $user;
					}
				}
				$this->subscriberToSend[$group] = ['groupid' => $group, 'grouppref' => $group . '-group', 'groupusers' => $groupusers];
			}
		}

		foreach ($this->subscriberPersons as $sp => $subscriber) { //TODO check this!
			if (!($subscriber == $this->author)) {
				$this->subscriberToSend['ungroupped']['groupusers'][$subscriber] = $this->Users->getUserDetails($subscriber);
			}
		}
	}

	/**
	 * Prepare data for sending
	 */
	public function prepareForSend() {
		$emails = $this->prepareEmailAddresses();
		$messagedata = [
			'rid' => $this->talkId,
			'date' => $this->date,
			'title' => $this->title,
			'text' => $this->text,
			'attachements' => serialize($this->fileLinks),
			'author' => $this->author,
			'subscribers' => $this->subscriberToSend,
			'hash' => $this->hash,
			'status' => 0
		];

		$this->forSend = ['talkid' => $this->talkIdId, 'emails' => $emails, 'data'=> $messagedata];
	}

	private function prepareEmailAddresses() {
		$authorname = $this->Users->getUserDetails($this->author);
		$addresses = array();
		foreach ($this->subscriberToSend as $ss => $item) {
			$groupname = $ss;
			if ($groupname == 'ungroupped') {
				$fromname = $authorname['settings'][0]['name'];
				$fromaddress = Helper::getUserAlias($this->author, $this->projectName);
				$replyto = Helper::getUserAlias($this->author, $this->projectName, $this->hash);
			}
			else {
				$fromname = $groupname;
				$fromaddress = Helper::getGroupAlias($groupname, $this->projectName);
				$replyto = Helper::getGroupAlias($groupname, $this->projectName, $this->hash);
			}
			foreach ($item['groupusers'] as $gu => $user) {
				if (!empty($user['settings'][0]['email'])) {
					$addresses[] = [
						'name' => !empty($user['settings'][0]['name']) ? $user['settings'][0]['name'] : $gu,
						'email' => $user['settings'][0]['email'],
						'groupname' => $groupname,
						'fromname' => $fromname,
                        'fromaddress' => $fromaddress,
                        'replyto' => $replyto
                    ];
				}
			}
		}

		return $addresses;
	}

	/**
	 * Save the message to db
	 */
	public function save() {
		$this->talkId = $this->Messages->save($this->forSaveData);
	}

	/**
	 * Send the message to all subscribers
	 */
	public function send() {
		return $this->prepareForSend();
	}

	/**
	 * Share selected files with selected users
	 */
	public function shareFiles() {
		$this->prepareUsersForShare();
		$files = array();
		foreach ($this->files as $id) {
			$file = $this->Files->getById($id)[0];
			$fileOwner = \OC\Files\Filesystem::getOwner($file['path']);
			$sharetype = $file['mimetype'] == 2 ? 'folder' : 'file';
			$sharedWith = \OCP\Share::getUsersItemShared($sharetype, $file['fileid'], $fileOwner, false, true);
			$isenabled = \OCP\Share::isEnabled(); 
			$isallowed = \OCP\Share::isResharingAllowed(); 
			foreach ($this->subscriberToShare as $userid) {
				if (
					isset($file['fileid']) &&
					is_array($file) &&
					!in_array($userid, $sharedWith) &&
					!($userid == $this->author) &&
					($fileOwner == $this->author || $file['permissions'] >= 16) && 
					$isenabled && 
					$isallowed 
					) {
					//try {
						\OCP\Share::shareItem($sharetype, $file['fileid'], \OCP\Share::SHARE_TYPE_USER, $userid, 1);
						$files[] = $file['fileid'];
					//}
					//catch (\Exception $e) {
					//	echo $e->getMessage();
					//}
				}
			}
		}
		$this->forSaveData['attachements'] = $files;
		$this->fileLinks = Helper::makeAttachLinks($files, $this->Files);
		//print_r($this->fileLinks);
		//file_put_contents('/tmp/inb.log', "\n\nfileLinks : "print_r($this->fileLinks, true)."\n", FILE_APPEND);

		/* foreach ($_POST['select-files'] as $id => $on) {
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
		} */
	}

	private function prepareUsersForShare() {
		$users = array();
		foreach ($this->subscriberToSend as $ss => $item) {
			foreach ($item['groupusers'] as $u => $user) {
				//$users[] = $user['settings'][0]['name'];
				$users[] = $u;
			}
		}
		$this->subscriberToShare = array_unique($users);
	}

	public function uploadedFiles($files) {
		foreach ($files as $id) {
			$this->files[] = $id;
		}
	}

	public function selectedFiles($files) {
		foreach ($files as $id => $on) {
			if ($on == 'on') {
				$this->files[] = $id;
			}
		}
	}

	public function setTalkId($id) {
		$this->talkId = $id;
	}

	public function setDate($date = NULL) {
		//$date = date_create(date("Y-m-d H:i:s"), timezone_open('UTC')); 
		//$this->date = isset($date) ? $date : date_format($date, 'Y-m-d H:i:s');
		$this->date = isset($date) ? $date : date('Y-m-d H:i:s');
	}

	public function setReply($reply) {
		$this->isreply = $reply;
	}

	public function setReplyId($id) {
		$this->replyId = $id;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function setSubscribers($subscriberList) {
		$this->subscribers = $subscriberList;
	}

	public function setHash($hash) {
		$this->hash = $hash;
	}

	public function setProjectName($name) {
		$this->projectName = $name;
	}
}