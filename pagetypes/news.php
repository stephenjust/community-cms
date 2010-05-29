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

/**
 * news_get_config - Get news module configuration from the database
 * @global object $db Database Connection Object
 * @return array
 */
function news_get_config() {
	global $db;

	$query = 'SELECT * FROM ' . NEWS_CONFIG_TABLE . ' LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		die('Could not load news settings.<br />');
		return array();
	} elseif ($db->sql_num_rows($handle) == 0) {
		die('There are no news settings available.<br />');
		return array();
	}
	$news_config = $db->sql_fetch_assoc($handle);
	return $news_config;
}

global $page;
global $db;
global $debug;
$return = NULL;

$news_config = news_get_config();

// Check for display mode
if (isset($_GET['article'])) {
	// We want to display the given article on the page
	// Make sure a valid article ID is passed
	if (!is_numeric($_GET['article']) || strlen($_GET['article']) == 0) {
		$debug->add_trace('Article ID not numeric',true,'news.php');
		header("HTTP/1.0 404 Not Found");
		$page->notification = 'The requested article does not exist.<br />'."\n";
		$page->title = 'Article not found';
		$page->showtitle = false;
		return $return.' ';
	}
	$article_id = (int)$_GET['article'];
	$article_page_query = 'SELECT `page` FROM `'.NEWS_TABLE.'`
		WHERE `id` = '.$article_id.' LIMIT 1';
	$article_page_handle = $db->sql_query($article_page_query);
	if ($db->error[$article_page_handle] === 1) {
		$debug->add_trace('Failed to look up article\'s page in the database',true,'news.php');
		header("HTTP/1.0 404 Not Found");
		$page->notification = 'An error occurred when trying to retrieve the requested article.<br />'."\n";
		$page->title = 'Error';
		$page->showtitle = false;
		return $return.' ';
	}
	if ($db->sql_num_rows($article_page_handle) != 1) {
		$debug->add_trace('Article does not exist',true,'news.php');
		header("HTTP/1.0 404 Not Found");
		$page->notification = 'The requested article does not exist.<br />'."\n";
		$page->title = 'Article not found';
		$page->showtitle = false;
		return $return.' ';
	} else {
		// Article exists
		// TODO: Make sure we're on the right page - redirect if not
	}
}

include(ROOT.'pagetypes/news_class.php');
if(!isset($_GET['start']) || $_GET['start'] == "" || (int)$_GET['start'] < 1) {
	$_GET['start'] = 1;
}
$start = (int)$_GET['start'];
$start_offset = $start - 1;
$first_date = NULL;
$news_query = 'SELECT `id` FROM `' . NEWS_TABLE . '`
	WHERE `page` = '.$page->id.' ORDER BY `priority` DESC, `date` DESC, `id` DESC
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