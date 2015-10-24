<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class UISelectPageList extends UISelect
{
    private $pagetype;
    
    protected function parse_params($params) 
    {
        if (array_key_exists('pagetype', $params)) {
            $this->pagetype = $params['pagetype']; 
        }
    }
    
    protected function preload() 
    {
        if (!$this->pagetype) {
            $query = 'SELECT `id`, `title` FROM `'.PAGE_TABLE.'` ORDER BY `title` ASC';
        } else {
            $query = 'SELECT `id`, `title` FROM `'.PAGE_TABLE.'` WHERE `type` = :type ORDER BY `title` ASC';
        }
        try {
            $results = DBConn::get()->query($query, [":type" => $this->pagetype], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception('Error reading page list.');
        }
        foreach ($results as $result) {
            $this->addOption($result['id'], $result['title']);
        }
    }
}
