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
 * page_get_info - Get requested fields for a page entry in the database
 * @global db $db Database connection object
 * @param integer $id     Page ID
 * @param array   $fields Database fields to get, default is all
 * @return mixed Returns false on failure, associative array of row on success
 */
function page_get_info($id,$fields = array('*')) 
{
    global $db;
    // Validate parameters
    if (!is_numeric($id)) {
        Debug::get()->addMessage('ID is not an integer', true);
        return false;
    }
    if(!is_array($fields)) {
        Debug::get()->addMessage('Field list is not an array', true);
        return false;
    }
    // Add backticks to field names and ensure that there are no spaces in field names
    $field_count = count($fields);
    for ($i = 0; $i < $field_count; $i++) {
        if (preg_match('/ /', $fields[$i])) {
            Debug::get()->addMessage('Removed field "'.$fields[$i].'"', false);
            unset($fields[$i]);
            continue;
        }
        if ($fields[$i] != '*' && strlen($fields[$i]) > 0) {
            $fields[$i] = '`'.$fields[$i].'`';
        }
    }
    $fields = implode(',', $fields);
    $page_info_query = 'SELECT '.$fields.' FROM `' . PAGE_TABLE .'`
		WHERE `id` = '.$id.' LIMIT 1';
    $page_info_handle = $db->sql_query($page_info_query);
    if ($db->error[$page_info_handle] === 1) {
        Debug::get()->addMessage('Failure to read page information', true);
        return false;
    }
    if ($db->sql_num_rows($page_info_handle) != 1) {
        Debug::get()->addMessage('Page \''.$id.'\' not found', true);
        return false;
    }
    $page_info = $db->sql_fetch_assoc($page_info_handle);
    return $page_info;
}

/**
 * Check if a new text ID is unique
 * @global db $db
 * @param string $text_id
 * @return boolean 
 */
function page_check_unique_id($text_id) 
{
    global $db;

    if (strlen($text_id) == 0) {
        Debug::get()->addMessage('Text ID is empty', true);
        return false;
    }
    $text_id_query = 'SELECT * FROM `'.PAGE_TABLE.'`
		WHERE `text_id` = \''.$text_id.'\' LIMIT 1';
    $text_id_handle = $db->sql_query($text_id_query);
    if ($db->sql_num_rows($text_id_handle) == 1) {
        return false;
    }
    return true;
}

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

/**
 * Move a page up the list in the site structure
 * @global db $db Database connection object
 * @param integer $id Page ID to move up
 * @return boolean
 */
function page_move_up($id) 
{
    global $db;

    if (!acl::get()->check_permission('page_order')) {
        return false;
    }
    if (!is_numeric($id) || is_array($id)) {
        return false;
    }
    $id = (int)$id;

    $page_info = page_get_info($id, array('id','list','parent'));
    if (!$page_info) {
        return false;
    }

    $start_pos = $page_info['list'];
    $end_pos = $page_info['list'] - 1;
    $move_down_query1 = 'SELECT id,list FROM ' . PAGE_TABLE . "
		WHERE `list` = $end_pos
		AND `parent` = {$page_info['parent']} LIMIT 1";
    $move_down1 = $db->sql_query($move_down_query1);
    if ($db->sql_num_rows($move_down1) != 1) {
        return false;
    }
    $move_down_handle1 = $db->sql_fetch_assoc($move_down1);
    $move_up_query2 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$end_pos.' WHERE id = '.$page_info['id'];
    $move_up_query3 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$start_pos.' WHERE id = '.$move_down_handle1['id'];
    $move_up_handle2 = $db->sql_query($move_up_query2);
    $move_up_handle3 = $db->sql_query($move_up_query3);
    if ($db->error[$move_up_handle2] === 1 || $db->error[$move_up_handle3] === 1) {
        return false;
    }
    return true;
}

/**
 * Move a page down the list in the site structure
 * @global db $db Database connection object
 * @param integer $id Page ID to move down
 * @return boolean
 */
function page_move_down($id) 
{
    global $db;

    if (!acl::get()->check_permission('page_order')) {
        return false;
    }
    if (!is_numeric($id) || is_array($id)) {
        return false;
    }
    $id = (int)$id;

    $page_info = page_get_info($id, array('id','list','parent'));
    if (!$page_info) {
        return false;
    }

    $start_pos = $page_info['list'];
    $end_pos = $page_info['list'] + 1;
    $move_up_query1 = "SELECT id,list FROM " . PAGE_TABLE . "
		WHERE `list` = $end_pos
		AND `parent` = {$page_info['parent']} LIMIT 1";
    $move_up1 = $db->sql_query($move_up_query1);
    if ($db->sql_num_rows($move_up1) != 1) {
        return false;
    }
    $move_up_handle1 = $db->sql_fetch_assoc($move_up1);
    $move_down_query2 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$end_pos.' WHERE id = '.$page_info['id'];
    $move_down_query3 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$start_pos.' WHERE id = '.$move_up_handle1['id'];
    $move_down_handle2 = $db->sql_query($move_down_query2);
    $move_down_handle3 = $db->sql_query($move_down_query3);
    if ($db->error[$move_down_handle2] === 1 || $db->error[$move_down_handle3] === 1) {
        return false;
    }
    return true;
}
