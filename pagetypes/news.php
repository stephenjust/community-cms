<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

use CommunityCMS\Component\EditBarComponent;
use CommunityCMS\Exceptions\ContentNotFoundException;

/**
 * @ignore
 */
if (!defined('SECURITY')) {
    exit;
}

function get_article_list($page, $start = 1) 
{
    assert(is_numeric($page), 'Invalid page ID');
    assert(is_numeric($start), 'Invalid starting value');

    if (acl::get()->check_permission('news_fe_show_unpublished')) {
        return Content::getByPage($page, $start, (int)SysConfig::get()->getValue('news_num_articles'));
    } else {
        return Content::getPublishedByPage($page, $start, (int)SysConfig::get()->getValue('news_num_articles'));
    }
}

function format_content(Content $content) 
{
    assert($content->getID());
    
    $template_article = new Template;
    $template_article->loadFile('article');
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
        $template_article->replaceRange('full_date', null);
    }

    // Edit bar permission check
    $editbar = new EditBarComponent;
    $editbar->setLabel('Article');
    $editbar->addControl(
        'admin.php?module=news&action=edit&amp;id='.$content->getID(),
        'edit.png',
        'Edit',
        array('news_edit','adm_news','admin_access')
    );

    // Get current url
    $query_string = preg_replace('/\&(amp;)?(login|(un)?publish)=[0-9]+/i', null, $_SERVER['QUERY_STRING']);
    if ($content->published()) {
        // Currently published
        $editbar->addControl(
            'index.php?'.$query_string.'&unpublish='.$content->getID(),
            'unpublish.png', 'Unpublish', array('news_publish')
        );
    } else {
        // Currently unpublished
        $editbar->addControl(
            'index.php?'.$query_string.'&publish='.$content->getID(),
            'publish.png', 'Publish', array('news_publish')
        );
    }
    $template_article->edit_bar = $editbar->render();

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
    if (SysConfig::get()->getValue('news_show_author') == 0) {
        $template_article->replaceRange('article_author', null);
    } else {
        $template_article->article_author = $content->getAuthor();
    }

    // Remove info div entirely if author and date are hidden
    if (!SysConfig::get()->getValue('news_show_author') && !$content->isDateVisible()) {
        $template_article->replaceRange('article_details', null);
    } else {
        $template_article->article_details_start = null;
        $template_article->article_details_end = null;
    }
    return (string) $template_article;
}

$return = null;

// (Un)publish articles on request
if (acl::get()->check_permission('news_publish')) {
    include_once ROOT . 'functions/news.php';
    if (isset($_GET['publish']) || isset($_GET['unpublish'])) {
        if (isset($_GET['publish'])) {
            $publish = (int)$_GET['publish'];
            news_publish($publish, true);
        } elseif (isset($_GET['unpublish'])) {
            $publish = (int)$_GET['unpublish'];
            news_publish($publish, false);
        }
    }
}

// Handle first article offset value
$start = (empty($_GET['start'])) ? 0 : $_GET['start'];

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
    $content_id_array = Content::getContentIDsByPage(Page::$id, !acl::get()->check_permission('news_fe_show_unpublished'));
    $article_pos = array_search($_GET['article'], $content_id_array);
    $start = floor($article_pos / SysConfig::get()->getValue('news_num_articles')) * SysConfig::get()->getValue('news_num_articles');
    $article_list = get_article_list(Page::$id, $start);
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

$pagination = new Component\PaginationComponent();
$pagination->setCurrentPage($start, SysConfig::get()->getValue('news_num_articles'), count($content_id_array));
$return .= $pagination->render();

return $return;
