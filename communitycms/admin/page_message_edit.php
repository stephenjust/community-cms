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

if (!$acl->check_permission('adm_page_message_edit')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;
if ($_GET['action'] == 'edit') {
	$page_name_query = 'SELECT * FROM ' . PAGE_TABLE . '
		WHERE id = '.$_POST['page_id'].' LIMIT 1';
	$page_name_handle = $db->sql_query($page_name_query);
	if ($db->error[$page_name_handle] === 1) {
		$content .= 'Failed to read name of current page for log message.<br />';
	}
	if ($db->sql_num_rows($page_name_handle) == 1) {
		$edit_id = (int)$_POST['id'];
		$_POST['start_year'] = (isset($_POST['start_year'])) ? $_POST['start_year'] : 0;
		$_POST['start_month'] = (isset($_POST['start_month'])) ? $_POST['start_month'] : 0;
		$_POST['start_day'] = (isset($_POST['start_day'])) ? $_POST['start_day'] : 0;
		$_POST['end_year'] = (isset($_POST['end_year'])) ? $_POST['end_year'] : 0;
		$_POST['end_month'] = (isset($_POST['end_month'])) ? $_POST['end_month'] : 0;
		$_POST['end_day'] = (isset($_POST['end_day'])) ? $_POST['end_day'] : 0;
		$start_date = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
		$end_date = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
		$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
		$text = addslashes($_POST['update_content']);
		$edit_article_query = 'UPDATE ' . PAGE_MESSAGE_TABLE . "
			SET start_date='$start_date',end_date='$end_date',end='$expire',text='$text' WHERE message_id = $edit_id";
		$edit_article = $db->sql_query($edit_article_query);
		if ($db->error[$edit_article] === 1) {
			$content .= 'Failed to edit page message.<br />';
		} else {
			$page_name = $db->sql_fetch_assoc($page_name_handle);
			$content .= 'Successfully edited page message. '.log_action('Edited page message for page \''.$page_name['title'].'\'');
		}
	} else {
		$content .= 'Failed to find the page which you are trying to edit the message of.';
	}
} else {
	if (!isset($_GET['id']) || $_GET['id'] == '') {
		$_GET['id'] = 0;
	}
	$page_message_query = 'SELECT * FROM ' . PAGE_MESSAGE_TABLE . '
		WHERE message_id = '.(int)$_GET['id'].' LIMIT 1';
	$page_message_handle = $db->sql_query($page_message_query);
	if($db->sql_num_rows($page_message_handle) == 1) {
		$page_message = $db->sql_fetch_assoc($page_message_handle);
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
		$smonth = $start_date[1] - 1;
		$content .= '<select name="start_month" value="'.$smonth.'" disabled>';
		$mcount = 1;
		for ($monthcount = 0; $monthcount < 12; $monthcount++) {
			if ($start_date[1] == $monthcount) {
				$content .= '<option value="'.$mcount.'" selected >'
					.$months[$monthcount].'</option>';
			} else {
				$content .= '<option value="'.$mcount.'">'
					.$months[$monthcount].'</option>';
			}
			$mcount++;
		}
		$content .= '</select>
			<input type="text" name="start_day" maxlength="2" size="2" value="'.$start_date[2].'" disabled />
			<input type="text" name="start_year" maxlength="4" size="4" value="'.$end_date[0].'" disabled /><br />
			End:<br />';
		$emonth = $end_date[1] - 1;
		$content .= '<select name="end_month" value="'.$emonth.'" disabled>';
		$mcount = 1;
		for ($monthcount = 0; $monthcount < 12; $monthcount++) {
			if ($start_date[1] == $monthcount) {
				$content .= '<option value="'.$mcount.'" selected >'
					.$months[$monthcount].'</option>';
			} else {
				$content .= '<option value="'.$mcount.'">'
					.$months[$monthcount].'</option>';
			}
			$mcount++;
		}
		$expire_checked = checkbox($page_message['end'], 1);
		$content .= '</select>
			<input type="text" name="end_day" maxlength="2" size="2" value="'.$end_date[2].'" disabled />
			<input type="text" name="end_year" maxlength="4" size="4" value="'.$end_date[0].'" disabled /></td></tr>
			<tr><td width="150" class="row1">Expire:</td><td class="row1">
			<input type="checkbox" name="expire" '.$expire_checked.' disabled /></td></tr>
			<tr><td width="150" class="row2">&nbsp;</td><td class="row2">
			<input type="submit" value="Submit" /></td></tr>
			</table>';
	} else {
		$content = 'Unable to find requested page message.';
	}
}
?>