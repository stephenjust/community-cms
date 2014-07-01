<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;
global $debug;

require_once(ROOT.'includes/ui/UIIcon.class.php');

if (!$acl->check_permission('adm_page'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

$page_id = (isset($_GET['id']) && (int)$_GET['id'] != 0) ? (int)$_GET['id'] : NULL;
$page_id = (isset($_POST['id']) && (int)$_POST['id'] != 0 && $page_id == NULL) ? (int)$_POST['id'] : $page_id;
if ($_GET['action'] == 'new') {
	$show_title = (isset($_POST['show_title'])) ? (bool)checkbox($_POST['show_title']) : false;
	$show_menu = (isset($_POST['menu'])) ? (bool)checkbox($_POST['menu']) : false;
	try {
		page_add($_POST['text_id'],
			$_POST['title'],
			$_POST['meta_desc'],
			$_POST['type'],
			$show_title,
			$show_menu,
			$_POST['parent']);
		echo 'Successfully added page.<br />'."\n";
	}
	catch (Exception $e) {
		echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
	}
}

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'new_link') {
	$link = $_POST['url'];
	if (strlen($link) > 10) {
		$link = htmlentities($link);
		$name = addslashes($_POST['title']);
		$parent = (int)$_POST['parent'];
		if (strlen($name) > 2) {
			$title = $name.'<LINK>'.$link;
			// Add page to database.
			$new_page_query = 'INSERT INTO ' . PAGE_TABLE . '
				(title,parent,type,menu) VALUES ("'.$title.'",'.$parent.',0,1)';
			$new_page = $db->sql_query($new_page_query);
			if ($db->error[$new_page] === 1) {
				echo 'Failed to create link to external page.<br />';
			} else {
				echo 'Successfully created link to external page.<br />'."\n";
				Log::addMessage('New menu link to external page \''.$_POST['title'].'\'');
			}
		} else {
			echo 'Failed to create link to external page. Invalid link name.<br />';
		}
	} else {
		echo 'Failed to create link to external page. Invalid address.<br />';
	}
} // IF 'new_link'

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
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
		if ((int)$_GET['id'] == $_GET['id']) {
			$page_id = (int)$_GET['id'];
		} else {
			break;
		}
		try {
			$pg = new PageManager($page_id);
			$pg->delete();
			echo 'Successfully deleted the page.<br />';
		} catch (PageException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break; // case 'del'

	case 'hide':
		// FIXME: Implement page hiding
		break;

	case 'unhide':
		// FIXME: Implement page hiding
		break;

// ----------------------------------------------------------------------------

	case 'editsave':
		// TODO: Make sure you have permission to edit this page
		$set_text_id = NULL;
		if(!isset($_POST['text_id'])) {
			$_POST['text_id'] = NULL;
		}
		if (page_check_unique_id($_POST['text_id']) && $_POST['text_id'] != NULL) {
			$set_text_id = "`text_id`='{$_POST['text_id']}', ";
		}
		$title = addslashes($_POST['title']);
		$meta_desc = addslashes($_POST['meta_desc']);
		$parent = (int)$_POST['parent'];
		$menu = (isset($_POST['hidden'])) ? checkbox($_POST['hidden']) : 0;
		$show_title = (isset($_POST['show_title'])) ? checkbox($_POST['show_title']) : 0;
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
page_clean_order();

// Move page down if requested.
if ($_GET['action'] == 'move_down') {
	if (page_move_down($page_id)) {
		echo 'Successfully moved page down.';
	} else {
		echo 'Failed to move page down.';
	}
}

// Move page up if requested.
if ($_GET['action'] == 'move_up') {
	if (page_move_up($page_id)) {
		echo 'Successfully moved page up.';
	} else {
		echo 'Failed to move page up.';
	}
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'edit') {
	// TODO: Make sure you have permission to edit this page group
	$tab_content['edit'] = NULL;
	$edit_page_query = 'SELECT * FROM ' . PAGE_TABLE . "
		WHERE id = $page_id LIMIT 1";
	$edit_page_handle = $db->sql_query($edit_page_query);
	if ($db->error[$edit_page_handle] === 1) {
		$tab_content['edit'] .= 'Failed to load page data.';
	} else {
		$edit_page = $db->sql_fetch_assoc($edit_page_handle);
		$show_title = checkbox($edit_page['show_title'],1);
		$hidden = checkbox($edit_page['menu'],1);
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
	$tab_layout->add_tab('Edit Page',$tab_content['edit']);
}

// ----------------------------------------------------------------------------

function adm_page_manage_list_row($id) {
	global $acl;
	global $db;

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

	$page_info = page_get_info($id);

	if ($page_info['type'] == 0) {
		$page_info['title'] = explode('<LINK>',$page_info['title']);
		$page_info['title'] = $page_info['title'][0].' (Link)';
	}
	$return = '<tr><td>';
	$pg = new PageManager($id);
	$p_level = $pg->getLevel();
	for ($i = 0; $i < $p_level; $i++) {
		if ($i == ($p_level - 1))
			$return .= $icon_child;
		else
			$return .= $icon_spacer;
	}
	if (strlen($page_info['text_id']) == 0 && $page_info['type'] != 0) {
		$return .= $icon_info.' ';
	}
	$return .= $page_info['title'].' ';
	if ($page_info['id'] == get_config('home')) {
		$return .= '(Default)';
	}
	if ($page_info['menu'] == 0) {
		$return .= '(Hidden)';
	}
	$return .= '</td>';
	if ($acl->check_permission('page_delete') && $pg->isEditable()) {
		$return .= '
			<td><a href="?module=page&action=del&id='.$page_info['id'].'">
			'.$icon_delete.'Delete</a></td>';
	}
	if ($acl->check_permission('page_order')) {
		$return .= '
			<td><a href="?module=page&action=move_up&id='.$page_info['id'].'">
			'.$icon_up.'Move Up</a></td>
			<td><a href="?module=page&action=move_down&id='.$page_info['id'].'">
			'.$icon_down.'Move Down</a></td>';
	}
	if ($page_info['type'] != 0) {
		if ($pg->isEditable()) {
			$return .= '<td><a href="?module=page&action=edit&id='.$page_info['id'].'">
				'.$icon_edit.'Edit</a></td>';
		} else {
			$return .= '<td></td>';
		}
		if ($acl->check_permission('page_set_home')) {
			$return .= '<td><a href="?module=page&action=home&id='.$page_info['id'].'">
				'.$icon_home.'Make Home</a></td>';
		}
	} else {
		$return .= '<td>&nbsp;</td><td>&nbsp;</td>';
	}
	$return .= '</tr>';
	if (Page::has_children($page_info['id']) == true) {
		$children_query = 'SELECT * FROM `'.PAGE_TABLE.'`
			WHERE `parent` = '.$page_info['id'].' ORDER BY `list` ASC';
		$children_handle = $db->sql_query($children_query);
		for ($i = 1; $i <= $db->sql_num_rows($children_handle); $i++) {
			$children_result = $db->sql_fetch_assoc($children_handle);
			$return .= adm_page_manage_list_row($children_result['id']);
		}
	}
	return $return;
}

// ----------------------------------------------------------------------------

$tab_content['manage'] = NULL;
$numopts = 1;
if ($acl->check_permission('page_delete')) {
	$numopts++;
}
if ($acl->check_permission('page_set_home')) {
	$numopts++;
}
if ($acl->check_permission('page_order')) {
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
$tab_layout->add_tab('Manage Pages',$tab_content['manage']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('page_create')) {
	$tab_content['add'] = NULL;

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
	$tab_layout->add_tab('Add Page',$tab_content['add']);
}

// ----------------------------------------------------------------------------

if ($acl->check_permission('page_create')) {
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
	$tab_layout->add_tab('Add Link to External Page',$tab_content['addlink']);
}

echo $tab_layout;
