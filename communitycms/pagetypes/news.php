<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

require_once(ROOT.'includes/acl/acl.php');
require_once(ROOT.'includes/Content.class.php');
require_once(ROOT.'includes/DBConn.class.php');
require_once(ROOT.'pagetypes/news_class.php');

function get_article_list($page, $start = 1) {
	assert(is_numeric($page), 'Invalid page ID');
	assert(is_numeric($start), 'Invalid starting value');

	if (acl::get()->check_permission('news_fe_show_unpublished')) {
		return Content::getByPage($page, $start-1, (int)get_config('news_num_articles'));
	} else {
		return Content::getPublishedByPage($page, $start-1, (int)get_config('news_num_articles'));
	}
}

global $db;
global $debug;
$return = NULL;

// (Un)publish articles on request
if (acl::get()->check_permission('news_publish')) {
	require_once(ROOT . 'functions/news.php');
	require_once(ROOT . 'functions/admin.php');
	if (isset($_GET['publish']) || isset($_GET['unpublish'])) {
		if (isset($_GET['publish'])) {
			$publish = (int)$_GET['publish'];
			news_publish($publish,true);
		} elseif (isset($_GET['unpublish'])) {
			$publish = (int)$_GET['unpublish'];
			news_publish($publish,false);
		}
	}
}

// Handle first article offset value
$start = (empty($_GET['start'])) ? 1 : $_GET['start'];

// Check for display mode
if (isset($_GET['article'])) {
	try {
		$c = new Content($_GET['article']);
		if (!$c->published() && !acl::get()->check_permission('news_fe_show_unpublished')) {
			throw new ContentNotFoundException();
		}
		if ($c->getPage() != Page::$id) {
			throw new ContentNotFoundException();
		}
	} catch (ContentNotFoundException $ex) {
		header("HTTP/1.0 404 Not Found");
		Page::$notification = 'The requested article does not exist.<br />'."\n";
		Page::$title = 'Article not found';
		Page::$showtitle = false;
		return $return.' ';
	}
	// FIXME: Replace $start with some estimation of where to start to ensure
	// the requested article is visible
	$article_list = get_article_list(Page::$id, $start);
} else {
	$article_list = get_article_list(Page::$id, $start);
}

if (count($article_list) == 0) {
	$return .= 'There are no articles to be displayed.';
	return $return;
}

foreach ($article_list AS $article_record) {
	$article = new news_item;
	$article->set_article_id($article_record->getID());
	$article->get_article();
	$last_article_date = $article->date;
	$return .= $article."\n\n";
	unset($article);
}

// Get array of content IDs for pagination
$content_id_array = Content::getContentIDsByPage(Page::$id, !acl::get()->check_permission('news_fe_show_unpublished'));
// Paginate
include(ROOT . 'includes/pagination.php');
$return .= pagination($start, get_config('news_num_articles'), $content_id_array);

return $return;
