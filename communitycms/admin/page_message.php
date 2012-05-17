<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;

if (!$acl->check_permission('adm_page_message'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

/**
 * Deletes a page message entry
 * @global acl $acl Permission object
 * @global db $db Database object
 * @param integer $id Page message ID
 * @throws Exception
 */
function pagemessage_delete($id) {
	global $acl;
	global $db;

	// Run pre-execution checks
	if (!$acl->check_permission('page_message_delete'))
		throw new Exception('You are not allowed to delete page messages.');
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('The given message ID is invalid.');

	// Read page message information
	$read_message_query = 'SELECT `p`.`title`
		FROM `'.PAGE_MESSAGE_TABLE.'` `m`, `'.PAGE_TABLE.'` `p`
		WHERE `m`.`message_id` = '.$id.' AND `m`.`page_id` = `p`.`id`
		LIMIT 1';
	$read_message_handle = $db->sql_query($read_message_query);
	if ($db->error[$read_message_handle] === 1)
		throw new Exception('An error occurred when reading the page message you asked to delete.');
	if ($db->sql_num_rows($read_message_handle) != 1)
		throw new Exception('The page message you are trying to delete does not exist.');
	
	// Delete page message record
	$delete_message_query = 'DELETE FROM `'.PAGE_MESSAGE_TABLE.'`
		WHERE `message_id` = '.$id.' LIMIT 1';
	$delete_message = $db->sql_query($delete_message_query);
	if ($db->error[$delete_message] === 1)
		throw new Exception('An error occurred while deleting the page message.');

	$read_message = $db->sql_fetch_assoc($read_message_handle);
	Log::addMessage('Deleted page message on page \''.$read_message['title'].'\'');
}

if ($_GET['action'] == 'delete') {
	try {
		pagemessage_delete($_GET['id']);
		echo 'Successfully deleted page message.<br />';
	}
	catch (Exception $e) {
		echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
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
echo $tab_layout;
?>