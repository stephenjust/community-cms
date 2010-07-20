<?php
/**
 * Community CMS
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

function get_article_list($page,$start = 1) {
	global $acl;
	global $db;
	global $debug;

	if (!isset($page)) {
		$debug->add_trace('No page ID provided',true,'get_article_list()');
		return array();
	}
	if (!is_numeric($start)) {
		$start = 1;
		$debug->add_trace('Non-numeric offset - reverting to 1',true,'get_article_list()');
	}
	$start = (int)($start - 1);
	$page = (int)$page;

	$first_date = NULL;
	if ($acl->check_permission('news_fe_show_unpublished')) {
		$query = 'SELECT `id` FROM `' . NEWS_TABLE . '`
			WHERE `page` = '.$page.'
			ORDER BY `priority` DESC, `date` DESC, `id` DESC
			LIMIT '.get_config('news_num_articles').' OFFSET '.$start.'';
	} else {
		$query = 'SELECT `id` FROM `' . NEWS_TABLE . '`
			WHERE `page` = '.$page.'
			AND `publish` = 1
			ORDER BY `priority` DESC, `date` DESC, `id` DESC
			LIMIT '.get_config('news_num_articles').' OFFSET '.$start.'';
	}
	$handle = $db->sql_query($query);

	if ($db->sql_num_rows($handle) == 0) {
		return array();
	}

	$article_list = array();
	for ($i = 1; $i <= $db->sql_num_rows($handle); $i++) {
		$news = $db->sql_fetch_assoc($handle);
		$article_list[] = (int)$news['id'];
	}
	return $article_list;
}

global $acl;
global $db;
global $debug;
global $page;
$return = NULL;

// Handle first article offset value
if(!isset($_GET['start']) || $_GET['start'] == "" || (int)$_GET['start'] < 1) {
	$_GET['start'] = 1;
}
$start = (int)$_GET['start'];

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
	if ($acl->check_permission('news_fe_show_unpublished')) {
		$article_page_query = 'SELECT `page`
			FROM `'.NEWS_TABLE.'`
			WHERE `id` = '.$article_id.'
			LIMIT 1';
	} else {
		$article_page_query = 'SELECT `page`
			FROM `'.NEWS_TABLE.'`
			WHERE `id` = '.$article_id.'
			AND `publish` = 1
			LIMIT 1';
	}
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
		$article_found = false;
		while ($article_found == false && $start < 1000) {
			$article_list = get_article_list($page->id,$start);
			for ($i = 0; $i < count($article_list); $i++) {
				if ($article_list[$i] == $article_id) {
					$article_found = true;
				}
			}
			if ($article_found == false) {
				$start = $start + get_config('news_num_articles');
			}
		}
		if ($start >= 1000) {
			$debug->add_trace('Gave up looking for article',true,'news.php');
			header("HTTP/1.0 404 Not Found");
			$page->notification = 'The requested article could not be found.<br />'."\n";
			$page->title = 'Article not found';
			$page->showtitle = false;
			return $return.' ';
		}
	}
}

include(ROOT.'pagetypes/news_class.php');
$article_list = get_article_list($page->id,$start);
$first_date = NULL;
// Initialize session variable if not initialized to prevent warnings.
if (!isset($_SESSION['user'])) {
	$_SESSION['user'] = NULL;
}
if (count($article_list) == 0) {
	$return .= 'There are no articles to be displayed.';
	return $return;
}

for ($i = 0; $i < count($article_list); $i++) {
	$article = new news_item;
	$article->set_article_id($article_list[$i]);
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
$return .= pagination($start,get_config('news_num_articles'),$content_id_array);

return $return;
?>