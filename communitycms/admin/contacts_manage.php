<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_contacts_manage')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;
include(ROOT.'functions/contacts.php');
$tab_layout = new tabs;

// ----------------------------------------------------------------------------

/**
 * Get contact list
 * @global db $db Database object
 * @global Debug $debug Debugging object
 * @param integer $page Page ID
 * @return array Contact information (or false on failure)
 */
function contact_list($page = '*') {
	global $db;
	global $debug;
	// Check parameters
	if (!is_numeric($page) && $page != '*') {
		$debug->addMessage('Invalid parameter',true);
		return false;
	}
	$query_page = ($page != '*') ? ' WHERE `page` = '.$page : NULL;
	$contact_list_query = 'SELECT * FROM `'.CONTACTS_TABLE.'`'.$query_page;
	$contact_list_handle = $db->sql_query($contact_list_query);
	if ($db->error[$contact_list_handle] === 1) {
		$debug->addMessage('SQL error: failed to read contact list',true);
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

switch ($_GET['action']) {
	default:

		break;
	case 'delete':
		if (!$acl->check_permission('contact_delete')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to delete a contact.</span><br />'."\n";
			break;
		}
		if (delete_contact($_GET['id'])) {
			$content .= 'Successfully deleted contact.<br />';
		} else {
			$content .= '<span class="errormessage">Failed to delete contact.</span><br />'."\n";
		}
		break;

// ----------------------------------------------------------------------------

	case 'create':
		$name = addslashes($_POST['name']);
		$uname = addslashes($_POST['username']);
		$title = addslashes($_POST['title']);
		$phone = $_POST['phone'];
		$address = addslashes($_POST['address']);
		$email = addslashes($_POST['email']);

		// Format phone number for storage
		if ($phone != "") {
			// Remove special characters that may be used in a phone number
			$phone = str_replace(array('-','(',')',' ','.','+'),NULL,$phone);
			if (!is_numeric($phone)) {
				$content .= 'Invalid telephone number.<br />'."\n";
				break;
			}
		} else {
			$phone = 'NULL';
		}
		
		// Verify email address
		if ($email != "") {
			if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i',$email)) {
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
				$uid = 0;
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
			`address`)
			VALUES (\''.$name.'\','.$uid.',\''.$title.'\',
			'.$phone.',\''.$email.'\',\''.$address.'\')';
		$new_contact_handle = $db->sql_query($new_contact_query);
		if ($db->error[$new_contact_handle] === 1) {
			$content .= 'Failed to create contact.<br />'."\n";
			break;
		}
		$content .= 'Successfully created contact.<br />'."\n";
		Log::new_message('New contact \''.$name.'\'');
		break;

// ----------------------------------------------------------------------------

	case 'edit':
		// Validate ID
		if (!is_numeric($_GET['id'])) {
			$content .= 'Invalid contact ID.<br />'."\n";
			break;
		}
		$id = (int)$_GET['id'];
		$get_info_query = 'SELECT * FROM `'.CONTACTS_TABLE.'`
			WHERE `id` = '.$id.' LIMIT 1';
		$get_info_handle = $db->sql_query($get_info_query);
		if ($db->error[$get_info_handle] === 1) {
			$content .= 'Failed to read contact information.<br />'."\n";
			break;
		}
		if ($db->sql_num_rows($get_info_handle) != 1) {
			$content .= 'Contact not found.<br />'."\n";
			break;
		}
		$contact = $db->sql_fetch_assoc($get_info_handle);

		// Check for username
		if ($contact['user_id'] != 0) {
			$username_query = 'SELECT `username` FROM `'.USER_TABLE.'`
				WHERE `id` = '.$contact['user_id'].' LIMIT 1';
			$username_handle = $db->sql_query($username_query);
			if ($db->error[$username_handle] === 1) {
				$content .= 'Failed to look up username.<br />'."\n";
				$uname['username'] = NULL;
			} else {
				if ($db->sql_num_rows($username_handle) != 1) {
					$content .= 'User associated with this contact no longer exists.<br />'."\n";
					$uname['username'] = NULL;
				} else {
					$uname = $db->sql_fetch_assoc($username_handle);
				}
			}
		} else {
			$uname['username'] = NULL;
		}

		// Create form
		$edit_form = new form;
		$edit_form->set_method('post');
		$edit_form->set_target('admin.php?module=contacts_manage&amp;action=editsave&amp;id='.$id);
		$edit_form->add_textbox('name','Name',stripslashes($contact['name']));
		$edit_form->add_textbox('username','Username (optional)',stripslashes($uname['username']));
		$edit_form->add_textbox('title','Title',stripslashes($contact['title']));
		$edit_form->add_textbox('phone','Telephone',format_tel($contact['phone']));
		$edit_form->add_textbox('address','Address',stripslashes($contact['address']));
		$edit_form->add_textbox('email','E-Mail',stripslashes($contact['email']));
		$edit_form->add_submit('submit','Submit');

		$tab_content['edit'] = $edit_form;
		$tab_layout->add_tab('Edit Contact',$tab_content['edit']);
		unset($uname);
		unset($contact);
		unset($id);
		break;

// ----------------------------------------------------------------------------

	case 'editsave':
		$name = addslashes($_POST['name']);
		$uname = addslashes($_POST['username']);
		$title = addslashes($_POST['title']);
		$phone = $_POST['phone'];
		$address = addslashes($_POST['address']);
		$email = addslashes($_POST['email']);

		// Format phone number for storage
		if ($phone != '') {
			// Remove special characters
			$phone = str_replace(array('-','(',')',' ','.','+'),NULL,$phone);
			if (!is_numeric($phone)) {
				$content .= 'Invalid telephone number.<br />'."\n";
				break;
			}
		} else {
			$phone = 'NULL';
		}

		// Verify email address
		if ($email != '') {
			if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i',$email)) {
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
				$uid = 0;
			} else {
				$username = $db->sql_fetch_assoc($username_handle);
				$uid = $username['id'];
			}
		} else {
			$uid = 0;
		}

		// Create contact
		$new_contact_query = 'UPDATE `'.CONTACTS_TABLE.'`
			SET `name`=\''.$name.'\',`user_id`='.$uid.',`title`=\''.$title.'\',
			`phone`='.$phone.',`email`=\''.$email.'\',`address`=\''.$address.'\'
			WHERE `id` = '.(int)$_GET['id'];
		$new_contact_handle = $db->sql_query($new_contact_query);
		if ($db->error[$new_contact_handle] === 1) {
			$content .= 'Failed to edit contact.<br />'."\n";
			break;
		}
		$content .= 'Successfully edited contact.<br />'."\n";
		Log::new_message('Edited contact \''.stripslashes($name).'\'');
		break;

// ----------------------------------------------------------------------------
	case 'settings_save':
		$display_mode = addslashes($_POST['display_mode']);
		if (set_config('contacts_display_mode',$display_mode)) {
			$content .= 'Saved settings.<br />';
		} else {
			$content .= '<span class="errormessage">Failed to save settings.</span><br />';
		}
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
<td><a href="?module=contacts_manage&action=edit&id={$contact['id']}"><img src="<!-- \$IMAGE_PATH\$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
<td><a href="javascript:confirm_delete('?module=contacts_manage&amp;action=delete&amp;
	id={$contact['id']}')"><img src="<!-- \$IMAGE_PATH\$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
</tr>
EOT;
	}
	$tab_content['manage'] .= '</table>';
}
$tab_layout->add_tab('Manage Contacts',$tab_content['manage']);

// ----------------------------------------------------------------------------

// A contact list is the same thing as a contacts page. One list per page.
$tab_content['manage_lists'] = NULL;
// Get current list of Contacts pages
$current_lists_query = 'SELECT `page`.`id`,`page`.`title`
	FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
	WHERE `pt`.`id` = `page`.`type`
	AND `pt`.`name` = \'Contacts\'';
$current_lists_handle = $db->sql_query($current_lists_query);
if ($db->error[$current_lists_handle] === 1) {
	$tab_content['manage_lists'] .= '<span class="errormessage">Failed to search for Contact Lists</span><br />';
} else {
	if ($db->sql_num_rows($current_lists_handle) == 0) {
		$tab_content['manage_lists'] .= 'No Contact Lists exist. Please create a new Contacts page to add one.<br />';
	} else {
		$tab_content['manage_lists'] .= '<select name="cl" id="adm_cl_list" onChange="update_cl_manager(\'-\')">'."\n";
		for ($i = 0; $i < $db->sql_num_rows($current_lists_handle); $i++) {
			$current_lists_result = $db->sql_fetch_assoc($current_lists_handle);
			// Set default page
			if (!isset($_POST['page'])) {
				$_POST['page'] = $current_lists_result['id'];
			}
			if ($_POST['page'] == $current_lists_result['id']) {
				$tab_content['manage_lists'] .= "\t".'<option value="'.$current_lists_result['id'].'" selected>'.$current_lists_result['title'].'</option>'."\n";
			} else {
				$tab_content['manage_lists'] .= "\t".'<option value="'.$current_lists_result['id'].'">'.$current_lists_result['title'].'</option>'."\n";
			}
		}
		$tab_content['manage_lists'] .= '</select>'."\n";
		$tab_content['manage_lists'] .= '<div id="adm_contact_list_manager">Loading...</div>'."\n";
		$tab_content['manage_lists'] .= '<script type="text/javascript">update_cl_manager(\''.$_POST['page'].'\');</script>';
	}
}
$tab_layout->add_tab('Contact Lists',$tab_content['manage_lists']);

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
$new_form->add_submit('submit','Submit');

$tab_content['create'] = $new_form;
$tab_layout->add_tab('Create Contact',$tab_content['create']);

// ----------------------------------------------------------------------------

$settings_form = new form;
$settings_form->set_method('post');
$settings_form->set_target('admin.php?module=contacts_manage&amp;action=settings_save');
$settings_form->add_select('display_mode','Display Mode',
		array('card','compact'),
		array('Business Card','Compact'),
		get_config('contacts_display_mode'));
$settings_form->add_submit('submit','Submit');
$tab_layout->add_tab('Settings',$settings_form);

$content .= $tab_layout;

?>
