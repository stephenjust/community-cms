<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;
require_once ROOT.'includes/pagination.php';

class NewsPage extends Page
{
    private $start;
    private $articles;
    private $content_ids;

    public function __construct($page_id) 
    {
        parent::__construct($page_id);
        $this->id = $page_id;
        $this->start = (empty($_GET['start'])) ? 1 : $_GET['start'];
        $this->articles = $this->getArticles();
        $this->content_ids = Content::getContentIdsByPage($page_id, !acl::get()->check_permission('news_fe_show_unpublished'));
        $this->publish();
    }
    
    public function getContent() 
    {
        $tpl = new Smarty();
        $tpl->assign('page', $this);
        $tpl->assign('articles', $this->articles);
        $tpl->assign('pagination', pagination($this->start, get_config('news_num_articles'), $this->content_ids));
        return $tpl->fetch('newsContent.tpl');
    }
    
    private function publish() 
    {
        if (acl::get()->check_permission('news_publish')) {
            include_once ROOT . 'functions/news.php';
            include_once ROOT . 'functions/admin.php';
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
    }
    
    private function getArticles() 
    {
        if (acl::get()->check_permission('news_fe_show_unpublished')) {
            return Content::getByPage($this->id, $this->start-1, (int)get_config('news_num_articles'));
        } else {
            return Content::getPublishedByPage($this->id, $this->start-1, (int)get_config('news_num_articles'));
        }
    }
}
