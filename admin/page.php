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

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_page')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

$page_id = FormUtil::get('id', FILTER_VALIDATE_INT, null,
    FormUtil::post('id', FILTER_VALIDATE_INT, null, 0));
$action = FormUtil::get('action');

switch ($action) {
default:
    break;

case 'new':
    try {
        PageManager::create(
            FormUtil::post('title'),
            FormUtil::post('parent'),
            FormUtil::post('type'),
            FormUtil::post('text_id'),
            FormUtil::post('meta_desc'),
            FormUtil::postCheckbox('show_title'),
            FormUtil::postCheckbox('menu'));
        echo 'Successfully added page.<br />'."\n";
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
    break;

case 'new_link':
    try {
        PageManager::createLink($_POST['title'], $_POST['url'], $_POST['parent']);
        echo 'Successfully created link to external page.<br />'."\n";
        Log::addMessage('New menu link to external page \''.$_POST['title'].'\'');
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to create link: '.$ex->getMessage().'</span><br />';
    }
    break;

case 'home':
    try {
        $pg = new PageManager($page_id);
        $pg->setHomepage();
        echo 'Changed default page.<br />';
    } catch (PageException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break; // case 'home'

case 'del':
    try {
        $pg = new PageManager($page_id);
        $pg->delete();
        echo 'Successfully deleted the page.<br />';
    } catch (PageException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

case 'editsave':
    try {
        PageManager::edit(
            $page_id,
            FormUtil::post('title'),
            FormUtil::post('parent'),
            FormUtil::post('text_id'),
            FormUtil::post('meta_desc'),
            FormUtil::postCheckbox('show_title'),
            FormUtil::postCheckbox('hidden'),
            FormUtil::post('blocks_left'),
            FormUtil::post('blocks_right')
        );
        echo 'Updated page information.<br />'."\n";
    } catch (\Exception $ex) {
        echo '<span class="errormessage">'.$ex->getMessage().'</span><br />'."\n";
    }
    break;
}

// ----------------------------------------------------------------------------

// Clean page list
PageUtil::cleanOrder();

// Move page down if requested.
if ($action == 'move_down') {
    try {
        $pm = new PageManager($page_id);
        $pm->moveDown();
        echo 'Successfully moved page down.';
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to move page down: '.$ex->getMessage().'</span>';
    }
}

// Move page up if requested.
if ($action == 'move_up') {
    try {
        $pm = new PageManager($page_id);
        $pm->moveUp();
        echo 'Successfully moved page up.';
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to move page up: '.$ex->getMessage().'</span>';
    }
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

// ----------------------------------------------------------------------------

if ($action == 'edit') {
    // TODO: Make sure you have permission to edit this page group
    $tab_content['edit'] = null;
    $edit_page_query = 'SELECT * FROM `'.PAGE_TABLE."`
		WHERE `id` = :id LIMIT 1";
    try {
        $edit_page = DBConn::get()->query($edit_page_query, [":id" => $page_id], DBConn::FETCH);
        $show_title = ($edit_page['show_title']) ? "checked" : null;
        $hidden = ($edit_page['menu']) ? "checked" : null;
        $tab_content['edit'] .= '<form method="POST" action="admin.php?module=page&action=editsave">
			<table class="admintable">
			<input type="hidden" name="id" id="adm_page" value="'.$page_id.'" />';
        if (strlen($edit_page['text_id']) < 1) {
            $tab_content['edit'] .= '<tr class="row2"><td width="150">Text ID (optional):</td><td><input type="text" name="text_id" value="" /></td></tr>';
        }

        $parent_page_list = new UISelectPageList(["name" => "parent"]);
        $parent_page_list->addOption(0, "(No Parent)");
        $parent_page_list->setChecked($edit_page['parent']);

        $tab_content['edit'] .= '<tr class="row1"><td width="150">Title (required):</td><td><input type="text" name="title" value="'.$edit_page['title'].'" /></td></tr>
			<tr><td width="150">Page Description (optional):</td><td><textarea name="meta_desc" rows="5" cols="30" class="mceNoEditor">'.$edit_page['meta_desc'].'</textarea></td></tr>
			<tr><td width="150">Parent Page:</td><td>'.$parent_page_list.'</td></tr>
			<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" '.$show_title.'/></td></tr>
			<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="hidden" '.$hidden.'/></td></td></tr>
			<tr class="row2"><td valign="top">Blocks:</td><td>
			<div id="adm_block_list"></div>
			<script type="text/javascript">block_list_update();</script>
			</td></tr>
			<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
			</table>
			</form>';
    } catch (Exceptions\DBException $ex) {
        $tab_content['edit'] .= 'Failed to load page data.';
    }
    $tab_layout->add_tab('Edit Page', $tab_content['edit']);
}

// ----------------------------------------------------------------------------

function adm_page_manage_list_row($id) 
{
    // Create icon instances
    $icon_info = new UIIcon(array('src' => 'info.png', 'alt' => 'Information'));
    $icon_child = new UIIcon(array('src' => 'child.png'));
    $icon_spacer = new UIIcon(array('src' => 'spacer.png'));
    $icon_delete = new UIIcon(array('src' => 'delete.png', 'alt' => 'Delete'));
    $icon_up = new UIIcon(array('src' => 'up.png', 'alt' => 'Move Up'));
    $icon_down = new UIIcon(array('src' => 'down.png', 'alt' => 'Move Down'));
    $icon_edit = new UIIcon(array('src' => 'edit.png', 'alt' => 'Edit'));
    $icon_home = new UIIcon(array('src' => 'home.png', 'alt' => 'Make Home'));

    if (!is_numeric($id) || is_array($id)) {
        return false;
    }
    $id = (int)$id;

    $pm = new PageManager($id);

    $return = '<tr><td>';
    $p_level = $pm->getLevel();
    for ($i = 0; $i < $p_level; $i++) {
        if ($i == ($p_level - 1)) {
            $return .= $icon_child; 
        }
        else {
            $return .= $icon_spacer; 
        }
    }
    if (strlen($pm->getTextId()) == 0 && $pm->getType() != 0) {
        $return .= $icon_info.' ';
    }
    $return .= $pm->getTitle(true);
    $return .= '</td>';
    if (acl::get()->check_permission('page_delete') && $pm->isEditable()) {
        $return .= '
			<td><a href="?module=page&action=del&id='.$pm->getId().'">
			'.$icon_delete.'Delete</a></td>';
    }
    if (acl::get()->check_permission('page_order')) {
        $return .= '
			<td><a href="?module=page&action=move_up&id='.$pm->getId().'">
			'.$icon_up.'Move Up</a></td>
			<td><a href="?module=page&action=move_down&id='.$pm->getId().'">
			'.$icon_down.'Move Down</a></td>';
    }
    if ($pm->getType() != 0) {
        if ($pm->isEditable()) {
            $return .= '<td><a href="?module=page&action=edit&id='.$pm->getId().'">
				'.$icon_edit.'Edit</a></td>';
        } else {
            $return .= '<td></td>';
        }
        if (acl::get()->check_permission('page_set_home')) {
            $return .= '<td><a href="?module=page&action=home&id='.$pm->getId().'">
				'.$icon_home.'Make Home</a></td>';
        }
    } else {
        $return .= '<td>&nbsp;</td><td>&nbsp;</td>';
    }
    $return .= '</tr>';
    $child_ids = $pm->getChildren();
    foreach ($child_ids as $id) {
        $return .= adm_page_manage_list_row($id);
    }
    return $return;
}

// ----------------------------------------------------------------------------

$tab_content['manage'] = null;
$numopts = 1;
if (acl::get()->check_permission('page_delete')) {
    $numopts++;
}
if (acl::get()->check_permission('page_set_home')) {
    $numopts++;
}
if (acl::get()->check_permission('page_order')) {
    $numopts = $numopts + 2;
}
$tab_content['manage'] .= '<table class="admintable">
<tr><th width="350">Page:</th><th colspan="'.$numopts.'">&nbsp;</th></tr>';
// Get page list in the order defined in the database. First is 0.

$page_list_query = 'SELECT * FROM `'.PAGE_TABLE.'`
	WHERE `parent` = 0 ORDER BY `list` ASC';
$page_list = DBConn::get()->query($page_list_query, [], DBConn::FETCH_ALL);
foreach ($page_list as $page) {
    $tab_content['manage'] .= adm_page_manage_list_row($page['id']);
} // FOR
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Pages', $tab_content['manage']);

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('page_create')) {
    $tab_content['add'] = null;

    $parent_page_list = new UISelectPageList(["name" => "parent"]);
    $parent_page_list->addOption(0, "(No Parent)");
    $parent_page_list->setChecked(0);

    $tab_content['add'] .= '<form method="POST" action="admin.php?module=page&action=new">
		<table class="admintable">
		<tr class="row1"><td width="150">Title (required):</td><td><input type="text" name="title" value="" /></td></tr>
		<tr><td width="150">Page Description (optional):</td><td><textarea name="meta_desc" rows="5" cols="30" class="mceNoEditor"></textarea></td></tr>
		<tr><td width="150">Parent Page:</td><td>'.$parent_page_list.'</td></tr>
		<tr class="row2"><td width="150">Text ID (optional):</td><td><input type="text" name="text_id" value="" /></td></tr>
		<tr class="row1"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" checked /></td></tr>
		<tr class="row2"><td>Show on Menu:</td><td><input type="checkbox" name="menu" checked /></td></td></tr>
		<tr class="row1"><td valign="top">Type:</td><td>';
    $pagetypes_query = 'SELECT `id`, `name` FROM ' . PAGE_TYPE_TABLE;
    $pagetypes = DBConn::get()->query($pagetypes_query, [], DBConn::FETCH_ALL);
    $pagetype_select = new UISelect(["name" => "type"]);
    foreach ($pagetypes as $pagetype) {
        $pagetype_select->addOption($pagetype['id'], $pagetype['name']);
    }
    $tab_content['add'] .= $pagetype_select.'
		</td></td></tr>
		<tr class="row2"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
		</table></form>';
    $tab_layout->add_tab('Add Page', $tab_content['add']);
}

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('page_create')) {
    $parent_page_list = new UISelectPageList(["name" => "parent"]);
    $parent_page_list->addOption(0, "(No Parent)");
    $parent_page_list->setChecked(0);
    $tab_content['addlink'] = '<form method="POST" action="admin.php?module=page&action=new_link">
		<table class="admintable" id="adm_pg_table_create_link">
		<tr class="row1"><td width="150">Link Text (required):</td><td><input type="text" name="title" value="" /></td></tr>
		<tr class="row2"><td valign="top">URL (required):</td><td>
		<input type="text" name="url" value="http://" /></td></tr>
		<tr class="row1"><td>Parent Page</td><td>'.$parent_page_list.'</td></tr>
		<tr class="row2"><td width="150">&nbsp;</td><td><input type="submit" value="Create Link" /></td></tr>
		</table></form>';
    $tab_layout->add_tab('Add Link to External Page', $tab_content['addlink']);
}

echo $tab_layout;
