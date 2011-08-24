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

if (!$acl->check_permission('page_message_new')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
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
		$_POST['start_year'] = (isset($_POST['start_year'])) ? $_POST['start_year'] : 0;
		$_POST['start_month'] = (isset($_POST['start_month'])) ? $_POST['start_month'] : 0;
		$_POST['start_day'] = (isset($_POST['start_day'])) ? $_POST['start_day'] : 0;
		$_POST['end_year'] = (isset($_POST['end_year'])) ? $_POST['end_year'] : 0;
		$_POST['end_month'] = (isset($_POST['end_month'])) ? $_POST['end_month'] : 0;
		$_POST['end_day'] = (isset($_POST['end_day'])) ? $_POST['end_day'] : 0;

		$start_date = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
		$end_date = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
		$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
		$text = addslashes($_POST['text']);
		$new_message_query = 'INSERT INTO ' . PAGE_MESSAGE_TABLE . "
			SET start_date='$start_date',end_date='$end_date',end='$expire',
			text='$text',page_id='$page_id',`order`='0'";
		$new_message = $db->sql_query($new_message_query);
		if ($db->error[$new_message] === 1) {
			$content .= 'Failed to create page message.<br />';
		} else {
			$page_name = $db->sql_fetch_assoc($page_name_handle);
			$content .= 'Successfully created page message.<br />';
			Log::addMessage('Created page message for page \''.$page_name['title'].'\'');
			$content .= '<a href="admin.php?module=page_message&amp;page='.$page_id.'">
				Return to previous page</a><br />';
		}
	} else {
		$content .= 'Failed to find the page which you are trying to add a message to.<br />';
	}
} else {
	if (!isset($_POST['page']) || $_POST['page'] == '') {
		$_POST['page'] = 1;
	}
	$content = NULL;
	$form = new form;
	$form->set_target('admin.php?module=page_message_new&amp;action=save');
	$form->set_method('post');
	$form->add_hidden('page_id',(int)$_POST['page']);
	$form->add_textarea('text','Content',NULL,'rows="30"');
	$form->add_date('start','Start Date','MDY',NULL,"disabled");
	$form->add_date('end','End Date','MDY',NULL,"disabled");
	$form->add_checkbox('expire','Expire',NULL,"disabled");
	$form->add_submit('submit','Save');
	$content .= '<h1>Create New Page Message</h1>'.$form;
}
?>