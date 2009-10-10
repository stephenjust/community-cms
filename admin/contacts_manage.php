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

		break;
	case 'create':

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
<td>Delete</td>
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
