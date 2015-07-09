<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2014-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\EditBarComponent;
use CommunityCMS\Exceptions\ContentNotFoundException;

class Content
{
    private $id = 0;
    private $page_id;
    private $title;
    private $content;
    private $author;
    private $date;
    private $date_edited;
    private $image;
    private $publish;
    private $priority;
    private $show_date;
    private $delete_date;
    
    /**
     * Get all of the content items on a page
     * @param int $page_id
     * @param int $start
     * @param int $num
     * @return \Content
     */
    public static function getByPage($page_id, $start = 0, $num = 0, $only_published = false) 
    {
        if ($start < 0) { $start = 0; 
        }
        $query = sprintf(
            'SELECT `id` FROM `%s` '
            . 'WHERE `page` = :page %s ORDER BY `priority` DESC, `date` DESC, `id` DESC %s %s',
            NEWS_TABLE, ($only_published) ? 'AND `publish` = 1' : null,
            ($num) ? 'LIMIT '.$num : null, ($start) ? 'OFFSET '.$start : null
        );
        $results = DBConn::get()->query(
            $query,
            array(':page' => $page_id), DBConn::FETCH_ALL
        );
        $items = array();
        foreach ($results AS $result) {
            $items[] = new Content($result['id']);
        }
        return $items;
    }
    
    public static function getPublishedByPage($page_id, $start = 0, $num = 0) 
    {
        return Content::getByPage($page_id, $start, $num, true);
    }
    
    public static function getContentIDsByPage($page_id, $only_published = false) 
    {
        $query = sprintf(
            'SELECT `id` FROM `%s` '
            . 'WHERE `page` = :page %s ORDER BY `priority` DESC, `date` DESC, `id` DESC',
            NEWS_TABLE, ($only_published) ? 'AND `publish` = 1' : null
        );
        $results = DBConn::get()->query(
            $query,
            array(':page' => $page_id), DBConn::FETCH_ALL
        );
        $items = array();
        foreach ($results AS $result) {
            $items[] = $result['id'];
        }
        return $items;
    }
    
    public function __construct($id) 
    {
        $result = DBConn::get()->query(
            sprintf('SELECT * FROM `%s` WHERE `id` = :id', NEWS_TABLE),
            array(':id' => $id), DBConn::FETCH
        );
        if (!$result) {
            throw new ContentNotFoundException('Content not found');
        }
        $this->id = $id;
        $this->page_id = $result['page'];
        $this->title = $result['name'];
        $this->content = $result['description'];
        $this->author = $result['author'];
        $this->date = $result['date'];
        $this->date_edited = $result['date_edited'];
        $this->image = $result['image'];
        $this->publish = $result['publish'];
        $this->priority = $result['priority'];
        $this->show_date = $result['showdate'];
        $this->delete_date = $result['delete_date'];
    }
    
    public function getEditBar() 
    {
        $editbar = new EditBarComponent();
        $editbar->setLabel('Article');
        $editbar->addControl(
            'admin.php?module=news&action=edit&amp;id='.$this->getID(),
            'edit.png',
            'Edit',
            array('news_edit','adm_news','admin_access')
        );

        // Get current url
        $query_string = preg_replace('/\&(amp;)?(login|(un)?publish)=[0-9]+/i', null, $_SERVER['QUERY_STRING']);
        if ($this->published()) {
            // Currently published
            $editbar->addControl(
                'index.php?'.$query_string.'&unpublish='.$this->getID(),
                'unpublish.png', 'Unpublish', array('news_publish')
            );
        } else {
            // Currently unpublished
            $editbar->addControl(
                'index.php?'.$query_string.'&publish='.$this->getID(),
                'publish.png', 'Publish', array('news_publish')
            );
        }
        return $editbar->render();
    }
    
    /**
     * Get content ID
     * @return int
     */
    public function getID() 
    {
        return $this->id;
    }
    
    /**
     * Get page ID
     * @return int
     */
    public function getPage() 
    {
        return $this->page_id;
    }
    
    /**
     * Get content title
     * @return string
     */
    public function getTitle() 
    {
        return $this->title;
    }
    
    /**
     * Get content body
     * @return string
     */
    public function getContent() 
    {
        return $this->content;
    }
    
    /**
     * Get content author
     * @return string
     */
    public function getAuthor() 
    {
        return $this->author;
    }
    
    /**
     * Get content image
     * @return string 
     */
    public function getImage() 
    {
        return str_replace('./files/', null, $this->image);
    }
    
    /**
     * Get content's creation date
     * @return string
     */
    public function getDate() 
    {
        return $this->date;
    }
    
    /**
     * True if the item is published
     * @return boolean
     */
    public function published() 
    {
        return (boolean) $this->publish;
    }

    /**
     * Get the priority of the item
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
    
    /**
     * True if the date should be visible
     * @return boolean
     */
    public function isDateVisible() 
    {
        return (bool) $this->show_date;
    }
    
    public function isAuthorVisible() 
    {
        return SysConfig::get()->getValue('news_show_author');
    }
}
