<?php

/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

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

    public static function textIdExists($text_id)
    {
        if ($text_id == null) {
            return false;
        }
        return DBConn::get()->query(
            sprintf('SELECT `text_id` FROM `%s` WHERE `text_id` = :id', PAGE_TABLE),
            array(':id' => $text_id), DBConn::ROW_COUNT
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

    /**
     * Ensure that all pages are in a regular order
     * @throws \Exception
     */
    public static function cleanOrder()
    {
        $query = 'SELECT `id`, `parent`, `list` FROM `'.PAGE_TABLE.'` '
            . 'ORDER BY `parent` ASC, `list` ASC';
        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to reorder pages.", $ex->getCode(), $ex);
        }

        $parent = 0;
        $count = 0;
        foreach ($results as $result) {
            if ($result['parent'] != $parent) {
                $parent = $result['parent'];
                $count = 0;
            }
            self::setPageOrder($result['id'], $count);
            $count++;
        }
    }

    private static function setPageOrder($id, $order)
    {
        $query = 'UPDATE `'.PAGE_TABLE.'` '
            . 'SET `list` = :list '
            . 'WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $id, ":list" => $order]);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception("Failed to reorder pages.", $ex->getCode(), $ex);
        }
    }
}
