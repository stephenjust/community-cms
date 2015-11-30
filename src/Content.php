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
     * Create a content record
     * @param string $title
     * @param string $content
     * @param integer $page
     * @param string $author
     * @param string $image
     * @param boolean $publish
     * @param boolean $showdate
     * @param string $deldate
     * @throws \Exception
     */
    public static function create($title,$content,$page,$author,$image,$publish,$showdate,$deldate)
    {
        acl::get()->require_permission('news_create');

        $query = "INSERT INTO `".NEWS_TABLE."` "
            . "(`page`,`name`,`description`,`author`,`image`,`date`,`showdate`,`publish`,`delete_date`) "
            . "VALUES "
            . "(:page, :name, :description, :author, :image, :date, :showdate, :publish, :delete_date)";
        try {
            DBConn::get()->query($query, [
                ":page" => (($page != 0) ? $page : null),
                ":name" => HTML::schars($title),
                ":description" => StringUtils::removeComments($content),
                ":author" => HTML::schars($author),
                ":image" => HTML::schars($image),
                ":date" => DATE_TIME,
                ":showdate" => $showdate,
                ":publish" => ((acl::get()->check_permission('news_publish')) ?
                    $publish : SysConfig::get()->getValue('news_default_publish_value')),
                ":delete_date" => ((strlen($deldate) == 10) ?
                    DateTime::createFromFormat('m/d/Y', $deldate)->format('Y-m-d') : null)
            ]);

            if ($page != 0) {
                $page_title = PageUtil::getTitle($page);
            } else {
                $page_title = "No Page";
            }
            Log::addMessage("Content '$title' added to '$page_title'");
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to create content.", $ex->getCode(), $ex);
        }
    }

    /**
     * Edit a content record
     * @param integer $id
     * @param string $title
     * @param string $content
     * @param integer $page
     * @param string $author
     * @param string $image
     * @param boolean $publish
     * @param boolean $showdate
     * @param string $deldate
     * @throws \Exception
     */
    public static function edit($id, $title,$content,$page,$image,$showdate,$deldate)
    {
        acl::get()->require_permission('news_edit');

        $query = "UPDATE `".NEWS_TABLE."` "
            . "SET `name` = :name, `description` = :description, `page` = :page, "
            . "`image` = :image, `date_edited` = :date, `showdate` = :showdate, "
            . "`delete_date` = :delete_date WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [
                ":id" => $id,
                ":page" => (($page != 0) ? $page : null),
                ":name" => HTML::schars($title),
                ":description" => StringUtils::removeComments($content),
                ":image" => HTML::schars($image),
                ":date" => DATE_TIME,
                ":showdate" => $showdate,
                ":delete_date" => ((strlen($deldate) == 10) ?
                    DateTime::createFromFormat('m/d/Y', $deldate)->format('Y-m-d') : null)
            ]);

            Log::addMessage("Edited content '$title'");
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to edit content.", $ex->getCode(), $ex);
        }
    }

    /**
     * Get all of the content items on a page
     * @param int $page_id
     * @param int $start
     * @param int $num
     * @return \Content
     */
    public static function getByPage($page_id, $start = 0, $num = 0, $only_published = false) 
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($page_id == "*") {
            $query = sprintf(
                'SELECT `id` FROM `%s` '
                . '%s ORDER BY `priority` DESC, `date` DESC, `id` DESC %s %s',
                NEWS_TABLE, ($only_published) ? 'WHERE `publish` = 1' : null,
                ($num) ? 'LIMIT '.$num : null, ($start) ? 'OFFSET '.$start : null
            );
            $args = [];
        } else {
            $query = sprintf(
                'SELECT `id` FROM `%s` '
                . 'WHERE `page` = :page %s ORDER BY `priority` DESC, `date` DESC, `id` DESC %s %s',
                NEWS_TABLE, ($only_published) ? 'AND `publish` = 1' : null,
                ($num) ? 'LIMIT '.$num : null, ($start) ? 'OFFSET '.$start : null
            );
            $args = [":page" => $page_id];
        }

        $results = DBConn::get()->query($query, $args, DBConn::FETCH_ALL);
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

    public static function savePriorities(array $priorities)
    {
        try {
            foreach($priorities AS $key => $value) {
                $query = 'UPDATE `'.NEWS_TABLE.'` '
                    . 'SET `priority` = :priority '
                    . 'WHERE `id` = :id';
                DBConn::get()->query($query, [':priority' => $value, ':id' => $key]);
            }
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to save priorities.", $ex->getCode(), $ex);
        }
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

    /**
     * Delete the content item
     * @throws \Exception
     */
    public function delete()
    {
        acl::get()->require_permission('news_delete');

        $query = 'DELETE FROM `' . NEWS_TABLE . '` WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $this->id]);
            Log::addMessage("Deleted content '{$this->title}' ({$this->id})");
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to delete content.", $ex->getCode(), $ex);
        }
    }

    /**
     * Move this content to a different page
     * @param integer $target
     * @throws \Exception
     */
    public function move($target)
    {
        $query = 'UPDATE `' . NEWS_TABLE . '` SET `page` = :page WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":page" => (($target != 0) ? $target : null), ":id" => $this->id]);
            $this->page_id = $target;
            Log::addMessage("Moved content '{$this->title}' ({$this->id})");
        } catch (Exception $ex) {
            throw new \Exception("Failed to move content.", $ex->getCode(), $ex);
        }
    }

    /**
     * Duplicate this content to a different page
     * @param integer $target
     */
    public function copy($target)
    {
        self::create($this->title, $this->content, (($target != 0) ? $target : null), $this->author,
            $this->image, $this->publish, $this->show_date, $this->delete_date);
    }

    public function publish($publish)
    {
        if ($publish != $this->published()) {
            $query = "UPDATE `".NEWS_TABLE."` SET `publish` = :publish WHERE `id` = :id";
            try {
                DBConn::get()->query($query, [":id" => $this->id, ":publish" => $publish]);
                $this->publish = $publish;
            } catch (Exceptions\DBException $ex) {
                throw new \Exception("Failed to edit content.");
            }
        }
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
