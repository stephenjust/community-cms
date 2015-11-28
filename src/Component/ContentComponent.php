<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\Content;
use CommunityCMS\File;
use CommunityCMS\HTML;
use CommunityCMS\SysConfig;
use CommunityCMS\Template;
use CommunityCMS\Component\EditBarComponent;

/**
 * Component to display news articles
 */
class ContentComponent extends BaseComponent
{
    private $content;

    public function setContent(Content $content)
    {
        assert($content->getID());
        $this->content = $content;
    }

    public function render()
    {
        $template_article = new Template;
        $template_article->loadFile('article');
        if (!empty($this->content->getImage())) {
            $im_file = new File($this->content->getImage());
            $im_info = $im_file->getInfo();
            $template_article->article_image = HTML::image('./files/'.$this->content->getImage(), $im_info['label'], 'news_image');
        } else {
            $template_article->article_image = null;
        }

        if ($this->content->isDateVisible()) {
            $template_article->full_date_start = null;
            $template_article->full_date_end = null;
        } else {
            $template_article->replaceRange('full_date', null);
        }

        // Edit bar permission check
        $editbar = new EditBarComponent;
        $editbar->setLabel('Article');
        $editbar->addControl(
            'admin.php?module=news&action=edit&amp;id='.$this->content->getID(),
            'edit.png',
            'Edit',
            array('news_edit','adm_news','admin_access')
        );

        // Get current url
        $query_string = preg_replace('/\&(amp;)?(login|(un)?publish)=[0-9]+/i', null, $_SERVER['QUERY_STRING']);
        if ($this->content->published()) {
            // Currently published
            $editbar->addControl(
                'index.php?'.$query_string.'&unpublish='.$this->content->getID(),
                'unpublish.png', 'Unpublish', array('news_publish')
            );
        } else {
            // Currently unpublished
            $editbar->addControl(
                'index.php?'.$query_string.'&publish='.$this->content->getID(),
                'publish.png', 'Publish', array('news_publish')
            );
        }
        $template_article->edit_bar = $editbar->render();

        $article_title = $this->content->getTitle();
        if (!$this->content->published()) {
            $article_title .= ' <span class="news_not_published_label">NOT PUBLISHED</span>';
        }

        $date = strtotime($this->content->getDate());
        $template_article->article_title = '<a href="index.php?showarticle='.$this->content->getID().'">'.$article_title.'</a>';
        $template_article->article_title_nolink = $this->content->getTitle();
        $template_article->article_content = $this->content->getContent();
        $template_article->article_id = $this->content->getID();
        $template_article->article_date_month = date('m', $date);
        $template_article->article_date_month_text = strtoupper(date('M', $date));
        $template_article->article_date_day = date('j', $date);
        $template_article->article_date_year = date('Y', $date);
        $template_article->article_date = date('d-m-Y', $date);
        if (SysConfig::get()->getValue('news_show_author') == 0) {
            $template_article->replaceRange('article_author', null);
        } else {
            $template_article->article_author = $this->content->getAuthor();
        }

        // Remove info div entirely if author and date are hidden
        if (!SysConfig::get()->getValue('news_show_author') && !$this->content->isDateVisible()) {
            $template_article->replaceRange('article_details', null);
        } else {
            $template_article->article_details_start = null;
            $template_article->article_details_end = null;
        }
        return (string) $template_article;
    }
}
