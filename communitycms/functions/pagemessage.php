<?php

/**
 * Community CMS
 *
 * @copyright Copyright (C) 2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
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
?>
