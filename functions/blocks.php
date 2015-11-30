<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2015 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */
namespace CommunityCMS;

/**
 * Create a new block record
 * @param string $type
 * @param string $attributes Comma separated list
 * @throws \Exception 
 */
function block_create($type, $attributes) 
{
    acl::get()->require_permission('block_create');

    $attb_array = explode(',', $attributes);
    $attb_count = count($attb_array);

    // Construct attribute string
    $attributes_final = array();
    for ($i = 0; $i < $attb_count; $i++) {
        if ($attb_array[$i] == null) {
            continue;
        }
        $attributes_final[] = $attb_array[$i].'='.FormUtil::post($attb_array[$i]);
    }
    $attb_string = implode(',', $attributes_final);
    
    // Create record
    $query = 'INSERT INTO `'.BLOCK_TABLE."`
		(`type`, `attributes`)
		VALUES
		(:type, :attributes)";
    try {
        DBConn::get()->query($query, [":type" => $type, ":attributes" => $attb_string]);
        Log::addMessage("Created block '$type' ($attb_string)");
    } catch (Exceptions\DBException $ex) {
        throw new \Exception('An error occurred while creating the block.', $ex->getCode(), $ex);
    }
}

/**
 * Edit a block entry
 * @param integer $id         Block ID
 * @param string  $attributes Comma separated list
 * @throws \Exception 
 */
function block_edit($id, $attributes)
{
    acl::get()->require_permission('block_edit');

    $attb_array = explode(',', $attributes);
    $attb_count = count($attb_array);

    // Construct attribute string
    $attributes_final = array();
    for ($i = 0; $i < $attb_count; $i++) {
        if ($attb_array[$i] == null) {
            continue;
        }
        $attributes_final[] = $attb_array[$i].'='.FormUtil::post($attb_array[$i]);
    }
    $attb_string = implode(',', $attributes_final);

    // Update the block record
    $query = 'UPDATE `'.BLOCK_TABLE."`
		SET `attributes` = :attributes
		WHERE `id` = :id";
    try {
        DBConn::get()->query($query, [":id" => $id, ":attributes" => $attb_string]);
        Log::addMessage("Edited block '$id' ($attb_string)");
    } catch (Exceptions\DBException $ex) {
        throw new \Exception('An error occurred while editing the block.', $ex->getCode(), $ex);
    }
}

/**
 * Generate the form for block management
 * @param string $type Block type
 * @param array  $vars Array of parameters to set as form defaults
 * @return string HTML for form (or false on failure)
 */
function block_edit_form($type,$vars = array()) 
{
    $return = null;
    if (!is_array($vars)) {
        Debug::get()->addMessage('Invalid set of variables', true);
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
        $news_items = Content::getByPage("*");
        if (count($news_items) == 0) {
            return 'No articles exist.<br />'."\n";
        }
        $news_select = new UISelect(["name" => "article_id"]);
        foreach ($news_items as $news_item) {
            $news_select->addOption($news_item->getID(), sprintf("%s - %s", PageUtil::getTitle($news_item->getPage()), $news_item->getTitle()));
        }
        $news_select->setChecked($vars['article_id']);
        $return .= "News Article $news_select<br />\n";
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
        $page_select = new UISelectPageList(["pagetype" => 1, "name" => "page"]);
        $page_select->setChecked($vars['page']);
        $return .= "Page $page_select<br />\n";
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
