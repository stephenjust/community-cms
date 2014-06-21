<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class Contact {
	private $mId;
	private $mName;
	private $mPhone;
	private $mEmail;
	private $mAddress;
	private $mTitle;
	private $mUser;
	private $mUserId;
	
	/**
	 * Load a contact record
	 * @global db $db
	 * @param integer $id
	 * @throws ContactException
	 */
	public function __construct($id) {
		global $db;

		$id = (int)$id;
		$query = 'SELECT `c`.*, `u`.`username`
			FROM `'.CONTACTS_TABLE.'` `c`
			LEFT JOIN `'.USER_TABLE.'` `u`
			ON `c`.`user_id` = `u`.`id`
			WHERE `c`.`id` = '.$id.' LIMIT 1';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new ContactException('Error reading contact information.');
		if ($db->sql_num_rows($handle) != 1)
			throw new ContactException('Contact not found.');
		$contact = $db->sql_fetch_assoc($handle);

		$this->mId = $contact['id'];
		$this->mName = $contact['name'];
		$this->mPhone = $contact['phone'];
		$this->mEmail = $contact['email'];
		$this->mAddress = $contact['address'];
		$this->mUser = $contact['username'];
		$this->mUserId = $contact['user_id'];
		$this->mTitle = $contact['title'];
	}

	/**
	 * Add contact to a contact list
	 * @global acl $acl
	 * @global db $db
	 * @param integer $list_id
	 * @throws ContactException
	 */
	public function addToList($list_id) {
		global $acl;
		global $db;

		// Check permissions
		if (!$acl->check_permission('contacts_edit_lists'))
			throw new ContactException('You are not allowed to edit contact lists.');

		// Check for invalid parameters
		if (!is_numeric($list_id))
			throw new ContactException('Invalid contact list.');

		$check_list_query = 'SELECT `page`.`id`, `page`.`title`
		FROM `' . PAGE_TABLE . '` `page`, `' . PAGE_TYPE_TABLE . '` `pt`
		WHERE `page`.`type` = `pt`.`id`
		AND `pt`.`name` = \'Contacts\'
		AND `page`.`id` = ' . $list_id;
		$check_list_handle = $db->sql_query($check_list_query);
		if ($db->error[$check_list_handle] === 1)
			throw new ContactException('Error loading contact list.');
		if ($db->sql_num_rows($check_list_handle) === 0)
			throw new ContactException('Contact list does not exist.');
		$check_list = $db->sql_fetch_assoc($check_list_handle);

		$check_dupe_query = 'SELECT `id` FROM `'.CONTENT_TABLE.'`
		WHERE `ref_id` = '.$this->mId.'
		AND `page_id` = '.$list_id;
		$check_dupe_handle = $db->sql_query($check_dupe_query);
		if ($db->error[$check_dupe_handle] === 1)
			throw new ContactException('Error checking for duplicate entries.');
		if ($db->sql_num_rows($check_dupe_handle) !== 0)
			throw new ContactException('Contact is already on this list.');

		// Add contact to list
		$insert_query = 'INSERT INTO `'.CONTENT_TABLE.'`
			(`page_id`,`ref_type`,`ref_id`) VALUES
			('.$list_id.',(
				SELECT `id`
				FROM `'.PAGE_TYPE_TABLE.'`
				WHERE `name` = \'Contacts\'
			), '.$this->mId.')';
		$insert_handle = $db->sql_query($insert_query);
		if ($db->error[$insert_handle] === 1)
			throw new ContactException('Error adding contact to list.');
		
		Log::addMessage('Added '.$this->mName.' to contact list \''.$check_list['title'].'\'');
	}
	
	/**
	 * Create a contact record
	 * @global acl $acl
	 * @global db $db
	 * @param string $name
	 * @param string $title
	 * @param string $phone
	 * @param string $address
	 * @param string $email
	 * @param string $username
	 * @return \Contact
	 * @throws ContactException
	 */
	public static function create($name, $title, $phone, $address, $email, $username) {
		global $acl;
		global $db;

		if (!$acl->check_permission('contacts_create'))
			throw new ContactException('You are not allowed to create contact records.');

		// Sanitize inputs
		$name = $db->sql_escape_string($name);
		$title = $db->sql_escape_string($title);
		$address = $db->sql_escape_string($address);
		$email = $db->sql_escape_string($email);
		$username = $db->sql_escape_string($username);

		// Format phone number for storage
		if ($phone != "") {
			$phone = preg_replace('[^0-9]', NULL, $phone);
			if (!is_numeric($phone))
				throw new ContactException('Invalid telephone number.');
		}

		// Verify email address
		if ($email != "") {
			if (!preg_match('/^[a-z0-9_\-\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i', $email))
				throw new ContactException('Invalid email address.');
		}

		// Verify username and get user ID
		if ($username != '') {
			$username_query = 'SELECT `id`
			FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'';
			$username_handle = $db->sql_query($username_query);
			if ($db->error[$username_handle] === 1)
				throw new ContactException('An error occurred while looking up a username record.');
			if ($db->sql_num_rows($username_handle) == 0) {
				$uid = 0;
			} else {
				$uname = $db->sql_fetch_assoc($username_handle);
				$uid = $uname['id'];
			}
		} else {
			$uid = 0;
		}

		// Create contact
		$query = 'INSERT INTO `'.CONTACTS_TABLE."`
		(`name`,`user_id`,`title`,`phone`,`email`,`address`)
		VALUES
		('$name',$uid,'$title',$phone,'$email','$address')";
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new ContactException('An error occurred while creating the contact record.');

		Log::addMessage('New contact \''.stripslashes($name).'\'');
		
		return new Contact($db->sql_insert_id(CONTACTS_TABLE, 'id'));
	}
	
	/**
	 * Delete the open contact entry
	 * @global acl $acl Permission object
	 * @global db $db Database object
	 * @throws ContactException
	 */
	public function delete() {
		global $acl;
		global $db;

		if (!$acl->check_permission('contacts_delete'))
			throw new ContactException('You are not allowed to delete contacts.');

		if (!$this->mId)
			throw new ContactException('Invalid contact ID.');

		// Delete 'content' records
		$del_cnt_query = 'DELETE FROM `'.CONTENT_TABLE.'`
			WHERE `ref_id` = ' . $this->mId . '
			AND `ref_type` = (
				SELECT `id` 
				FROM `'.PAGE_TYPE_TABLE.'`
				WHERE `name` = \'Contacts\'
			)';
		$del_cnt_handle = $db->sql_query($del_cnt_query);
		if ($db->error[$del_cnt_handle] === 1)
			throw new ContactException('Error deleting content record.');

		// Delete record
		$delete_query = 'DELETE FROM `'.CONTACTS_TABLE.'`
		   WHERE `id` = '.$this->mId;
		$delete_contact = $db->sql_query($delete_query);
		if ($db->error[$delete_contact] === 1)
			throw new ContactException('Error deleting contact record.');

		Log::addMessage('Deleted contact \''.$this->mName.'\'');
		$this->mId = 0;
	}

	/**
	 * Remove contact from list
	 * @global acl $acl
	 * @global db $db
	 * @param integer $page_id
	 * @throws ContactException
	 */
	function deleteFromList($page_id) {
		global $acl;
		global $db;

		if (!$acl->check_permission('contacts_edit_lists'))
			throw new ContactException('You are not allowed to edit contact lists.');
		if (!is_numeric($page_id))
			throw new ContactException('Invalid content ID.');

		$delete_query = 'DELETE FROM `'.CONTENT_TABLE.'`
			WHERE `ref_id` = '.$this->mId.'
			AND `page_id` = '.$page_id;
		$delete_handle = $db->sql_query($delete_query);
		if ($db->error[$delete_handle] === 1)
			throw new ContactException('Error removing contact from list.');
		
		Log::addMessage('Removed '.$this->mName.' from contact list.');
	}
	
	/**
	 * Edit contact record
	 * @global acl $acl
	 * @global db $db
	 * @param string $name
	 * @param string $title
	 * @param string $phone
	 * @param string $address
	 * @param string $email
	 * @param string $username
	 * @throws ContactException
	 */
	public function edit($name, $title, $phone, $address, $email, $username) {
		global $acl;
		global $db;

		if (!$acl->check_permission('contacts_edit'))
			throw new ContactException('You are not allowed to edit contact records.');

		// Sanitize inputs
		$name = $db->sql_escape_string($name);
		$title = $db->sql_escape_string($title);
		$address = $db->sql_escape_string($address);
		$email = $db->sql_escape_string($email);
		$username = $db->sql_escape_string($username);

		// Format phone number for storage
		if ($phone != "") {
			$phone = preg_replace('[^0-9]', NULL, $phone);
			if (!is_numeric($phone))
				throw new ContactException('Invalid telephone number.');
		}

		// Verify email address
		if ($email != "") {
			if (!preg_match('/^[a-z0-9_\-\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i', $email))
				throw new ContactException('Invalid email address.');
		}

		// Verify username and get user ID
		if ($username != '') {
			$username_query = 'SELECT `id`
			FROM `' . USER_TABLE . '`
			WHERE `username` = \'' . $username . '\'';
			$username_handle = $db->sql_query($username_query);
			if ($db->error[$username_handle] === 1)
				throw new ContactException('Error looking up username record.');
			if ($db->sql_num_rows($username_handle) == 0) {
				$uid = 0;
			} else {
				$uname = $db->sql_fetch_assoc($username_handle);
				$uid = $uname['id'];
			}
		} else {
			$uid = 0;
		}

		// Update contact record
		$query = 'UPDATE `' . CONTACTS_TABLE . "`
		SET `name`='$name',`user_id`=$uid,`title`='$title',
		`phone`=$phone,`email`='$email',`address`='$address'
		WHERE `id` = $this->mId";
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new ContactException('An error occurred while updating the contact record.');
		
		$this->mName = stripslashes($name);
		$this->mUser = $username;
		$this->mTitle = stripslashes($title);
		$this->mPhone = $phone;
		$this->mEmail = stripslashes($email);
		$this->mAddress = stripslashes($address);
		
		Log::addMessage('Edited contact \''.$this->mName.'\'');
	}
	
	public function getAddress() {
		return HTML::schars($this->mAddress);
	}
	
	public function getEmail() {
		return HTML::schars($this->mEmail);
	}
	
	public function getId() {
		return $this->mId;
	}
	
	/**
	 * Get IDs of contacts in a list
	 * @global db $db
	 * @param integer $list_id
	 * @return array
	 * @throws ContactException
	 */
	public static function getList($list_id) {
		global $db;
		
		if (!is_numeric($list_id))
			throw new ContactException('Invalid list ID.');
		
		$query = 'SELECT `ref_id`
			FROM `'.CONTENT_TABLE.'`
			WHERE `page_id` = '.(int)$list_id.'
			ORDER BY `order` ASC';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new ContactException('Error loading contact list.');
		
		$result = array();
		for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
			$row = $db->sql_fetch_assoc($handle);
			$result[] = $row['ref_id'];
		}
		return $result;
	}
	
	public function getName() {
		return HTML::schars($this->mName);
	}
	
	public function getPhone() {
		return HTML::schars(format_tel($this->mPhone));
	}
	
	public function getTitle() {
		return HTML::schars($this->mTitle);
	}
	
	public function getUserId() {
		return $this->mUserId;
	}
	
	public function getUsername() {
		return HTML::schars($this->mUser);
	}
	
	/**
	 * Set contact list order
	 * @global acl $acl
	 * @global db $db
	 * @param integer $order
	 * @param integer $page_id
	 * @throws ContactException
	 */
	public function setListOrder($order, $page_id) {
		global $acl;
		global $db;

		if (!$acl->check_permission('contacts_edit_lists'))
			throw new ContactException('You are not allowed to edit contact lists.');
		if (!is_numeric($page_id) || !is_numeric($order))
			throw new ContactException('Invalid page ID or order.');

		$order_query = 'UPDATE `'.CONTENT_TABLE.'`
			SET `order` = '.(int)$order.'
			WHERE `ref_id` = '.$this->mId.'
			AND `page_id` = '.(int)$page_id;
		$order_handle = $db->sql_query($order_query);
		if($db->error[$order_handle] === 1)
			throw new ContactException('Error setting contact order.');
	}
	
}

class ContactException extends Exception {}
?>
