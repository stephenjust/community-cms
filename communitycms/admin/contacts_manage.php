<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;

if (!$acl->check_permission('adm_contacts_manage'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

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
		try {
			contact_delete($_GET['id']);
			echo 'Successfully deleted contact.<br />';
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

	case 'create':
		try {
			contact_create($_POST['name'],
					$_POST['title'],
					$_POST['phone'],
					$_POST['address'],
					$_POST['email'],
					$_POST['username']);
			echo 'Successfully created contact.<br />'."\n";
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

	case 'edit':
		try {
			$contact = contact_get($_GET['id']);

			// Create form
			$edit_form = new form;
			$edit_form->set_method('post');
			$edit_form->set_target('admin.php?module=contacts_manage&amp;action=editsave&amp;id='.$contact['id']);
			$edit_form->add_textbox('name','Name',$contact['name']);
			$edit_form->add_textbox('username','Username (optional)',$contact['username']);
			$edit_form->add_textbox('title','Title',$contact['title']);
			$edit_form->add_textbox('phone','Telephone',format_tel($contact['phone']));
			$edit_form->add_textbox('address','Address',$contact['address']);
			$edit_form->add_textbox('email','E-Mail',$contact['email']);
			$edit_form->add_submit('submit','Submit');

			$tab_content['edit'] = $edit_form;
			$tab_layout->add_tab('Edit Contact',$tab_content['edit']);
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

// ----------------------------------------------------------------------------

	case 'editsave':
		try {
			contact_edit($_GET['id'],
					$_POST['name'],
					$_POST['title'],
					$_POST['phone'],
					$_POST['address'],
					$_POST['email'],
					$_POST['username']);
			echo 'Successfully edited contact.<br />'."\n";
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

// ----------------------------------------------------------------------------
	case 'settings_save':
		$display_mode = addslashes($_POST['display_mode']);
		if (set_config('contacts_display_mode',$display_mode)) {
			echo 'Saved settings.<br />';
		} else {
			echo '<span class="errormessage">Failed to save settings.</span><br />';
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

echo $tab_layout;

?>
