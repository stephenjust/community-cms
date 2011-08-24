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
 * @global Debug $debug Debug object
 * @param integer $id Page message ID
 * @return boolean Success
 */
function delete_page_message($id) {
	global $acl;
	global $db;
	global $debug;

	// Run pre-execution checks
	if (!$acl->check_permission('page_message_delete')) {
		$debug->addMessage('User lacks necessary permissions to delete pagemessage',true);
		return false;
	}
	if (!is_numeric($id)) {
		$debug->addMessage('Invalid parameter type',true);
		return false;
	}
	$id = (int)$id;

	$read_message_query = 'SELECT m.message_id,m.page_id,p.title,p.id
		FROM ' . PAGE_MESSAGE_TABLE . ' m, ' . PAGE_TABLE . ' p
		WHERE m.message_id = '.$id.' AND m.page_id = p.id
		LIMIT 1';
	$read_message_handle = $db->sql_query($read_message_query);
	if ($db->error[$read_message_handle] === 1) {
		$debug->addMessage('Failed to read message',true);
		return false;
	}
	if ($db->sql_num_rows($read_message_handle) != 1) {
		$debug->addMessage('Page message does not exist',true);
		return false;
	}
	$delete_message_query = 'DELETE FROM ' . PAGE_MESSAGE_TABLE . '
		WHERE message_id = '.(int)$_GET['id'].' LIMIT 1';
	$delete_message = $db->sql_query($delete_message_query);
	if ($db->error[$delete_message] === 1) {
		return false;
	}
	$read_message = $db->sql_fetch_assoc($read_message_handle);
	Log::addMessage('Deleted page message on page \''.stripslashes($read_message['title']).'\'');
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

// Get current page ID
if (!isset($_POST['page']) && !isset($_GET['page'])) {
	$page_id = get_config('home');
} elseif (!isset($_POST['page']) && isset($_GET['page'])) {
	$page_id = (int)$_GET['page'];
	unset($_GET['page']);
} else {
	$page_id = (int)$_POST['page'];
	unset($_POST['page']);
}

$tab_layout = new tabs;
$tab_content['manage'] = '<form method="post" action="?module=page_message_new">
	<select id="adm_page_message_page_list" name="page" onChange="update_page_message_list(\'-\')">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!preg_match('/<LINK>/',$page['title'])) {
		if ($page['id'] == $page_id) {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
		} else {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		}
	}
	$i++;
}
$tab_content['manage'] .= '</select><br />'."\n";
$tab_content['manage'] .= '<div id="adm_page_message_list">Loading...</div>'."\n";
$tab_content['manage'] .= '<script type="text/javascript">update_page_message_list(\''.$page_id.'\');</script>';

$tab_content['manage'] .= '<input type="submit" value="New Page Message" /></form>';
$tab_layout->add_tab('Manage Page Messages',$tab_content['manage']);
$content .= $tab_layout;
?>