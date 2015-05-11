<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2014-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

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
        $pagination = new Component\PaginationComponent();
        $pagination->setCurrentPage($this->start, SysConfig::get()->getValue("news_num_articles"), count($this->content_ids));
        $tpl = new Smarty();
        $tpl->assign('page', $this);
        $tpl->assign('articles', $this->articles);
        $tpl->assign('pagination', $pagination->render());
        return $tpl->fetch('newsContent.tpl');
    }
    
    private function publish() 
    {
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
    }
    
    private function getArticles() 
    {
        if (acl::get()->check_permission('news_fe_show_unpublished')) {
            return Content::getByPage($this->id, $this->start-1, (int)SysConfig::get()->getValue('news_num_articles'));
        } else {
            return Content::getPublishedByPage($this->id, $this->start-1, (int)SysConfig::get()->getValue('news_num_articles'));
        }
    }
}
