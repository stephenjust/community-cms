<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	$date = date('Y-m-d H:i:s');
  if ($_GET['action'] == 'edit') {
  	$page_name_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_POST['page_id'].' LIMIT 1';
		$page_name_handle = $db->query($page_name_query);
		if(!$page_name_handle) {
			$message .= 'Failed to read name of current page for log message. '.mysqli_error($db);
			}
		if($page_name_handle->num_rows == 1) {
  		$edit_id = $_POST['id'];
  		$start_date = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
  		$end_date = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
			$expire = checkbox($_POST['expire']);
  		$text = addslashes($_POST['update_content']);
			$edit_article_query = 'UPDATE '.$CONFIG['db_prefix']."page_messages SET start_date='$start_date',end_date='$end_date',end='$expire',text='$text' WHERE message_id = $edit_id";
			$edit_article = $db->query($edit_article_query);
			if(!$edit_article) {
				$content = 'Failed to edit page message. '.mysqli_error($db);
				} else {
				$page_name = $page_name_handle->fetch_assoc();
				$content = 'Successfully edited page message. '.log_action('Edited page message for page \''.$page_name['title'].'\'');
				}
			} else {
			$content = 'Failed to find the page which you are trying to edit the message of.';
			}
		} else {
		if(!isset($_GET['id']) || $_GET['id'] == '') {
			$_GET['id'] = 0;
			}
		$page_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'page_messages WHERE message_id = '.$_GET['id'].' LIMIT 1';
		$page_message_handle = $db->query($page_message_query);
		if($page_message_handle->num_rows == 1) {
			$page_message = $page_message_handle->fetch_assoc();
			$content = '<form method="POST" action="admin.php?module=page_message_edit&action=edit">
<h1>Edit Page Message</h1>
<table class="admintable">
<input type="hidden" name="id" value="'.$page_message['message_id'].'" />
<input type="hidden" name="page_id" value="'.$page_message['page_id'].'" />
<tr><td class="row1" valign="top">Content:</td>
<td class="row1">
<textarea name="update_content" rows="30">
'.stripslashes($page_message['text']).'</textarea>
</td></tr>
<tr><td width="150" class="row2" valign="top">Date:</td><td class="row2">';
			$months = array('January','February','March','April','May','June','July',
'August','September','October','November','December');
			$start_date = explode('-',$page_message['start_date']);
			$end_date = explode('-',$page_message['end_date']);
			$content .= 'Start:<br />';
			$content .= '<select name="start_month" value="'.$_POST['month'].'" >';
			$monthcount = 1; 
			while ($monthcount <= 12) {
				if($start_date[1] == $monthcount) {
					$content .= '<option value="'.$monthcount.'" selected >'.$months[$monthcount-1].'
</option>'; // Need [$monthcount-1] as arrays start at 0.
					$monthcount++;
					} else {
					$content .= '<option value="'.$monthcount.'">'.$months[$monthcount-1].'</option>';
					$monthcount++;
					}
				}
			$content .= '</select>
<input type="text" name="start_day" maxlength="2" size="2" value="'.$start_date[2].'" />
<input type="text" name="start_year" maxlength="4" size="4" value="'.$end_date[0].'" /><br />
End:<br />';
			$content .= '<select name="end_month" value="'.$_POST['month'].'" >';
			$monthcount = 1; 
			while ($monthcount <= 12) {
				if($end_date[1] == $monthcount) {
					$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."
</option>"; // Need [$monthcount-1] as arrays start at 0.
					$monthcount++;
					} else {
					$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
					$monthcount++;
					}
				}
			if($page_message['end'] == 1) {
				$expire_checked = 'checked';
				} else {
				$expire_checked = NULL;
				}
			$content .= '</select>
<input type="text" name="end_day" maxlength="2" size="2" value="'.$end_date[2].'" />
<input type="text" name="end_year" maxlength="4" size="4" value="'.$end_date[0].'" /></td></tr>
<tr><td width="150" class="row1">Expire:</td><td class="row1">
<input type="checkbox" name="expire" '.$expire_checked.' /></td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2">
<input type="submit" value="Submit" /></td></tr>
</table>';
			} else {
			$content = 'Unable to find requested page message.';
			}
		}
	?>