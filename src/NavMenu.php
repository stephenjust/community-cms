<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class NavMenu
{
    private $id = 0;
    private $current = 0;
    private $label;
    private $url;

    public function __construct($current_id = 0, $item = 0) 
    {
        $this->id = $item;
        $this->current = $current_id;
        if (!$item) {
            return;
        }
        $record = DBConn::get()->query(
            sprintf(
                'SELECT * FROM `%s` '
                . 'WHERE `id` = :id '
                . 'AND `hidden` = 0', PAGE_TABLE
            ),
            array(':id' => $item), DBConn::FETCH
        );
        if(!$record) {
            throw new \Exception('Failed to load menu item.');
        }
        
        if ($record['type'] != 0) {
            $this->label = $record['title'];
            if (strlen($record['text_id'])) {
                $this->url = sprintf('index.php?page=%s', $record['text_id']);
            } else {
                $this->url = sprintf('index.php?id=%d', $record['id']);
            }
        } else {
            // Page links
            $link = explode('<LINK>', $record['title']);
            $this->label = $link[0];
            $this->url = $link[1];
        }
    }

    public function getID() 
    {
        return $this->id;
    }

    public function getChildren() 
    {
        $children_ids = Page::getChildren($this->id);
        $children = array();
        foreach ($children_ids as $child_id) {
            $children[] = new NavMenu($this->current, $child_id);
        }
        return $children;
    }
    
    public function getLabel() 
    {
        assert($this->id);
        return $this->label;
    }
    
    public function getTarget() 
    {
        assert($this->id);
        return $this->url;
    }
    
    public function isCurrent() 
    {
        return $this->current == $this->id;
    }
}
