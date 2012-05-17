<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

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
 * @throws Exception 
 */
function contact_create($name,$title,$phone,$address,$email,$username) {
	global $acl;
	global $db;
	
	if (!$acl->check_permission('contacts_create'))
		throw new Exception('You are not allowed to create contact records.');

	// Sanitize inputs
	$name = $db->sql_escape_string(htmlspecialchars($name));
	$title = $db->sql_escape_string(htmlspecialchars($title));
	$address = $db->sql_escape_string(htmlspecialchars($address));
	$email = $db->sql_escape_string(htmlspecialchars($email));
	$username = $db->sql_escape_string(htmlspecialchars($username));

	// Format phone number for storage
	if ($phone != "") {
		// Remove special characters that may be used in a phone number
		$phone = str_replace(array('-','(',')',' ','.','+'),NULL,$phone);
		if (!is_numeric($phone))
			throw new Exception('Invalid telephone number.');
	}

	// Verify email address
	if ($email != "") {
		if (!preg_match('/^[a-z0-9_\-\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i',$email))
			throw new Exception('Invalid email address.');
	}

	// Verify username and get user ID
	if ($username != '') {
		$username_query = 'SELECT `id`
			FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'';
		$username_handle = $db->sql_query($username_query);
		if ($db->error[$username_handle] === 1)
			throw new Exception('An error occurred while looking up a username record.');
		if ($db->sql_num_rows($username_handle) == 0) {
			echo 'This contact will not be associated with the chosen
				username because that user does not exist.<br />'."\n";
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
		throw new Exception('An error occurred while creating the contact record.');

	Log::addMessage('New contact \''.$name.'\'');
}

/**
 * Delete a contact entry from the database
 * @global acl $acl Permission object
 * @global db $db Database object
 * @param integer $id Contact ID
 * @throws Exception
 */
function contact_delete($id) {
	global $acl;
	global $db;

	if (!$acl->check_permission('contact_delete'))
		throw new Exception('You are not allowed to delete contacts.');
	
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('Invalid contact ID.');

	// Get info for log message
	$info_query = 'SELECT *
		FROM `'.CONTACTS_TABLE.'`
		WHERE `id` = '.$id.'
		LIMIT 1';
	$info_handle = $db->sql_query($info_query);
	if ($db->error[$info_handle] === 1)
		throw new Exception('An error occurred while reading contact information.');
	if ($db->sql_num_rows($info_handle) != 1)
		throw new Exception('The contact you want to delete does not exist.');
	$contact_info = $db->sql_fetch_assoc($info_handle);

	// Delete 'content' records
	$del_cnt_query = 'DELETE FROM `'.CONTENT_TABLE.'`
		WHERE `ref_id` = '.$id.'
		AND `ref_type` = (SELECT `id` FROM `'.PAGE_TYPE_TABLE.'` WHERE `name` = \'Contacts\')';
	$del_cnt_handle = $db->sql_query($del_cnt_query);
	if ($db->error[$del_cnt_handle] === 1)
		throw new Exception('An error occurred while deleting the content record.');

	// Delete record
	$delete_query = 'DELETE FROM `' . CONTACTS_TABLE . '`
		WHERE `id` = '.$id;
	$delete_contact = $db->sql_query($delete_query);
	if ($db->error[$delete_contact] === 1)
		throw new Exception('An error occurred while deleting the contact record.');

	Log::addMessage('Deleted contact \''.$contact_info['name'].'\'');
}

function contact_edit($id,$name,$title,$phone,$address,$email,$username) {
	global $acl;
	global $db;
	
	if (!$acl->check_permission('contacts_edit'))
		throw new Exception('You are not allowed to edit contact records.');

	// Sanitize inputs
	$name = $db->sql_escape_string(htmlspecialchars($name));
	$title = $db->sql_escape_string(htmlspecialchars($title));
	$address = $db->sql_escape_string(htmlspecialchars($address));
	$email = $db->sql_escape_string(htmlspecialchars($email));
	$username = $db->sql_escape_string(htmlspecialchars($username));

	// Format phone number for storage
	if ($phone != "") {
		// Remove special characters that may be used in a phone number
		$phone = str_replace(array('-','(',')',' ','.','+'),NULL,$phone);
		if (!is_numeric($phone))
			throw new Exception('Invalid telephone number.');
	}

	// Verify email address
	if ($email != "") {
		if (!preg_match('/^[a-z0-9_\-\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i',$email))
			throw new Exception('Invalid email address.');
	}

	// Verify username and get user ID
	if ($username != '') {
		$username_query = 'SELECT `id`
			FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'';
		$username_handle = $db->sql_query($username_query);
		if ($db->error[$username_handle] === 1)
			throw new Exception('An error occurred while looking up a username record.');
		if ($db->sql_num_rows($username_handle) == 0) {
			echo 'This contact will not be associated with the chosen
				username because that user does not exist.<br />'."\n";
			$uid = 0;
		} else {
			$uname = $db->sql_fetch_assoc($username_handle);
			$uid = $uname['id'];
		}
	} else {
		$uid = 0;
	}

	// Update contact record
	$query = 'UPDATE `'.CONTACTS_TABLE."`
		SET `name`='$name',`user_id`=$uid,`title`='$title',
		`phone`=$phone,`email`='$email',`address`='$address'
		WHERE `id` = $id";
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1)
		throw new Exception('An error occurred while updating the contact record.');

	Log::addMessage('Edited contact \''.stripslashes($name).'\'');
}

/**
 * Fetch a single contact record
 * @global db $db
 * @param integer $id
 * @return array
 * @throws Exception 
 */
function contact_get($id) {
	global $db;

	$id = (int)$id;
	$query = 'SELECT `c`.*, `u`.`username`
		FROM `'.CONTACTS_TABLE.'` `c`
		LEFT JOIN `'.USER_TABLE.'` `u`
		ON `c`.`user_id` = `u`.`id`
		WHERE `c`.`id` = '.$id.' LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1)
		throw new Exception('An error occurred while retrieving contact information.');
	if ($db->sql_num_rows($handle) != 1)
		throw new Exception('The requested contact could not be found.');
	$contact = $db->sql_fetch_assoc($handle);
	
	return $contact;
}

/**
 * Add a contact to a contact list
 * @global acl $acl
 * @global db $db
 * @param integer $contact_id
 * @param integer $list_id
 * @return boolean
 */
function contact_add_to_list($contact_id,$list_id) {
	global $acl;
	global $db;

	// Check permissions
	if (!$acl->check_permission('contacts_edit_lists')) {
		return false;
	}

	// Check for invalid parameters
	if (!is_numeric($contact_id) || !is_numeric($list_id)) {
		return false;
	}

	$check_contact_query = 'SELECT `id`,`name` FROM `'.CONTACTS_TABLE.'`
		WHERE `id` = '.$contact_id;
	$check_contact_handle = $db->sql_query($check_contact_query);
	if ($db->error[$check_contact_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($check_contact_handle) === 0) {
		return false;
	}
	$check_contact = $db->sql_fetch_assoc($check_contact_handle);

	$check_list_query = 'SELECT `page`.`id`, `page`.`title`
		FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
		WHERE `page`.`type` = `pt`.`id`
		AND `pt`.`name` = \'Contacts\'
		AND `page`.`id` = '.$list_id;
	$check_list_handle = $db->sql_query($check_list_query);
	if ($db->error[$check_list_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($check_list_handle) === 0) {
		return false;
	}
	$check_list = $db->sql_fetch_assoc($check_list_handle);

	$check_dupe_query = 'SELECT `id` FROM `'.CONTENT_TABLE.'`
		WHERE `ref_id` = '.$contact_id.'
		AND `page_id` = '.$list_id;
	$check_dupe_handle = $db->sql_query($check_dupe_query);
	if ($db->error[$check_dupe_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($check_dupe_handle) !== 0) {
		return false;
	}

	// Add contact to list
	$insert_query = 'INSERT INTO `'.CONTENT_TABLE.'`
		(`page_id`,`ref_type`,`ref_id`) VALUES
		('.$list_id.',(SELECT `id` FROM `'.PAGE_TYPE_TABLE.'` WHERE `name` = \'Contacts\'),'.$contact_id.')';
	$insert_handle = $db->sql_query($insert_query);
	if ($db->error[$insert_handle] === 1) {
		return false;
	}
	Log::addMessage('Added '.$check_contact['name'].' to contact list \''.$check_list['title'].'\'');
	return true;
}

/**
 * Remove a contact from a contact list
 * @global acl $acl
 * @global db $db
 * @param integer $content_id
 * @return boolean
 */
function contact_remove_from_list($content_id) {
	global $acl;
	global $db;

	if (!$acl->check_permission('contacts_edit_lists')) {
		return false;
	}
	if (!is_numeric($content_id)) {
		return false;
	}
	$delete_query = 'DELETE FROM `'.CONTENT_TABLE.'`
		WHERE `id` = '.$content_id;
	$delete_handle = $db->sql_query($delete_query);
	if($db->error[$delete_handle] === 1) {
		return false;
	}
	return true;
}

/**
 * Update contact order
 * @global acl $acl
 * @global db $db
 * @param integer $list_entry
 * @param integer $order
 * @return boolean
 */
function contact_order_list($list_entry,$order) {
	global $acl;
	global $db;

	if (!$acl->check_permission('contacts_edit_lists')) {
		return false;
	}
	if (!is_numeric($list_entry) || !is_numeric($order)) {
		return false;
	}

	$order_query = 'UPDATE `'.CONTENT_TABLE.'`
		SET `order` = '.(int)$order.'
		WHERE `id` = '.$list_entry;
	$order_handle = $db->sql_query($order_query);
	if($db->error[$order_handle] === 1) {
		return false;
	}
	return true;
}
?>
