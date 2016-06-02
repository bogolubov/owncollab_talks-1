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

class Answers {

	private $connect;
	private $Messages;
	private $Users;

	public $answerId;
	public $talkId;
	private $talkAuthor = '';
	public $date;
	public $title;
	public $text;
	public $author;
	public $subscribers;
	public $subscriberGroups;
	public $subscriberPersons;
	public $subscriberToSave;
	public $subscriberToSend;
	public $hash;
	public $status;
	public $forSave;
	public $forSend;
	public $projectName;

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

	public function talkAnswerList($id) {
		$this->talkId = $id;
		$answerlist = $this->Messages->getByParent($this->talkId);
		return $answerlist;
	}

	public function getById($id) {
		$this->Messages->getById();
	}

	public function getIdByHash($hash) {
		return $this->Messages->getTalkByHash($hash);
	}

	/**
	 * Devide all subscribers to Groups and Personal users
	 */
	public function devideSubscribers() {
		if (!is_array($this->subscribers)) {
			$this->subscribers = $this->subscribersToArray();
		}
		if ($this->isreply) {
			$this->subscribers = $this->swapSubscribers($this->subscribers, $this->talkAuthor, $this->author);
		}
		foreach ($this->subscribers as $s => $subscriber) {
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
		if ($this->isreply) {
			$this->subscriberToSave = $this->swapSubscribers($this->subscriberToSave, $this->talkAuthor, $this->author);
		}
		$this->subscriberToSave = $this->removeFromList($this->subscriberToSave, $this->author);
		$messagedata = array(
			'rid' => $this->talkId,
			'date' => $this->date,
			'title' => $this->title,
			'text' => $this->text,
			'attachements' => '',
			'author' => $this->author,
			'subscribers' => is_array($this->subscriberToSave) ? implode(',', $this->subscriberToSave) : $this->subscriberToSave,
			'hash' => $this->hash,
			'status' => 0
		); 
		
		$this->forSave = $messagedata; 
		return $messagedata;
	}

	/**
	 * Prepare lists of subscribers
	 */
	public function prepareSubscribers() {
		//file_put_contents('/tmp/inb.log', "prepareSubscribers > author : ".$this->author."\n", FILE_APPEND);
		if (in_array($this->author, $this->subscriberPersons)) { 
		  $this->subscriberPersons = $this->removeFromList($this->subscriberPersons, $this->author); 
		} 
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
					$user = $this->users->getUserDetails($groupuser['uid']);
					if (!($user == $this->author)) {
						$groupusers[$groupuser['uid']] = $user;
					}
				}
				$this->subscriberToSend[$group] = ['groupid' => $group, 'grouppref' => $group.'-group', 'groupusers' => $groupusers];
			}
		}

		foreach ($this->subscriberPersons as $sp => $subscriber) {
			$this->subscriberToSend['ungroupped']['groupusers'][$subscriber] = $this->Users->getUserDetails($subscriber);
		}
		//file_put_contents('/tmp/inb.log', "prepareSubscribers > subscriberPersons : \n".print_r($this->subscriberPersons, true)."\n", FILE_APPEND);
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
			'attachements' => '',
			'author' => $this->author,
			'subscribers' => $this->subscriberToSend,
			'hash' => $this->hash,
			'status' => 0
		];

		$this->forSend = ['answerid' => $this->answerId, 'emails' => $emails, 'data'=> $messagedata];
	}

	private function prepareEmailAddresses() {
		$authorname = $this->Users->getUserDetails($this->author);
		$addresses = array();
		foreach ($this->subscriberToSend as $ss => $item) {
			$groupname = $ss;
			if ($groupname == 'ungroupped') {
				$fromname = !empty($authorname['settings'][0]['name']) ? $authorname['settings'][0]['name'] : $this->author;
				$fromaddress = Helper::getUserAlias($this->author, $this->projectName);
				$replyto = Helper::getUserAlias($this->author, $this->projectName, $this->hash);
			}
			else {
				$fromname = $groupname;
				$fromaddress = Helper::getGroupAlias($groupname, $this->projectName);
				$replyto = Helper::getGroupAlias($groupname, $this->projectName, $this->hash);
			}
			foreach ($item['groupusers'] as $gu => $user) {
				if (!empty($user['settings'][0])) {
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
		if (!$this->forSave) { 
			$this->forSave = $this->prepareForSave(); 
		} 
		$answerId = $this->Messages->save($this->forSave);
		$this->answerId = $answerId;
		return $answerId;
	}

	/**
	 * Send the message to all subscribers
	 */
	public function send() {
		return $this->prepareForSend();
	}

	/**
	 * Save files attached to email massage
	 */
	public function saveFiles($files) {
		foreach ($files as $file) {
			if (!empty($file['contents'])) {
				if (!empty($file['contentType']) && $file['encoding'] == 'base64') {
					$fileToUpload = $this->saveTmpFile($file['contents'], $file['filename']);
				}
			} 
		}
	}

	private function saveTmpFile($contents, $filename) {
		$path = realpath(dirname(__DIR__));
		$dir = $path.'/tmp';
		$ifp = fopen($dir.'/'.$filename.'+'.$this->answerId, "wb");
		fwrite($ifp, $contents);
		fclose($ifp);
		return $dir.'/'.$filename;
	}

	/**
	 * Share selected files with selected users
	 */
	public function shareFiles() {
		$this->prepareUsersForShare();
		$files = array();
		foreach ($this->files as $id) {
			$files[] = $file = $this->files->getById($id)[0];
			$fileOwner = \OC\Files\Filesystem::getOwner($file['path']);
			$sharetype = $file['mimetype'] == 2 ? 'folder' : 'file';
			$sharedWith = \OCP\Share::getUsersItemShared($sharetype, $file['fileid'], $fileOwner, false, true);
			foreach ($this->subscriberToShare as $userid) {
				if (
					isset($file['fileid']) && 
					is_array($file) && 
					!in_array($userid, $sharedWith) && 
					!($userid == $this->author) && 
					($fileOwner == $this->author || $file['permissions'] >= 16)
				) {
					\OCP\Share::shareItem($sharetype, $file['fileid'], \OCP\Share::SHARE_TYPE_USER, $userid, 1);
				}
			}
		}
		$this->forSend['messagedata']['attachlinks'] = Helper::makeAttachLinks($this->files, $files);
	}

	private function prepareUsersForShare() {
		$users = array();
		foreach ($this->subscriberToSend as $ss => $item) {
			$users[] = $item['groupusers']['settings'][0]['name'];
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

	private function removeFromList($list, $item) {
		$index = array_search($item, $list);
		unset($list[$index]);
		return $list;
	}

	/**
	 * Get all users from group
	 * @param $group string
	 * @return array
	 */
	private function getGroupUsers($groupid) {
		return $this->Users->getUsersFromGroup($groupid);
	}

	private function swapSubscribers($subscribers, $prevAuthor, $newAuthor) {
		if (in_array($newAuthor, $subscribers)) {
			$pos = array_search($newAuthor, $subscribers);
			unset($subscribers[$pos]);
		}
		$subscribers[] = $prevAuthor;
		return $subscribers;
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

	public function setAuthor($author, $talkAuthor = '') {
		$this->author = $author;
		if (!empty($talkAuthor)) {
			$this->talkAuthor = $talkAuthor;
		}
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