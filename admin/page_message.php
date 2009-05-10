<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
if ($_GET['action'] == 'delete') {
	$read_message_query = 'SELECT m.message_id,m.page_id,p.title,p.id
		FROM ' . PAGE_MESSAGE_TABLE . ' m, ' . PAGE_TABLE . ' p
		WHERE m.message_id = '.(int)$_GET['id'].' AND m.page_id = p.id
		LIMIT 1';
	$read_message_handle = $db->sql_query($read_message_query);
	if ($db->error[$read_message_handle] === 1) {
		$content .= 'Failed to read message information.<br />';
	}
	if ($db->sql_num_rows($read_message_handle) == 1) {
		$delete_message_query = 'DELETE FROM ' . PAGE_MESSAGE_TABLE . '
			WHERE message_id = '.(int)$_GET['id'].' LIMIT 1';
		$delete_message = $db->sql_query($delete_message_query);
		if ($db->error[$delete_message] === 1) {
			$content .= 'Failed to delete message.<br />';
		} else {
			$read_message = $db->sql_fetch_assoc($read_message_handle);
			$content .= 'Successfully deleted page message. '.log_action('Deleted page message on page \''.stripslashes($read_message['title']).'\'');
		}
	} else {
		$content .= 'Could not find the page message you asked to delete.<br />';
	}
}

// ----------------------------------------------------------------------------

$content .= '<h1>Page Messages</h1>
<table style="border: 1px solid #000000;">
<tr><td><form method="post" action="admin.php?module=page_message"><select name="page">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!isset($_POST['page'])) {
		$_POST['page'] = $site_info['home'];
	}
	if (!eregi('<LINK>',$page['title'])) {
		if ($page['id'] == $_POST['page']) {
			$content .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
		} else {
			$content .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		}
	}
	$i++;
}
$content .= '</select></td><td colspan="2"><input type="submit" value="Change Page" /></form></td></tr>
<tr><td width="350">Content:</td><td>Del</td><td>Edit</td></tr>';
// Get page message list in the order defined in the database. First is 0.
$page_message_query = 'SELECT * FROM '.PAGE_MESSAGE_TABLE.'
	WHERE page_id = '.addslashes($_POST['page']);
$page_message_handle = $db->sql_query($page_message_query);
$i = 1;
if($db->sql_num_rows($page_message_handle) == 0) {
	$content .= '<tr><td colspan="3">There are no page messages present on this page.</td></tr>';
	}
while ($i <= $db->sql_num_rows($page_message_handle)) {
	$page_message = $db->sql_fetch_assoc($page_message_handle);
	$content .= '<tr>
		<td class="adm_page_list_item">'.truncate(strip_tags(stripslashes($page_message['text']),'<br>'),75).'</td>
		<td><a href="?module=page_message&action=delete&id='.$page_message['message_id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=page_message_edit&id='.$page_message['message_id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
		</tr>';
	$i++;
}
$content .= '<tr>
<td colspan="3">
<form method="post" action="?module=page_message_new&amp;page='.$_POST['page'].'">
<input type="submit" value="New Page Message" />
</form>
</td>
</tr></table>';
?>