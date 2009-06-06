<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

global $page;
global $db;
$return = NULL;

// Load configuration
$news_config_query = 'SELECT * FROM ' . NEWS_CONFIG_TABLE . ' LIMIT 1';
$news_config_handle = $db->sql_query($news_config_query);
if ($db->error[$news_config_handle] === 1) {
	$page->notification .= 'Could not load news settings.<br />';
	return $return;
} elseif ($db->sql_num_rows($news_config_handle) == 0) {
	$page->notification .= 'There are no news settings available.<br />';
	return $return;
}
$news_config = $db->sql_fetch_assoc($news_config_handle);

include(ROOT.'pagetypes/news_class.php');
if(!isset($_GET['start']) || $_GET['start'] == "" || $_GET['start'] < 0) {
	$_GET['start'] = 0;
}
$start = (int)$_GET['start'];
$first_date = NULL;
$news_query = 'SELECT `id` FROM `' . NEWS_TABLE . '`
	WHERE `page` = '.$page->id.' ORDER BY `date`,`id` DESC
	LIMIT '.$news_config['num_articles'].' OFFSET '.$start.'';
$news_handle = $db->sql_query($news_query);
// Initialize session variable if not initialized to prevent warnings.
if (!isset($_SESSION['user'])) {
	$_SESSION['user'] = NULL;
}
if ($db->sql_num_rows($news_handle) == 0) {
	$return .= 'There are no articles to be displayed.';
	return $return;
}

for ($i = 1; $i <= $db->sql_num_rows($news_handle); $i++) {
	$news = $db->sql_fetch_assoc($news_handle);
	$article = new news_item;
	$article->set_article_id((int)$news['id']);
	$article->get_article();
	$last_article_date = $article->date;
	$return .= $article."\n\n";
	unset($article);
}


// FIXME: Pagination does not work.
$last_article_date = 0;
$news_first_query = 'SELECT `id`,`date` FROM `' . NEWS_TABLE . '`
	WHERE `page` = '.$page->id.' ORDER BY `date`,`id` DESC LIMIT 1';
$news_first_handle = $db->sql_query($news_first_query);
$news_first = $db->sql_fetch_assoc($news_first_handle);
$news_last_query = 'SELECT `id`,`date` FROM `' . NEWS_TABLE . '`
	WHERE `page` = '.$page->id.' ORDER BY `date`,`id` ASC LIMIT 1';
$news_last_handle = $db->sql_query($news_last_query);
$news_last = $db->sql_fetch_assoc($news_last_handle);
$template_pagination = new template;
$template_pagination->load_file('pagination');
if($news_first['date'] != $first_date && isset($first_date)) {
	$prev_start = $start - $news_config['num_articles'];
	$template_pagination->prev_page = '<a href="index.php?id='.$id.'&start='.$prev_start.'" class="prev_page" id="prev_page">Previous Page</a>';
} else {
	$template_pagination->prev_page = '';
}
if($news_last['date'] != $last_article_date && $last_article_date != NULL) {
	$next_start = $start + $news_config['num_articles'];
	$template_pagination->next_page = '<a href="index.php?id='.$id.'&start='.$next_start.'" class="prev_page" id="prev_page">Next Page</a>';
} else {
	$template_pagination->next_page = '';
}
$return .= $template_pagination;
unset($template_pagination);
return $return;
?>