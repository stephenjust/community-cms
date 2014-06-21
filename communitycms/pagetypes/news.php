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

function get_article_list($page, $start = 1) {
	assert(is_numeric($page), 'Invalid page ID');
	assert(is_numeric($start), 'Invalid starting value');

	if (acl::get()->check_permission('news_fe_show_unpublished')) {
		return Content::getByPage($page, $start-1, (int)get_config('news_num_articles'));
	} else {
		return Content::getPublishedByPage($page, $start-1, (int)get_config('news_num_articles'));
	}
}

function format_content(Content $content) {
	assert($content->getID());
	
	$template_article = new template;
	$template_article->load_file('article');
	if (!empty($content->getImage())) {
		$im_file = new File($content->getImage());
		$im_info = $im_file->getInfo();
		$template_article->article_image = HTML::image('./files/'.$content->getImage(), $im_info['label'], 'news_image');
	} else {
		$template_article->article_image = null;
	}

	if ($content->isDateVisible()) {
		$template_article->full_date_start = null;
		$template_article->full_date_end = null;
	} else {
		$template_article->replace_range('full_date', null);
	}

	// Edit bar permission check
	$editbar = new editbar;
	$editbar->set_label('Article');
	$page_group_id = page_group_news($content->getID());
	if (!acl::get()->check_permission('pagegroupedit-'.$page_group_id)) {
		$editbar->visible = false;
	}
	$editbar->add_control('admin.php?module=news&action=edit&amp;id='.$content->getID(),
			'edit.png',
			'Edit',
			array('news_edit','adm_news','admin_access'));

	// Get current url
	$query_string = preg_replace('/\&(amp;)?(login|(un)?publish)=[0-9]+/i', null, $_SERVER['QUERY_STRING']);
	if ($content->published()) {
		// Currently published
		$editbar->add_control('index.php?'.$query_string.'&unpublish='.$content->getID(),
				'unpublish.png','Unpublish',array('news_publish'));
	} else {
		// Currently unpublished
		$editbar->add_control('index.php?'.$query_string.'&publish='.$content->getID(),
			'publish.png','Publish',array('news_publish'));
	}
	$template_article->edit_bar = $editbar;

	$article_title = $content->getTitle();
	if (!$content->published()) {
		$article_title .= ' <span class="news_not_published_label">NOT PUBLISHED</span>';
	}

	$date = strtotime($content->getDate());
	$template_article->article_title = '<a href="index.php?showarticle='.$content->getID().'">'.$article_title.'</a>';
	$template_article->article_title_nolink = $content->getTitle();
	$template_article->article_content = $content->getContent();
	$template_article->article_id = $content->getID();
	$template_article->article_date_month = date('m', $date);
	$template_article->article_date_month_text = strtoupper(date('M', $date));
	$template_article->article_date_day = date('j', $date);
	$template_article->article_date_year = date('Y', $date);
	$template_article->article_date = date('d-m-Y', $date);
	if (get_config('news_show_author') == 0) {
		$template_article->replace_range('article_author', null);
	} else {
		$template_article->article_author = $content->getAuthor();
	}

	// Remove info div entirely if author and date are hidden
	if (!get_config('news_show_author') && !$content->isDateVisible()) {
		$template_article->replace_range('article_details', null);
	} else {
		$template_article->article_details_start = null;
		$template_article->article_details_end = null;
	}

	$template_article->replace_variable('article_url_onpage','article_url_onpage($a);');
	$template_article->replace_variable('article_url_ownpage','article_url_ownpage($a);');
	$template_article->replace_variable('article_url_nopage','article_url_nopage($a);');

	return (string) $template_article;
}

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
if (isset($_GET['showarticle'])) {
	Page::$showtitle = false;
	try {
		$c = new Content($_GET['showarticle']);
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
		Page::$exists = false;
		return $return.' ';
	}
	$article_list = array($c);
	$content_id_array = array($_GET['showarticle']);
	Page::$title = $c->getTitle();
} elseif (isset($_GET['article'])) {
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
	$article_pos = array_search($_GET['article'], $content_id_array);
	$start = floor($article_pos / get_config('news_num_articles')) * get_config('news_num_articles') + 1;
	$article_list = get_article_list(Page::$id, $start);
	$content_id_array = Content::getContentIDsByPage(Page::$id, !acl::get()->check_permission('news_fe_show_unpublished'));
} else {
	$article_list = get_article_list(Page::$id, $start);
	$content_id_array = Content::getContentIDsByPage(Page::$id, !acl::get()->check_permission('news_fe_show_unpublished'));
}

if (count($article_list) == 0) {
	$return .= 'There are no articles to be displayed.';
	return $return;
}

foreach ($article_list AS $article_record) {
	$return .= format_content($article_record)."\n\n";
}

// Paginate
include(ROOT . 'includes/pagination.php');
$return .= pagination($start, get_config('news_num_articles'), $content_id_array);

return $return;
