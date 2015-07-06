<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component.Block
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2009-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component\Block;

use CommunityCMS\CalCategory;
use CommunityCMS\DBConn;
use CommunityCMS\Tpl;
use CommunityCMS\Exceptions\DBException;

/**
 * Block component that displays a list of calendar categories
 */
class CalendarCategoryBlockComponent extends BlockComponent
{
    public function getType()
    {
        return "calendarcategories";
    }

    public function render()
    {
        $categories = array();
        $query = "SELECT `cat_id` AS `id` FROM `".CALENDAR_CATEGORY_TABLE."`";
        try {
            $results = DBConn::get()->query($query, null, DBConn::FETCH_ALL);
            foreach ($results as $result) {
                $categories[] = new CalCategory($result['id']);
            }
        } catch (DBException $ex) {
            throw new \Exception("Failed to load calendar categories", $ex);
        }

        $tpl = new Tpl();
        $tpl->assign('categories', $categories);
        return $tpl->fetch("calCategoriesBlock.tpl");
    }
}