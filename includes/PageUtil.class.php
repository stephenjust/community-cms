<?php

/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

require_once ROOT.'includes/DBConn.class.php';

class PageUtil
{
    /**
     * Check whether the given page exists
     * @param int $id Page ID
     * @return boolean
     */
    public static function exists($id) 
    {
        return DBConn::get()->query(
            sprintf('SELECT `id` FROM `%s` WHERE `id` = :id', PAGE_TABLE),
            array(':id' => $id), DBConn::ROW_COUNT
        ) > 0;
    }
    
    /**
     * Get the title of the given page
     * @param int $id Page ID
     * @return string
     */
    public static function getTitle($id) 
    {
        $result = DBConn::get()->query(
            sprintf('SELECT `title` FROM `%s` WHERE `id` = :id', PAGE_TABLE),
            array(':id' => $id), DBConn::FETCH
        );
        return $result['title'];
    }
}
