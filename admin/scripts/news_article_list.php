<?php
/**
 * Community CMS
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
    $article_list_query = 'SELECT * FROM `'.NEWS_TABLE.'` ORDER BY `id` DESC';
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
	$article_title = stripslashes($article_list['name']);
	if ($article_list['publish'] == 0) {
		$article_title .= ' (Not published)';
	}
	$current_row[] = $article_title;
	unset($article_title);

	// If the view is "All Pages", we can't easily see what page each article
	// is on, so we'll fetch the title of the page each article is on.
	if (!is_numeric($page_id)) {
		if($article_list['page'] == 0) {
			$current_row[] = 'No Page';
		} else {
			// Get page title
			$article_page_query = 'SELECT `page`.`title` FROM 
				`'.PAGE_TABLE.'` `page`, `'.NEWS_TABLE.'` `news`
				WHERE `news`.`id` = '.$article_list['id'].'
				AND `news`.`page` = `page`.`id`';
			$article_page_handle = $db->sql_query($article_page_query);
			if ($db->error[$article_page_handle] === 1) {
				$current_row[] = '<span class="errormessage">Error</span>';
			} elseif ($db->sql_num_rows($article_page_handle) == 0) {
				$current_row[] = 'Unknown Page';
			} else {
				$article_page = $db->sql_fetch_assoc($article_page_handle);
				$current_row[] = stripslashes($article_page['title']);
			}
		}
	}

	if ($acl->check_permission('news_delete')) {
		$current_row[] = '<a href="?module=news&amp;action=delete&amp;id='
			.$article_list['id'].'&amp;page='.$page_id.'">'
			.'<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" '
			.'height="16px" border="0px" /></a>';
	}
	if ($acl->check_permission('news_edit')) {
		$current_row[] = '<a href="?module=news&amp;action=edit&amp;id='
			.$article_list['id'].'"><img src="./admin/templates/default/images/edit.png" '
			.'alt="Edit" width="16px" height="16px" border="0px" /></a>';
	}
	if ($acl->check_permission('news_publish')) {
		if ($article_list['publish'] == 1) {
			$current_row[] = '<a href="?module=news&amp;action=unpublish&amp;id='.$article_list['id'].'&amp;page='.$page_id.'">Unpublish</a>';
		} else {
			$current_row[] = '<a href="?module=news&amp;action=publish&amp;id='.$article_list['id'].'&amp;page='.$page_id.'">Publish</a>';
		}
	}
	$current_row[] = '<input type="text" size="3" maxlength="11" name="pri-'.$article_list['id'].'" value="'.$article_list['priority'].'" />';
	$list_rows[] = $current_row;
} // FOR

$label_array = array('','ID','Title');

// Add "Page" column when in "All Pages" view
if (!is_numeric($page_id)) {
	$label_array[] = 'Page';
}

if ($acl->check_permission('news_delete')) {
	$label_array[] = 'Delete';
}
if ($acl->check_permission('news_edit')) {
	$label_array[] = 'Edit';
}
if ($acl->check_permission('news_publish')) {
	$label_array[] = 'Publish';
}
$label_array[] = 'Priority';
$content = create_table($label_array,$list_rows);
$content .= '<input type="hidden" name="page" value="'.$page_id.'" />';

echo $content;

clean_up();
?>