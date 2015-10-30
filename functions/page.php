<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Generate a page list at a certain level in the page structure
 * @global db $db Database connection object
 * @param integer $parent       Parent item of (sub)menu
 * @param boolean $visible_only Only list pages that will appear on menu
 * @return mixed False on fail, array of pages on success
 */
function page_list($parent = 0, $visible_only = false) 
{
    global $db;

    if (!is_numeric($parent) || is_array($parent)) {
        return false;
    }
    $parent = (int)$parent;

    $visible = null;
    if ($visible_only == true) {
        $visible = 'AND `menu` = 1 ';
    }

    $query = 'SELECT * FROM `'.PAGE_TABLE.'`
		WHERE `parent` = 0 '.$visible.'ORDER BY `list` ASC';
    $handle = $db->sql_query($query);
    if ($db->error[$handle] === 1) {
        return false;
    }
    if ($db->sql_num_rows($handle) == 0) {
        return false;
    }

    $page_list = array();

    for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
        $result = $db->sql_fetch_assoc($handle);

        $page_list[$i] = $result;
        $page_list[$i]['has_children'] = Page::hasChildren($page_list[$i]['id'], $visible_only);
    }
    return $page_list;
}
