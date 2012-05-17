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

/**
 * Change the content of a page message record
 * @global acl $acl
 * @global db $db
 * @param integer $id
 * @param integer $page
 * @param string $content
 * @param date $start
 * @param date $end
 * @param boolean $expire
 * @throws Exception 
 */
function pagemessage_edit($id,$page,$content,$start,$end,$expire) {
	global $acl;
	global $db;

	if (!$acl->check_permission('adm_page_message_edit'))
		throw new Exception('You are not allowed to edit page messages.');
	
	// Sanitize inputs
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('An invalid page message id was given.');
	$page = (int)$page;
	if ($page < 1)
		throw new Exception('An invalid page was given.');
	$content = $db->sql_escape_string($content);
	if (!preg_match('/[0-9]+\-[0-9]+\-[0-9]+/', $start)
			|| !preg_match('/[0-9]+\-[0-9]+\-[0-9]+/', $end))
		throw new Exception('An invalid start or end date was given.');
	$expire = ($expire === true) ? 1 : 0;

	// Load info of page
	$page_name_query = 'SELECT `title` FROM `'.PAGE_TABLE.'`
		WHERE `id` = '.$page.' LIMIT 1';
	$page_name_handle = $db->sql_query($page_name_query);
	if ($db->error[$page_name_handle] === 1)
		throw new Exception('An error occurred while reading page information from the database.');
	if ($db->sql_num_rows($page_name_handle) != 1)
		throw new Exception('The page you are trying to edit a page message from does not exist.');

	// Update page message record
	$edit_article_query = 'UPDATE `'.PAGE_MESSAGE_TABLE."`
		SET start_date='$start',end_date='$end',end='$expire',text='$content'
		WHERE message_id = $id";
	$edit_article = $db->sql_query($edit_article_query);
	if ($db->error[$edit_article] === 1)
		throw new Exception('An error occurred when updating the page message record.');

	$page_name = $db->sql_fetch_assoc($page_name_handle);
	Log::addMessage('Edited page message for page \''.$page_name['title'].'\'');
}

global $acl;
if (!$acl->check_permission('adm_page_message_edit'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

if ($_GET['action'] == 'edit') {
	try {
		$_POST['start_year'] = (isset($_POST['start_year'])) ? $_POST['start_year'] : 0;
		$_POST['start_month'] = (isset($_POST['start_month'])) ? $_POST['start_month'] : 0;
		$_POST['start_day'] = (isset($_POST['start_day'])) ? $_POST['start_day'] : 0;
		$_POST['end_year'] = (isset($_POST['end_year'])) ? $_POST['end_year'] : 0;
		$_POST['end_month'] = (isset($_POST['end_month'])) ? $_POST['end_month'] : 0;
		$_POST['end_day'] = (isset($_POST['end_day'])) ? $_POST['end_day'] : 0;
		$start_date = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
		$end_date = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
		$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
		pagemessage_edit($_POST['id'], $_POST['page_id'],
				$_POST['update_content'], $start_date, $end_date, (boolean)$expire);
		echo 'Successfully edited page message.<br />';
	}
	catch (Exception $e) {
		echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
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
		echo 'Start:<br />';
		$smonth = $start_date[1] - 1;
		echo '<select name="start_month" value="'.$smonth.'" disabled>';
		$mcount = 1;
		for ($monthcount = 0; $monthcount < 12; $monthcount++) {
			if ($start_date[1] == $monthcount) {
				echo '<option value="'.$mcount.'" selected >'
					.$months[$monthcount].'</option>';
			} else {
				echo '<option value="'.$mcount.'">'
					.$months[$monthcount].'</option>';
			}
			$mcount++;
		}
		echo '</select>
			<input type="text" name="start_day" maxlength="2" size="2" value="'.$start_date[2].'" disabled />
			<input type="text" name="start_year" maxlength="4" size="4" value="'.$end_date[0].'" disabled /><br />
			End:<br />';
		$emonth = $end_date[1] - 1;
		echo '<select name="end_month" value="'.$emonth.'" disabled>';
		$mcount = 1;
		for ($monthcount = 0; $monthcount < 12; $monthcount++) {
			if ($start_date[1] == $monthcount) {
				echo '<option value="'.$mcount.'" selected >'
					.$months[$monthcount].'</option>';
			} else {
				echo '<option value="'.$mcount.'">'
					.$months[$monthcount].'</option>';
			}
			$mcount++;
		}
		$expire_checked = checkbox($page_message['end'], 1);
		echo '</select>
			<input type="text" name="end_day" maxlength="2" size="2" value="'.$end_date[2].'" disabled />
			<input type="text" name="end_year" maxlength="4" size="4" value="'.$end_date[0].'" disabled /></td></tr>
			<tr><td width="150" class="row1">Expire:</td><td class="row1">
			<input type="checkbox" name="expire" '.$expire_checked.' disabled /></td></tr>
			<tr><td width="150" class="row2">&nbsp;</td><td class="row2">
			<input type="submit" value="Submit" /></td></tr>
			</table>';
	} else {
		echo 'Unable to find requested page message.';
	}
}
?>