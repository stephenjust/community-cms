<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_block_manager')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

$tab_layout = new Tabs;

switch ($_GET['action']) {
default:
    break;

case 'delete':
    try {
        $block = new Block($_GET['id']);
        $block->delete();
        echo 'Successfully deleted block.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

case 'new':
    try {
        if (!isset($_POST['type'])) { $_POST['type'] = null; 
        }
        if (!isset($_POST['attributes'])) { $_POST['attributes'] = null; 
        }
        block_create($_POST['type'], $_POST['attributes']);
        echo 'Successfully created block.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

// ----------------------------------------------------------------------------

case 'edit':
    if (!isset($_GET['id'])) {
        echo 'No block to edit.<br />'."\n";
        break;
    }
    if (!is_numeric($_GET['id'])) {
        echo 'Invalid block ID.<br />'."\n";
        break;
    }
    $edit_id = (int)$_GET['id'];
    $edit_block = new Block($edit_id);
    $options = block_edit_form($edit_block->getType(), $edit_block->getAttributes());

    $tab_content['edit'] = null;
    $tab_content['edit'] .= 'Block Type: '.$edit_block->getType().'<br />'."\n";
    $tab_content['edit'] .= 'Options:<br />'."\n";
    $tab_content['edit'] .= '<form method="post" action="'.HTML::schars('?module=block_manager&action=edit_save').'">'."\n"
    .$options.'<input type="hidden" name="id" value="'.$edit_id.'" />'."\n";
    if (count($edit_block->getAttributes()) != 0) {
        $tab_content['edit'] .= '<input type="Submit" value="Save Changes" />';
    }
    $tab_content['edit'] .= '</form><form method="post" action="?module=block_manager"><input type="submit" value="Go back" /></form>'."\n";


    $tab_layout->add_tab('Edit Block', $tab_content['edit']);
    break;

// ----------------------------------------------------------------------------

case 'edit_save':
    try {
        if (!isset($_POST['id'])) { $_POST['id'] = null; 
        }
        if (!isset($_POST['attributes'])) { $_POST['attributes'] = null; 
        }
        block_edit($_POST['id'], $_POST['attributes']);
        echo 'Successfully edited block.<br />'."\n";
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
}

// ----------------------------------------------------------------------------

$block_list_query = 'SELECT `id`,`type`,`attributes`
	FROM `'.BLOCK_TABLE.'`
	ORDER BY `type` ASC';
$block_list = DBConn::get()->query($block_list_query, [], DBConn::FETCH_ALL);
foreach ($block_list as $block)
{
    $attribute_list = ($block['attributes'] == '') ? null : ' ('.$block['attributes'].')';
    $current_row = array($block['type'].$attribute_list);
    if (acl::get()->check_permission('block_delete')) {
        $current_row[] = '<a href="?module=block_manager&action=delete&id='.$block['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a>'; 
    }
    if (acl::get()->check_permission('block_edit')) {
        $current_row[] = '<a href="?module=block_manager&action=edit&id='.$block['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a>'; 
    }
    $block_list_rows[] = $current_row;
}
$heading_list = array('Info');
if (acl::get()->check_permission('block_delete')) {
    $heading_list[] = 'Delete'; 
}
if (acl::get()->check_permission('block_edit')) {
    $heading_list[] = 'Edit'; 
}
$tab_content['manage'] = TableComponent::create($heading_list, $block_list_rows);
$tabs['manage'] = $tab_layout->add_tab('Manage Blocks', $tab_content['manage']);

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('block_create')) {
    $tab_content['create'] = null;
    $block_types = ["text", "events", "scrolling", "calendarcategories"];
    $block_types_list = '<select name="type" id="adm_block_type_list" onChange="block_options_list_update()">';
    foreach ($block_types as $block_type) {
        $block_types_list .= '<option value="'.$block_type.'">'.$block_type.'</option>';
    }
    $block_types_list .= '</select>';

    // ----------------------------------------------------------------------------

    $tab_content['create'] .= '<form method="post" action="admin.php?module=block_manager&action=new">
                    <table class="admintable">
                    <tr><td>Type:</td><td>'.$block_types_list.'</td></tr>
                    <tr><td>Options:</td><td><noscript>You need JavaScript enabled for the block options view to work properly.</noscript>
                    <div id="adm_block_type_options"></div></td></tr>
                    <tr><td class="empty"></td><td><input type="submit" value="Submit" /></td></tr>
                    </table></form>
                    <script language="javascript" type="text/javascript">
                    block_options_list_update()</script>';
    $tab_layout->add_tab('Create Block', $tab_content['create']);
}

echo $tab_layout;
?>