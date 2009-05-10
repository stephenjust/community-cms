<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
if ($_GET['action'] == 'save') {
	$page_name_query = 'SELECT * FROM ' . PAGE_TABLE . '
		WHERE id = '.$_POST['page_id'].' LIMIT 1';
	$page_name_handle = $db->sql_query($page_name_query);
	if ($db->error[$page_name_handle] === 1) {
		$content .= 'Failed to read name of current page for log message.<br />';
	}
	if ($db->sql_num_rows($page_name_handle) == 1) {
		$page_id = $_POST['page_id'];
		$start_date = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
		$end_date = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
		$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
		$text = addslashes($_POST['text']);
		$new_message_query = 'INSERT INTO ' . PAGE_MESSAGE_TABLE . "
			SET start_date='$start_date',end_date='$end_date',end='$expire',
			text='$text',page_id='$page_id'";
		$new_message = $db->sql_query($new_message_query);
		if ($db->error[$new_message] === 1) {
			$content .= 'Failed to create page message.<br />';
		} else {
			$page_name = $db->sql_fetch_assoc($page_name_handle);
			$content .= 'Successfully created page message. '.log_action('Created page message for page \''.$page_name['title'].'\'');
		}
	} else {
		$content .= 'Failed to find the page which you are trying to add a message to.<br />';
	}
} else {
	if (!isset($_GET['page']) || $_GET['page'] == '') {
		$_GET['page'] = 1;
	}
	$content = '<form method="POST" action="admin.php?module=page_message_new&action=save">
		<h1>Create Page Message</h1>
		<table class="admintable">
		<input type="hidden" name="page_id" value="'.$_GET['page'].'" />
		<tr><td class="row1" valign="top">Content:</td><td class="row1"><textarea name="text" rows="30"></textarea></td></tr>
		<tr><td width="150" class="row2" valign="top">Date:</td><td class="row2">';
	$months = array('January','February','March','April','May','June','July',
		'August','September','October','November','December');
	$content .= 'Start:<br />';
	$content .= '<select name="start_month">';
	$monthcount = 1;
	while ($monthcount <= 12) {
		if(date('m') == $monthcount) {
			$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
			$monthcount++;
		} else {
			$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
			$monthcount++;
		}
	}
	$content .= '</select><input type="text" name="start_day" maxlength="2" size="2" value="'.date('d').'" /><input type="text" name="start_year" maxlength="4" size="4" value="'.date('Y').'" /><br />
		End:<br />';
	$content .= '<select name="end_month">';
	$monthcount = 1;
	while ($monthcount <= 12) {
		if(date('m') == $monthcount) {
			$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
			$monthcount++;
		} else {
			$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
			$monthcount++;
		}
	}
	$content .= '</select><input type="text" name="end_day" maxlength="2" size="2" value="'.date('d').'" /><input type="text" name="end_year" maxlength="4" size="4" value="'.date('Y').'" /></td></tr>
		<tr><td width="150" class="row1">Expire:</td><td class="row1"><input type="checkbox" name="expire" /></td></tr>
		<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
		</table>';
}
?>