<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

/**
 * Class to handle News objects
 *
 * @author Stephen
 */
class News
{
    private $id;
    private $page;
    private $priority;
    private $author;
    private $date;
    private $date_edited;
    private $title;
    private $content;
    private $image;
    private $showdate;
    private $published;
    private $delete_date;
    
    /**
     * Get the IDs of all news articles on a given page
     * @param mixed $page Integer page id, '*' or NULL
     * @return array
     */
    public static function getByPage($page) 
    {
        try {
            if ($page === 0 || $page === '0' || $page === null) {
                $page = null;
                $query = 'SELECT `id` FROM `'.NEWS_TABLE.'`
					WHERE `page` is NULL
					ORDER BY `priority` DESC, `id` DESC';
            } elseif ($page === '*') {
                $query = 'SELECT `id` FROM `'.NEWS_TABLE.'`
					ORDER BY `priority` DESC, `id` DESC';
            } else {
                $query = 'SELECT `id` FROM `'.NEWS_TABLE.'`
					WHERE `page` = :page_id
					ORDER BY `priority` DESC, `id` DESC';
            }
            $result = DBConn::get()->query(
                $query,
                array(':page_id' => $page), DBConn::FETCH_ALL
            );
            $return = array();
            foreach ($result AS $row) {
                $return[] = $row['id'];
            }
            return $return;
        } catch (Exceptions\DBException $e) {
            Debug::get()->addMessage('Database error while getting news articles: '.$e->getMessage(), true);
            return array();
        }
    }
    
    /**
     * Get a news article
     * @param integer $id
     * @return null|\News
     */
    public static function get($id) 
    {
        try {
            $article = DBConn::get()->query(
                'SELECT * FROM `'.NEWS_TABLE.'`
					WHERE `id` = :id
					LIMIT 1',
                array(':id' => $id),
                DBConn::FETCH
            );
            if ($article === false) { return null; 
            }
            $instance = new News();
            $instance->populate($article);
            return $instance;
        } catch (Exceptions\DBException $e) {
            Debug::get()->addMessage('Failed to get article: '.$e, true);
            return null;
        }
    }
    
    /**
     * Populate class variables
     * @param array $data
     */
    private function populate($data) 
    {
        $this->id = $data['id'];
        $this->author = $data['author'];
        $this->content = $data['description'];
        $this->title = $data['name'];
        $this->page = $data['page'];
        $this->published = $data['publish'];
        $this->priority = $data['priority'];
    }
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getTitle() 
    {
        return $this->title;
    }
    
    public function getPriority() 
    {
        return $this->priority;
    }
    
    public function getPageTitle() 
    {
        if ($this->page === null) { return 'No Page'; 
        }
        $page_info = page_get_info($this->page, array('title'));
        return $page_info['title'];
    }
    
    public function isPublished() 
    {
        if ($this->published) { return true; 
        }
        return false;
    }
}

?>
