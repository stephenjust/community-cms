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
if(!isset($_GET['start']) || $_GET['start'] == "" || (int)$_GET['start'] < 1) {
	$_GET['start'] = 1;
}
$start = (int)$_GET['start'];
$start_offset = $start - 1;
$first_date = NULL;
$news_query = 'SELECT `id` FROM `' . NEWS_TABLE . '`
	WHERE `page` = '.$page->id.' ORDER BY `date`,`id` DESC
	LIMIT '.$news_config['num_articles'].' OFFSET '.$start_offset.'';
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

// Get array of content IDs for pagination
$news_id_query = 'SELECT `id` FROM `' . NEWS_TABLE .'`
	WHERE `page` = '.$page->id.'
	ORDER BY `date`,`id` DESC';
$news_id_handle = $db->sql_query($news_id_query);
if ($db->error[$news_id_handle] === 1) {
	$debug->add_trace('Failed to read array of content IDs from database',true,'news.php');
	$return .= 'Failed to retreive information necessary for pagination.<br />';
}
$content_id_array = array();
for ($i = 0; $i < $db->sql_num_rows($news_id_handle); $i++) {
	$news_id = $db->sql_fetch_assoc($news_id_handle);
	$content_id_array[] = $news_id['id'];
}
unset($news_id_query);
unset($news_id_handle);
// Paginate
include(ROOT . 'includes/pagination.php');
$return .= pagination($start,(int)$news_config['num_articles'],$content_id_array);

return $return;
?>