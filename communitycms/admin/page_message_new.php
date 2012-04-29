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

if (!$acl->check_permission('page_message_new')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

/**
 * Create a new page message record
 * @global acl $acl
 * @global db $db
 * @param inetger $page
 * @param string $content
 * @param date $start
 * @param date $end
 * @param boolean $expire
 * @throws Exception 
 */
function pagemessage_create($page,$content,$start,$end,$expire) {
	global $acl;
	global $db;
	
	if (!$acl->check_permission('page_message_new'))
		throw new Exception('You are not allowed to create new page messages.');

	// Sanitize inputs
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
		throw new Exception('The page you are trying to create a page message for does not exist.');

	// Create page message record
	$new_message_query = 'INSERT INTO ' . PAGE_MESSAGE_TABLE . "
			SET start_date='$start',end_date='$end',end='$expire',
			text='$content',page_id='$page',`order`='0'";
	$new_handle = $db->sql_query($new_message_query);
	if ($db->error[$new_handle] === 1)
		throw new Exception('An error occurred when creating the page message record.');

	$page_name = $db->sql_fetch_assoc($page_name_handle);
	Log::addMessage('Created page message for page \''.$page_name['title'].'\'');
}

$content = NULL;
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
		$content .= 'Successfully created page message.<br />';
		$content .= '<a href="admin.php?module=page_message&amp;page='.$_POST['page_id'].'">
			Return to previous page</a><br />';
	}
	catch (Exception $e) {
		$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
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