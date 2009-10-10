<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
$tab_layout = new tabs;

// ----------------------------------------------------------------------------

function contact_list($page = '*') {
	global $db;
	global $debug;
	// Check parameters
	if (!is_numeric($page) && $page != '*') {
		$debug->add_trace('Invalid parameter',true,'contact_list');
		return false;
	}
	$query_page = ($page != '*') ? ' WHERE `page` = '.$page : NULL;
	$contact_list_query = 'SELECT * FROM `'.CONTACTS_TABLE.'`'.$query_page;
	$contact_list_handle = $db->sql_query($contact_list_query);
	if ($db->error[$contact_list_handle] === 1) {
		$debug->add_trace('SQL error: failed to read contact list',true,'contact_list');
		return false;
	}
	$num_contacts = $db->sql_num_rows($contact_list_handle);
	$contacts = array();
	for ($i = 0; $i < $num_contacts; $i++) {
		$contact_list = $db->sql_fetch_assoc($contact_list_handle);
		$contacts[] = $contact_list;
	}
	return $contacts;
}

// ----------------------------------------------------------------------------

// FIXME: Implement different operations
switch ($_GET['action']) {
	default:

		break;
	case 'delete':
		if (!is_numeric($_GET['id'])) {
			$content .= 'Invalid contact ID.<br />'."\n";
			break;
		}
		$delete_contact_query = 'DELETE FROM `' . CONTACTS_TABLE . '`
			WHERE `id` = '.(int)$_GET['id'];
		$delete_contact = $db->sql_query($delete_contact_query);
		if ($db->error[$delete_contact] === 1) {
			$content .= 'Failed to delete contact.<br />'."\n";
		} else {
			$content .= 'Successfully deleted contact.<br />'.log_action('Deleted contact #'.$_GET['id']);
		}
		break;
	case 'create':
		$name = addslashes($_POST['name']);
		$uname = addslashes($_POST['username']);
		$title = addslashes($_POST['title']);
		$phone = $_POST['phone'];
		$address = addslashes($_POST['address']);
		$email = addslashes($_POST['email']);
		$phone_hide = (isset($_POST['phone_hide'])) ? 1 : 0;
		$address_hide = (isset($_POST['address_hide'])) ? 1 : 0;
		$email_hide = (isset($_POST['email_hide'])) ? 1 : 0;

		// Format phone number for storage
		if ($phone != "") {
			$phone = str_replace(array('-','(',')',' ','.'),NULL,$phone);
			if (!is_numeric($phone)) {
				$content .= 'Invalid telephone number.<br />'."\n";
				break;
			}
			$phone = (int)$phone;
		}

		// Verify email address
		if ($email != "") {
			if (!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
				$content .= 'Invalid E-Mail address.<br />'."\n";
				break;
			}
		}

		// Verify username and get user ID
		if ($uname != '') {
			$username_query = 'SELECT `id` FROM `'.USER_TABLE.'`
				WHERE `username` = \''.$uname.'\'';
			$username_handle = $db->sql_query($username_query);
			if ($db->error[$username_handle] === 1) {
				$content .= 'Failed to check if you entered a valid username.<br />'."\n";
				break;
			}
			if ($db->sql_num_rows($username_handle) == 0) {
				$content .= 'This contact will not be associated with the chosen
					username because that user does not exist.<br />'."\n";
			} else {
				$username = $db->sql_fetch_assoc($username_handle);
				$uid = $username['id'];
			}
		} else {
			$uid = 0;
		}

		// Create contact
		$new_contact_query = 'INSERT INTO `'.CONTACTS_TABLE.'`
			(`name`,`user_id`,`title`,`phone`,`email`,
			`address`,`phone_hide`,`email_hide`,`address_hide`)
			VALUES (\''.$name.'\','.$uid.',\''.$title.'\','.$phone.',\''.$email.'\',
			\''.$address.'\','.$phone_hide.','.$address_hide.','.$email_hide.')';
		$new_contact_handle = $db->sql_query($new_contact_query);
		if ($db->error[$new_contact_handle] === 1) {
			$content .= 'Failed to create contact.<br />'."\n";
			break;
		}
		$content .= 'Successfully created contact.<br />'."\n";
		log_action('New contact \''.$name.'\'');
		break;
	case 'edit':

		$tab_layout->add_tab('Edit Contact',NULL);
		break;
}

// ----------------------------------------------------------------------------

$contact_list = contact_list();
$tab_content['manage'] = NULL;
if (count($contact_list) == 0) {
	$tab_content['manage'] .= 'There are currently no contacts in the database.<br />'."\n";
} else {
	$tab_content['manage'] .= <<<EOT
<table class="admintable">
<tr>
<th width="10px">ID</th><th>Name</th><th colspan="2" width="10px"></th>
</tr>
EOT;
	foreach ($contact_list as $contact) {
		$tab_content['manage'] .= <<<EOT
<tr>
<td>{$contact['id']}</td>
<td>{$contact['name']}</td>
<td>Edit</td>
<td><a href="?module=contacts_manage&action=delete&id={$contact['id']}"><img src="<!-- \$IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
</tr>
EOT;
	}
	$tab_content['manage'] .= '</table>';
}
$tab_layout->add_tab('Manage Contacts',$tab_content['manage']);

// ----------------------------------------------------------------------------

$new_form = new form;
$new_form->set_method('post');
$new_form->set_target('admin.php?module=contacts_manage&amp;action=create');
$new_form->add_textbox('name','Name');
$new_form->add_textbox('username','Username (optional)');
$new_form->add_textbox('title','Title');
$new_form->add_textbox('phone','Telephone');
$new_form->add_textbox('address','Address');
$new_form->add_textbox('email','E-Mail');
$new_form->add_checkbox('phone_hide','Hide Telephone');
$new_form->add_checkbox('address_hide','Hide Address');
$new_form->add_checkbox('email_hide','Hide E-Mail');
$new_form->add_submit('submit','Submit');

$tab_content['create'] = $new_form;
$tab_layout->add_tab('Create Contact',$tab_content['create']);

$content .= $tab_layout;

?>
