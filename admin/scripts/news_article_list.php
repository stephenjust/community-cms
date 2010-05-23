<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
define('ADMIN',1);
define('SECURITY',1);
define('ROOT','../../');

include (ROOT . 'config.php');
include (ROOT . 'include.php');
include (ROOT . 'functions/admin.php');

initialize('ajax');

if (!$acl->check_permission('adm_news') || !checkuser_admin()) {
	die ('You do not have the necessary permissions to access this page.');
}
if (!isset($_GET['page'])) {
	die ('No page ID provided to script.');
} else {
	$page_id = $_GET['page'];
	if ($page_id != '*') {
		$page_id = (int)$page_id;
	}
}

// Get article list
if (!is_numeric($page_id)) {
    $article_list_query = 'SELECT * FROM `' . NEWS_TABLE . '` ORDER BY `id` DESC';
} else {
    $article_list_query = 'SELECT * FROM `' . NEWS_TABLE . '`
		WHERE `page` = '.$page_id.' ORDER BY `priority` DESC, `id` DESC';
}
$article_list_handle = $db->sql_query($article_list_query);
$article_list_rows = $db->sql_num_rows($article_list_handle);
$list_rows = array();
for ($i = 1; $i <= $article_list_rows; $i++) {
    $article_list = $db->sql_fetch_assoc($article_list_handle);
	$current_row = array();
	$current_row[] = '<input type="checkbox" name="item_'.$article_list['id'].'" />';
	$current_row[] = $article_list['id'];
	$current_row[] = stripslashes($article_list['name']);
	$current_row[] = '<a href="?module=news&amp;action=delete&amp;id='
		.$article_list['id'].'&amp;page='.$page_id.'">'
		.'<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" '
		.'height="16px" border="0px" /></a>';
	$current_row[] = '<a href="?module=news_edit_article&amp;id='
		.$article_list['id'].'"><img src="./admin/templates/default/images/edit.png" '
		.'alt="Edit" width="16px" height="16px" border="0px" /></a>';
	$current_row[] = '<input type="text" size="3" maxlength="11" name="pri-'.$article_list['id'].'" value="'.$article_list['priority'].'" />';
	$list_rows[] = $current_row;
} // FOR

$content = create_table(array('','ID','Title','Delete','Edit','Priority'),$list_rows);
$content .= '<input type="hidden" name="page" value="'.$page_id.'" />';

echo $content;

clean_up();
?>