<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */
namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * get_block - Get contents of a block
 * @global acl $acl
 * @global db $db
 * @param int $block_id ID of block to display
 * @return string
 */
function get_block($block_id = null) 
{
    $block_id = (int)$block_id;
    if(strlen($block_id) < 1 || $block_id <= 0) {
        return;
    }
    global $acl;
    global $db;
    $block_content = null;
    $block_query = 'SELECT * FROM ' . BLOCK_TABLE . '
		WHERE id = '.$block_id.' LIMIT 1';
    $block_handle = $db->sql_query($block_query);
    if ($db->error[$block_handle] === 0) {
        if ($db->sql_num_rows($block_handle) == 1) {
            $block_info = $db->sql_fetch_assoc($block_handle);
            $block_content .= include ROOT.'content_blocks/'.$block_info['type'].'_block.php';
        } else {
            if ($acl->check_permission('show_fe_errors')) {
                $block_content .= '<div class="notification"><strong>Error:</strong> Could not load block '.$block_id.'.</div>';
            }
        }
    }
    return $block_content;
}

/**
 * Create a new block record
 * @global acl $acl
 * @global db $db
 * @param string $type
 * @param string $attributes Comma separated list
 * @throws \Exception 
 */
function block_create($type, $attributes) 
{
    global $acl;
    global $db;

    if (!$acl->check_permission('block_create')) {
        throw new \Exception('You are not allowed to create blocks.'); 
    }

    // Sanitize inputs
    $type = $db->sql_escape_string($type);
    if (strlen($type) == 0) {
        throw new \Exception('Invalid block type.'); 
    }
    $attributes = explode(',', $attributes);
    $attb_count = count($attributes);

    // Construct attribute string
    $attributes_final = array();
    for ($i = 0; $i < $attb_count; $i++) {
        if ($attributes[$i] == null) { continue; 
        }
        if (!isset($_POST[$attributes[$i]])) { $_POST[$attributes[$i]] = null; 
        }
        $attributes_final[] = $attributes[$i].'='.$_POST[$attributes[$i]];
    }
    $attb_string = $db->sql_escape_string(implode(',', $attributes_final));
    
    // Create record
    $query = 'INSERT INTO `'.BLOCK_TABLE."`
		(`type`,`attributes`)
		VALUES
		('$type','$attb_string')";
    $handle = $db->sql_query($query);
    if($db->error[$handle] === 1) {
        throw new \Exception('An error occurred while creating the block.'); 
    }

    Log::addMessage('Created block \''.stripslashes($type).' ('.stripslashes($attb_string).')\'');
}

/**
 * Edit a block entry
 * @global acl $acl
 * @global db $db
 * @param integer $id         Block ID
 * @param string  $attributes Comma separated list
 * @throws \Exception 
 */
function block_edit($id,$attributes) 
{
    global $acl;
    global $db;
    
    if (!$acl->check_permission('block_edit')) {
        throw new \Exception('You are not allowed to edit content blocks.'); 
    }

    // Validate inputs
    $id = (int)$id;
    if ($id < 1) {
        throw new \Exception('Invalid block ID.'); 
    }
    $attributes = explode(',', $attributes);
    $attb_count = count($attributes);

    // Generate a string of attributes
    $attributes_final = array();
    for ($i = 0; $i < $attb_count; $i++) {
        if ($attributes[$i] == null) { continue; 
        }
        if (!isset($_POST[$attributes[$i]])) { $_POST[$attributes[$i]] = null; 
        }
        $attributes_final[] = $attributes[$i].'='.$_POST[$attributes[$i]];
    }
    $attb_string = $db->sql_escape_string(implode(',', $attributes_final));

    // Update the block record
    $query = 'UPDATE `'.BLOCK_TABLE."`
		SET `attributes` = '$attb_string'
		WHERE `id` = $id";
    $handle = $db->sql_query($query);
    if($db->error[$handle] === 1) {
        throw new \Exception('An error occurred while editing the block.'); 
    }
    Log::addMessage('Edited block \''.$id.' ('.stripslashes($attb_string).')\'');
}

/**
 * Delete a block record
 * @global acl $acl Permission object
 * @global db $db Database connection object
 * @param integer $id Block ID
 * @throws \Exception
 */
function block_delete($id) 
{
    global $acl;
    global $db;

    if (!$acl->check_permission('block_delete')) {
        throw new \Exception('You are not allowed to delete blocks.'); 
    }

    // Sanitize inputs
    $id = (int)$id;
    if ($id < 1) {
        throw new \Exception('Invalid block ID.'); 
    }

    // Check that block exists
    $block_exists_query = 'SELECT `type`,`attributes`
		FROM `'.BLOCK_TABLE.'`
		WHERE `id` = '.$id.'
		LIMIT 1';
    $block_exists_handle = $db->sql_query($block_exists_query);
    if($db->error[$block_exists_handle] === 1) {
        throw new \Exception('An error occurred while checking if the block exists.'); 
    }
    if ($db->sql_num_rows($block_exists_handle) === 0) {
        throw new \Exception('The block you are trying to delete does not exist.'); 
    }

    // Delete the block record
    $delete_block_query = 'DELETE FROM `' . BLOCK_TABLE . '`
		WHERE `id` = '.$id;
    $delete_block = $db->sql_query($delete_block_query);
    if (!$db->error[$delete_block] === 1) {
        throw new \Exception('An error occurred while deleting the block.'); 
    }
    
    $block_exists = $db->sql_fetch_assoc($block_exists_handle);
    Log::addMessage('Deleted block \''.$block_exists['type'].' ('.$block_exists['attributes'].')\'');
}

/**
 * Generate the form for block management
 * @global db $db
 * @global Debug $debug
 * @param string $type Block type
 * @param array  $vars Array of parameters to set as form defaults
 * @return string HTML for form (or false on failure)
 */
function block_edit_form($type,$vars = array()) 
{
    global $db;
    global $debug;

    $return = null;
    if (!is_array($vars)) {
        $debug->addMessage('Invalid set of variables', true);
        return false;
    }
    switch ($type) {
    default:
        break;
    case 'text':
        if (!isset($vars['article_id'])) {
            $vars['article_id'] = 0;
        }
        if (!isset($vars['show_border'])) {
            $vars['show_border'] = 'yes';
        }
        $news_query = 'SELECT `news`.`name`, `news`.`id`, `news`.`page`, `page`.`title`
				FROM `'.NEWS_TABLE.'` `news`
				LEFT JOIN `'.PAGE_TABLE.'` `page`
				ON `news`.`page` = `page`.`id`
				ORDER BY `news`.`page` ASC, `news`.`name` ASC';
        $news_handle = $db->sql_query($news_query);
        if ($db->error[$news_handle] === 1) {
            $debug->addMessage('Failed to read news articles', true);
            return false;
        }
        $num_articles = $db->sql_num_rows($news_handle);
        if ($num_articles == 0) {
            return 'No articles exist.<br />'."\n";
        }
        $return .= 'News Article <select name="article_id">'."\n";
        for ($i = 1; $i <= $num_articles; $i++) {
            $news_result = $db->sql_fetch_assoc($news_handle);
            if ($news_result['title'] == null && $news_result['page'] == 0) {
                $news_result['title'] = 'No Page';
            } elseif ($news_result['title'] == null && $news_result['page'] != 0) {
                $news_result['title'] = 'Unknown Page';
            }
            if ($vars['article_id'] == $news_result['id']) {
                $return .= "\t".'<option value="'.$news_result['id'].'" selected>'.$news_result['title'].' - '.$news_result['name'].'</option>'."\n";
            } else {
                $return .= "\t".'<option value="'.$news_result['id'].'">'.$news_result['title'].' - '.$news_result['name'].'</option>'."\n";
            }
        }
        $return .= '</select><br />'."\n";
        $return .= 'Show Border <select name="show_border">'."\n";
        if ($vars['show_border'] == 'yes') {
            $return .= "\t".'<option value="yes" selected>Yes</option>'."\n".
            "\t".'<option value="no">No</option>'."\n";
        } else {
            $return .= "\t".'<option value="yes">Yes</option>'."\n".
            "\t".'<option value="no" selected>No</option>'."\n";
        }
        $return .= '</select><br />'."\n";
        $return .= '<input type="hidden" name="attributes" value="article_id,show_border" />';
        break;

    // ----------------------------------------------------------------------------
            
    case 'scrolling':
        if (!isset($vars['page'])) {
            $vars['page'] = 0;
        }
        $news_query = 'SELECT *
				FROM `'.PAGE_TABLE.'`
				ORDER BY `title` ASC';
        $news_handle = $db->sql_query($news_query);
        if ($db->error[$news_handle] === 1) {
            $debug->addMessage('Failed to read pages', true);
            return false;
        }
        $num_articles = $db->sql_num_rows($news_handle);
        if ($num_articles == 0) {
            return 'No pages exist.<br />'."\n";
        }
        $return .= 'Page <select name="page">'."\n";
        for ($i = 1; $i <= $num_articles; $i++) {
            $news_result = $db->sql_fetch_assoc($news_handle);
            if ($vars['page'] == $news_result['id']) {
                $return .= "\t".'<option value="'.$news_result['id'].'" selected>'.$news_result['title'].'</option>'."\n";
            } else {
                $return .= "\t".'<option value="'.$news_result['id'].'">'.$news_result['title'].'</option>'."\n";
            }
        }
        $return .= '</select><br />'."\n";
        $return .= '<input type="hidden" name="attributes" value="page" />';
        break;
            
    // ----------------------------------------------------------------------------

    case 'calendarcategories':
        $return .= '<input type="hidden" name="attributes" value="" />'."\n";
        $return .= 'No options exist for this type.<br />'."\n";
        break;

    // ----------------------------------------------------------------------------

    case 'events':
        if (!isset($vars['mode'])) {
            $vars['mode'] = 'upcoming';
        }
        if (!isset($vars['num'])) {
            $vars['num'] = 10;
        }
        $return .= 'Display Mode <select name="mode">'."\n";
        if ($vars['mode'] == 'upcoming') {
            $return .= "\t".'<option value="upcoming" selected>Upcoming</option>'."\n";
            $return .= "\t".'<option value="past">Past</option>'."\n";
        } else {
            $return .= "\t".'<option value="upcoming">Upcoming</option>'."\n";
            $return .= "\t".'<option value="past" selected>Past</option>'."\n";
        }
        $return .= '</select><br />'."\n";
        $return .= 'Number of Entries <input type="text" name="num" size="4" value="'.$vars['num'].'" />';
        $return .= '<input type="hidden" name="attributes" value="mode,num" />'."\n";
        break;
    }
    return $return;
}
?>