<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

function article_url_nopage($id) {
	if (!is_numeric($id)) {
		return '#';
	}

	return 'view.php?article_id='.$id;
}

function article_url_onpage($id) {
	global $db;
	if (!is_numeric($id)) {
		return '#';
	}

	$page_query = 'SELECT `page` FROM `'.NEWS_TABLE.'`
		WHERE `id` = '.$id;
	$page_handle = $db->sql_query($page_query);
	if ($db->sql_num_rows($page_handle) == 0) {
		return '#';
	}
	$page_result = $db->sql_fetch_assoc($page_handle);
	return 'index.php?id='.$page_result['page'].'&amp;article='.$id.'#article-'.$id;
}

function article_url_ownpage($id) {
	if (!is_numeric($id)) {
		return '#';
	}

	return 'index.php?showarticle='.$id;
}
?>