<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	function truncate($text,$numb) {
		$text = html_entity_decode($text, ENT_QUOTES);
		if (strlen($text) > $numb) {
			$text = substr($text, 0, $numb);
			$text = substr($text,0,strrpos($text," "));
			//This strips the full stop:
			if ((substr($text, -1)) == ".") {
        $text = substr($text,0,(strrpos($text,".")));
    		}
			$etc = "...";
			$text = $text.$etc;
			}
		$text = htmlentities($text, ENT_QUOTES);
		return $text;
		}
	$root = "./";
	$message = NULL;
	$date = date('Y-m-d H:i:s');
		if ($_GET['action'] == 'delete') {
		$read_message_query = 'SELECT message.message_id,message.page_id,page.title,page.id FROM '.$CONFIG['db_prefix'].'page_messages message, '.$CONFIG['db_prefix'].'pages page WHERE message.message_id = '.$_GET['id'].' AND message.page_id = page.id LIMIT 1';
		$read_message_handle = $db->query($read_message_query);
		if(!$read_message_handle) {
			$message .= 'Failed to read message information. '.mysqli_error($db);
			}
		if($read_message_handle->num_rows == 1) {
			$delete_message_query = 'DELETE FROM '.$CONFIG['db_prefix'].'page_messages WHERE message_id = '.$_GET['id'].' LIMIT 1';
			$delete_message = $db->query($delete_message_query);
			if(!$delete_message) {
				$message .= 'Failed to delete message. '.mysqli_error($db);
				} else {
				$read_message = $read_message_handle->fetch_assoc();
				$message .= 'Successfully deleted page message. '.log_action('Deleted page message on page \''.addslashes($read_message['title']).'\'');
				}
			} else {
			$message .= 'Could not find the page message you asked to delete.';
			}
		}
	$content = $message;
$content .= '<h1>Page Messages</h1>
<table style="border: 1px solid #000000;">
<tr><td><form method="post" action="admin.php?module=page_message"><select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			if(!isset($_POST['page'])) {
				$_POST['page'] = $site_info['home'];
				}
			if(!eregi('<LINK>',$page['title'])) {
				if($page['id'] == $_POST['page']) {
					$content .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
					} else {
					$content .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
					}
				}
			$i++;
			}
		$content = $content.'</select></td><td colspan="2"><input type="submit" value="Change Page" /></form></td></tr>
<tr><td width="350">Content:</td><td>Del</td><td>Edit</td></tr>';
	// Get page message list in the order defined in the database. First is 0.
	$page_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'page_messages WHERE page_id = '.stripslashes($_POST['page']);
	$page_message_handle = $db->query($page_message_query);
 	$i = 1;
 	if($page_message_handle->num_rows == 0) {
 		$content .= '<tr><td colspan="3">There are no page messages present on this page.</td></tr>';
 		}
	while ($i <= $page_message_handle->num_rows) {
		$page_message = $page_message_handle->fetch_assoc();
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