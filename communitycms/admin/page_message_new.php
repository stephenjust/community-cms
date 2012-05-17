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

require_once(ROOT.'functions/pagemessage.php');

global $acl;
if (!$acl->check_permission('page_message_new'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

if ($_GET['action'] == 'save') {
	try {
		$_POST['start_year'] = (isset($_POST['start_year'])) ? $_POST['start_year'] : 0;
		$_POST['start_month'] = (isset($_POST['start_month'])) ? $_POST['start_month'] : 0;
		$_POST['start_day'] = (isset($_POST['start_day'])) ? $_POST['start_day'] : 0;
		$_POST['end_year'] = (isset($_POST['end_year'])) ? $_POST['end_year'] : 0;
		$_POST['end_month'] = (isset($_POST['end_month'])) ? $_POST['end_month'] : 0;
		$_POST['end_day'] = (isset($_POST['end_day'])) ? $_POST['end_day'] : 0;
		$start = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
		$end = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
		$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
		pagemessage_create($_POST['page_id'],
				$_POST['text'], $start, $end, (boolean)$expire);
		echo 'Successfully created page message.<br />';
		echo '<a href="admin.php?module=page_message&amp;page='.$_POST['page_id'].'">
			Return to previous page</a><br />';
	}
	catch (Exception $e) {
		echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
	}
} else {
	if (!isset($_POST['page']) || $_POST['page'] == '') {
		$_POST['page'] = 1;
	}
	$form = new form;
	$form->set_target('admin.php?module=page_message_new&amp;action=save');
	$form->set_method('post');
	$form->add_hidden('page_id',(int)$_POST['page']);
	$form->add_textarea('text','Content',NULL,'rows="30"');
	$form->add_date('start','Start Date','MDY',NULL,"disabled");
	$form->add_date('end','End Date','MDY',NULL,"disabled");
	$form->add_checkbox('expire','Expire',NULL,"disabled");
	$form->add_submit('submit','Save');
	echo '<h1>Create New Page Message</h1>'.$form;
}
?>