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

if ($action == 'new') {
    $show_title = FormUtil::postCheckbox('show_title');
    $show_menu = FormUtil::postCheckbox('menu');
    try {
        PageManager::create(
            $_POST['title'],
            $_POST['parent'],
            $_POST['type'],
            $_POST['text_id'],
            $_POST['meta_desc'],
            $show_title,
            $show_menu);
        echo 'Successfully added page.<br />'."\n";
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
}

// ----------------------------------------------------------------------------

if ($action == 'new_link') {
    try {
        PageManager::createLink($_POST['title'], $_POST['url'], $_POST['parent']);
        echo 'Successfully created link to external page.<br />'."\n";
        Log::addMessage('New menu link to external page \''.$_POST['title'].'\'');
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to create link: '.$ex->getMessage().'</span><br />';
    }
} // IF 'new_link'

// ----------------------------------------------------------------------------

switch ($action) {
default:
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
    break; // case 'del'

// ----------------------------------------------------------------------------

case 'editsave':
    // TODO: Make sure you have permission to edit this page
    $set_text_id = null;
    $text_id = FormUtil::post('text_id');
    if (!PageUtil::textIdExists($text_id) && $text_id != null) {
        $set_text_id = "`text_id`='{$text_id}', ";
    }
    $title = addslashes($_POST['title']);
    $meta_desc = addslashes($_POST['meta_desc']);
    $parent = (int)$_POST['parent'];
    $menu = FormUtil::postCheckbox('hidden');
    $show_title = FormUtil::postCheckbox('show_title');
    $blocks_left = addslashes($_POST['blocks_left']);
    $blocks_right = addslashes($_POST['blocks_right']);
    $save_query = 'UPDATE ' . PAGE_TABLE . "
			SET {$set_text_id}`title`='$title', `meta_desc`='$meta_desc',
			`menu`=$menu, `show_title`=$show_title, `parent`=$parent,
			`blocks_left`='$blocks_left',
			`blocks_right`='$blocks_right'
			WHERE id = $page_id";
    $save_handle = $db->sql_query($save_query);
    if ($db->error[$save_handle] === 1) {
        echo '<span class="errormessage">Failed to edit page.</span><br />'."\n";
        break;
    }
    echo 'Updated page information.<br />'."\n";
    Log::addMessage('Updated information for page \''.stripslashes($title).'\'');
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
    $edit_page_query = 'SELECT * FROM ' . PAGE_TABLE . "
		WHERE id = $page_id LIMIT 1";
    $edit_page_handle = $db->sql_query($edit_page_query);
    if ($db->error[$edit_page_handle] === 1) {
        $tab_content['edit'] .= 'Failed to load page data.';
    } else {
        $edit_page = $db->sql_fetch_assoc($edit_page_handle);
        $show_title = ($edit_page['show_title']) ? "checked" : null;
        $hidden = ($edit_page['menu']) ? "checked" : null;
        $tab_content['edit'] .= '<form method="POST" action="admin.php?module=page&action=editsave">
			<table class="admintable">
			<input type="hidden" name="id" id="adm_page" value="'.$page_id.'" />';
        if (strlen($edit_page['text_id']) < 1) {
            $tab_content['edit'] .= '<tr class="row2"><td width="150">Text ID (optional):</td><td><input type="text" name="text_id" value="" /></td></tr>';
        }
        
        // Get list of pages for list of options as parent page
        $parent_page_list_query = 'SELECT * FROM `'.PAGE_TABLE.'`
			ORDER BY `title` ASC';
        $parent_page_list_handle = $db->sql_query($parent_page_list_query);
        if ($db->error[$parent_page_list_handle] === 1) {
            $parent_page = 'You cannot set a parent page at this time.'.
            '<input type="hidden" name="parent" value="0" />';
        } else {
            $parent_page = '<select name="parent">'."\n".
            '<option value="0">(No Parent)</option>'."\n";
            for ($i = 1; $i <= $db->sql_num_rows($parent_page_list_handle); $i++) {
                $parent_page_result = $db->sql_fetch_assoc($parent_page_list_handle);
                // Don't show current page on this list
                if ($page_id == $parent_page_result['id']) {
                    continue;
                }

                if ($edit_page['parent'] == $parent_page_result['id']) {
                    $parent_page .= '<option value="'.$parent_page_result['id'].'" selected>'.
                    $parent_page_result['title'].'</option>';
                } else {
                    $parent_page .= '<option value="'.$parent_page_result['id'].'">'.
                    $parent_page_result['title'].'</option>';
                }
            }
            $parent_page .= '</select>'."\n";
        }

        $tab_content['edit'] .= '<tr class="row1"><td width="150">Title (required):</td><td><input type="text" name="title" value="'.$edit_page['title'].'" /></td></tr>
			<tr><td width="150">Page Description (optional):</td><td><textarea name="meta_desc" rows="5" cols="30" class="mceNoEditor">'.$edit_page['meta_desc'].'</textarea></td></tr>
			<tr><td width="150">Parent Page:</td><td>'.$parent_page.'</td></tr>
			<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" '.$show_title.'/></td></tr>
			<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="hidden" '.$hidden.'/></td></td></tr>
			<tr class="row2"><td valign="top">Blocks:</td><td>
			<div id="adm_block_list"></div>
			<script type="text/javascript">block_list_update();</script>
			</td></tr>
			<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
			</table>
			</form>';
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
$page_list_handle = $db->sql_query($page_list_query);
$page_list_rows = $db->sql_num_rows($page_list_handle);
$rowstyle = 'row1';
for ($i = 1; $i <= $page_list_rows; $i++) {
    $page_list = $db->sql_fetch_assoc($page_list_handle);
    $tab_content['manage'] .= adm_page_manage_list_row($page_list['id']);
} // FOR
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Pages', $tab_content['manage']);

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('page_create')) {
    $tab_content['add'] = null;

    // Get list of pages for list of options as parent page
    $parent_page_list_query = 'SELECT * FROM `'.PAGE_TABLE.'`
		ORDER BY `title` ASC';
    $parent_page_list_handle = $db->sql_query($parent_page_list_query);
    if ($db->error[$parent_page_list_handle] === 1) {
        $parent_page = 'You cannot set a parent page at this time.'.
        '<input type="hidden" name="parent" value="0" />';
    } else {
        $parent_page = '<select name="parent">'."\n".
        '<option value="0">(No Parent)</option>'."\n";
        for ($i = 1; $i <= $db->sql_num_rows($parent_page_list_handle); $i++) {
            $parent_page_result = $db->sql_fetch_assoc($parent_page_list_handle);
            $parent_page .= '<option value="'.$parent_page_result['id'].'">'.
            $parent_page_result['title'].'</option>';
        }
        $parent_page .= '</select>'."\n";
    }

    $tab_content['add'] .= '<form method="POST" action="admin.php?module=page&action=new">
		<table class="admintable">
		<tr class="row1"><td width="150">Title (required):</td><td><input type="text" name="title" value="" /></td></tr>
		<tr><td width="150">Page Description (optional):</td><td><textarea name="meta_desc" rows="5" cols="30" class="mceNoEditor"></textarea></td></tr>
		<tr><td width="150">Parent Page:</td><td>'.$parent_page.'</td></tr>
		<tr class="row2"><td width="150">Text ID (optional):</td><td><input type="text" name="text_id" value="" /></td></tr>
		<tr class="row1"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" checked /></td></tr>
		<tr class="row2"><td>Show on Menu:</td><td><input type="checkbox" name="menu" checked /></td></td></tr>
		<tr class="row1"><td valign="top">Type:</td><td>
		<select name="type">';
    $pagetypes_query = 'SELECT id,name FROM ' . PAGE_TYPE_TABLE;
    $pagetypes_handle = $db->sql_query($pagetypes_query);
    $i = 1;
    while ($i <= $db->sql_num_rows($pagetypes_handle)) {
        $pagetypes = $db->sql_fetch_assoc($pagetypes_handle);
        $tab_content['add'] .= '<option value="'.$pagetypes['id'].'">'.$pagetypes['name'].'</option>';
        $i++;
    }
    $tab_content['add'] .= '</select>
		</td></td></tr>
		<tr class="row2"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
		</table></form>';
    $tab_layout->add_tab('Add Page', $tab_content['add']);
}

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('page_create')) {
    // Get list of pages for list of options as parent page
    $parent_page_list_query = 'SELECT * FROM `'.PAGE_TABLE.'`
		ORDER BY `title` ASC';
    $parent_page_list_handle = $db->sql_query($parent_page_list_query);
    if ($db->error[$parent_page_list_handle] === 1) {
        $parent_page = 'You cannot set a parent page at this time.'.
        '<input type="hidden" name="parent" value="0" />';
    } else {
        $parent_page = '<select name="parent">'."\n".
        '<option value="0">(No Parent)</option>'."\n";
        for ($i = 1; $i <= $db->sql_num_rows($parent_page_list_handle); $i++) {
            $parent_page_result = $db->sql_fetch_assoc($parent_page_list_handle);
            $parent_page .= '<option value="'.$parent_page_result['id'].'">'.
            $parent_page_result['title'].'</option>';
        }
        $parent_page .= '</select>'."\n";
    }
    $tab_content['addlink'] = '<form method="POST" action="admin.php?module=page&action=new_link">
		<table class="admintable" id="adm_pg_table_create_link">
		<tr class="row1"><td width="150">Link Text (required):</td><td><input type="text" name="title" value="" /></td></tr>
		<tr class="row2"><td valign="top">URL (required):</td><td>
		<input type="text" name="url" value="http://" /></td></tr>
		<tr class="row1"><td>Parent Page</td><td>'.$parent_page.'</td></tr>
		<tr class="row2"><td width="150">&nbsp;</td><td><input type="submit" value="Create Link" /></td></tr>
		</table></form>';
    $tab_layout->add_tab('Add Link to External Page', $tab_content['addlink']);
}

echo $tab_layout;
