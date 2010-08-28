<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_page_message')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

/**
 * Deletes a page message entry
 * @global acl $acl Permission object
 * @global db $db Database object
 * @global debug $debug Debug object
 * @param integer $id Page message ID
 * @return boolean Success
 */
function delete_page_message($id) {
	global $acl;
	global $db;
	global $debug;

	// Run pre-execution checks
	if (!$acl->check_permission('pagemessage_delete')) {
		$debug->add_trace('User lacks necessary permissions to delete pagemessage',true,'delete_page_message()');
		return false;
	}
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid parameter type',true,'delete_page_message()');
		return false;
	}
	$id = (int)$id;

	$read_message_query = 'SELECT m.message_id,m.page_id,p.title,p.id
		FROM ' . PAGE_MESSAGE_TABLE . ' m, ' . PAGE_TABLE . ' p
		WHERE m.message_id = '.$id.' AND m.page_id = p.id
		LIMIT 1';
	$read_message_handle = $db->sql_query($read_message_query);
	if ($db->error[$read_message_handle] === 1) {
		$debug->add_trace('Failed to read message',true,'delete_page_message()');
		return false;
	}
	if ($db->sql_num_rows($read_message_handle) != 1) {
		$debug->add_trace('Page message does not exist',true,'delete_page_message()');
		return false;
	}
	$delete_message_query = 'DELETE FROM ' . PAGE_MESSAGE_TABLE . '
		WHERE message_id = '.(int)$_GET['id'].' LIMIT 1';
	$delete_message = $db->sql_query($delete_message_query);
	if ($db->error[$delete_message] === 1) {
		return false;
	}
	$read_message = $db->sql_fetch_assoc($read_message_handle);
	log_action('Deleted page message on page \''.stripslashes($read_message['title']).'\'');
	return true;
}

$content = NULL;
if ($_GET['action'] == 'delete') {
	if (delete_page_message($_GET['id'])) {
		$content .= 'Successfully deleted page message.<br />';
	} else {
		$content .= '<span class="errormessage">Failed to delete page message.</span><br />';
	}
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$tab_content['manage'] = NULL;
$tab_content['manage'] .= '<table class="admintable">
<tr><th colspan="3"><form method="post" action="admin.php?module=page_message"><select name="page">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!isset($_POST['page']) && !isset($_GET['page'])) {
		$page_id = get_config('home');
	} elseif (!isset($_POST['page']) && isset($_GET['page'])) {
		$page_id = (int)$_GET['page'];
		unset($_GET['page']);
	} else {
		$page_id = (int)$_POST['page'];
		unset($_POST['page']);
	}

	if (!preg_match('/<LINK>/',$page['title'])) {
		if ($page['id'] == $page_id) {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
		} else {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		}
	}
	$i++;
}
$tab_content['manage'] .= '</select><input type="submit" value="Change Page" /></form></th></tr>
<tr><th width="350">Content:</th><th colspan="2"></th></tr>';
// Get page message list in the order defined in the database. First is 0.
$page_message_query = 'SELECT * FROM '.PAGE_MESSAGE_TABLE.'
	WHERE page_id = '.$page_id;
$page_message_handle = $db->sql_query($page_message_query);
$i = 1;
if ($db->sql_num_rows($page_message_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="3">There are no page messages present on this page.</td></tr>';
}
while ($i <= $db->sql_num_rows($page_message_handle)) {
	$page_message = $db->sql_fetch_assoc($page_message_handle);
	$tab_content['manage'] .= '<tr>
		<td class="adm_page_list_item">'.truncate(strip_tags(stripslashes($page_message['text']),'<br>'),75).'</td>
		<td><a href="?module=page_message&action=delete&id='.$page_message['message_id'].'&amp;page='.$page_id.'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=page_message_edit&id='.$page_message['message_id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
		</tr>';
	$i++;
}
if ($acl->check_permission('page_message_new')) {
	$tab_content['manage'] .= '<tr><td colspan="3">
		<form method="post" action="?module=page_message_new&amp;page='.$page_id.'">
		<input type="submit" value="New Page Message" />
		</form></td></tr>';
}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Page Messages',$tab_content['manage']);
$content .= $tab_layout;
?>