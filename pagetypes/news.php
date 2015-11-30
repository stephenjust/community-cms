<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\ContentComponent;
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

$return = null;

// (Un)publish articles on request
if (acl::get()->check_permission('news_publish')) {
    if (FormUtil::get('publish')) {
        $c = new Content(FormUtil::get('publish'));
        $c->publish(true);
    }
    if (FormUtil::get('unpublish')) {
        $c = new Content(FormUtil::get('unpublish'));
        $c->publish(false);
    }
}

// Handle first article offset value
$start = FormUtil::get('start', FILTER_VALIDATE_INT, null, 0);

// Check for display mode
$showarticle = FormUtil::get('showarticle', FILTER_VALIDATE_INT, null);
$article = FormUtil::get('article', FILTER_VALIDATE_INT, null);
if ($showarticle) {
    Page::$showtitle = false;
    try {
        $c = new Content($showarticle);
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
    $content_id_array = array($showarticle);
    Page::$title = $c->getTitle();
} elseif ($article) {
    try {
        $c = new Content($article);
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
    $article_pos = array_search($article, $content_id_array);
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

    $cc = new ContentComponent();
    $cc->setContent($article_record);
    $return .= $cc->render()."\n\n";
}

$pagination = new Component\PaginationComponent();
$pagination->setCurrentPage($start, SysConfig::get()->getValue('news_num_articles'), count($content_id_array));
$return .= $pagination->render();

return $return;
