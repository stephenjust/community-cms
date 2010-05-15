<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;

if (!$acl->check_permission('adm_page')) {
	$content .= 'You do not have the necessary permissions to access this module.';
	return true;
}

$page_id = (isset($_GET['id']) && (int)$_GET['id'] != 0) ? (int)$_GET['id'] : NULL;
$page_id = (isset($_POST['id']) && (int)$_POST['id'] != 0 && $page_id == NULL) ? (int)$_POST['id'] : $page_id;
$text_id = NULL;
if (isset($_POST['text_id']) && strlen($_POST['text_id']) > 0) {
	$text_id_query = 'SELECT * FROM ' . PAGE_TABLE . '
		WHERE text_id = \''.$_POST['text_id'].'\' LIMIT 1';
	$text_id_handle = $db->sql_query($text_id_query);
	if ($db->sql_num_rows($text_id_handle) == 1) {
		$content .= 'The Text ID you set is not unique.<br />';
	} else {
		$text_id = $_POST['text_id'];
	}
}
if ($_GET['action'] == 'new') {
	$menu = checkbox($_POST['menu']);
	if (!isset($_POST['show_title'])) {
		$_POST['show_title'] = NULL;
	}
	$parent = (int)$_POST['parent'];
	$show_title = checkbox($_POST['show_title']);
	// Add page to database.
	$new_page_query = 'INSERT INTO ' . PAGE_TABLE . '
		(text_id,title,meta_desc,show_title,type,menu,parent)
		VALUES (\''.$text_id.'\',\''.addslashes($_POST['title']).'\',\''.addslashes($_POST['meta_desc']).'\','.$show_title.',
		\''.(int)$_POST['type'].'\','.$menu.','.$parent.')';
	$new_page = $db->sql_query($new_page_query);
	if ($db->error[$new_page] === 1) {
		$content .= 'Failed to add page.<br />';
	} else {
		$content .= 'Successfully added page.<br />'."\n";
		log_action('New page \''.$_POST['title'].'\'');
	}
} // IF 'new'

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
				$content .= 'Failed to create link to external page.<br />';
			} else {
				$content .= 'Successfully created link to external page.<br />'."\n";
				log_action('New menu link to external page \''.$_POST['title'].'\'');
			}
		} else {
			$content .= 'Failed to create link to external page. Invalid link name.<br />';
		}
	} else {
		$content .= 'Failed to create link to external page. Invalid address.<br />';
	}
} // IF 'new_link'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'home') {
	if (!$acl->check_permission('page_set_home')) {
		$content .= 'You are not authorized to change the default page.<br />';
	} else {
		$check_page_query = 'SELECT id,title FROM ' . PAGE_TABLE . "
			WHERE id = $page_id LIMIT 1";
		$check_page_handle = $db->sql_query($check_page_query);
		if ($db->error[$check_page_handle] === 1) {
			$content .= 'Failed to check if page exists.<br />';
		}
		if ($db->sql_num_rows($check_page_handle) == 1) {
			if(!set_config('home',$page_id)) {
				$content .= 'Failed to change home page.<br />';
			} else {
				$check_page = $db->sql_fetch_assoc($check_page_handle);
				$content .= 'Successfully changed home page.<br />'."\n";
				log_action('Set home page to \''.stripslashes($check_page['title']).'\'');
			}
		} else {
			$content .= 'Could not find the page you are trying to delete.';
		}
	}
} // IF 'home'

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
	default:
		break;

	case 'del':
		if ((int)$_GET['id'] == $_GET['id']) {
			$page_id = (int)$_GET['id'];
		} else {
			break;
		}
		if (!page_delete($page_id)) {
			$content .= 'An error occured when attempting to delete the page.<br />'."\n";
		} else {
			$content .= 'Successfully deleted the page.<br />'."\n";
		}
		break; // case 'del'

	case 'new_page_group':
		if (!isset($_POST['page_group_name'])) {
			$content .= 'Invalid page group name.<br />'."\n";
		}
		if (strlen($_POST['page_group_name']) == 0) {
			$content .= 'Page group name must not be left blank.<br />'."\n";
		}
		$new_page_group = addslashes($_POST['page_group_name']);
		$new_page_group_query = 'INSERT INTO `'.PAGE_GROUP_TABLE.'` (`label`)
			VALUES (\''.$new_page_group.'\')';
		$new_page_group_handle = $db->sql_query($new_page_group_query);
		if ($db->error[$new_page_group_handle] === 1) {
			$content .= 'Failed to create new page group.<br />'."\n";
		} else {
			$content .= 'Created new page group.<br />'."\n";
			log_action('Created page group \''.stripslashes($new_page_group).'\'');
		}
		break; // case 'new_page_group'

	case 'hide':
		// FIXME: Implement page hiding
		break;

	case 'unhide':
		// FIXME: Implement page hiding
		break;
}

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'editsave') {
	$set_text_id = NULL;
	if(!isset($_POST['text_id'])) {
		$_POST['text_id'] = NULL;
	}
	if ($text_id == $_POST['text_id'] && $text_id != NULL) {
		$set_text_id = "text_id='$text_id',";
	}
	$title = addslashes($_POST['title']);
	$meta_desc = addslashes($_POST['meta_desc']);
	$parent = (int)$_POST['parent'];
	$menu = (isset($_POST['hidden'])) ? checkbox($_POST['hidden']) : 0;
	$show_title = (isset($_POST['show_title'])) ? checkbox($_POST['show_title']) : 0;
	$blocks_left = addslashes($_POST['blocks_left']);
	$blocks_right = addslashes($_POST['blocks_right']);
	$save_query = 'UPDATE ' . PAGE_TABLE . "
		SET {$set_text_id}title='$title', meta_desc='$meta_desc', menu=$menu,
		show_title=$show_title, parent=$parent,
		blocks_left='$blocks_left', blocks_right='$blocks_right'
		WHERE id = $page_id";
	$save_handle = $db->sql_query($save_query);
	if ($db->error[$save_handle] === 1) {
		$content .= 'Failed to edit page.<br />';
	} else {
		$content .= 'Updated page information.<br />'."\n";
		log_action('Updated information for page \''.stripslashes($title).'\'');
	}
} // IF 'editsave'

// ----------------------------------------------------------------------------

// Clean page list
page_clean_order();

// Move page down if requested.
if ($_GET['action'] == 'move_down') {
	if (page_move_down($page_id)) {
		$content .= 'Successfully moved page down.';
	} else {
		$content .= 'Failed to move page down.';
	}
}

// Move page up if requested.
if ($_GET['action'] == 'move_up') {
	if (page_move_up($page_id)) {
		$content .= 'Successfully moved page up.';
	} else {
		$content .= 'Failed to move page up.';
	}
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'edit') {
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
			<div id="tabs-0">
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
						stripslashes($parent_page_result['title']).'</option>';
				} else {
					$parent_page .= '<option value="'.$parent_page_result['id'].'">'.
						stripslashes($parent_page_result['title']).'</option>';
				}
			}
			$parent_page .= '</select>'."\n";
		}

		$tab_content['edit'] .= '<tr class="row1"><td width="150">Title (required):</td><td><input type="text" name="title" value="'.stripslashes($edit_page['title']).'" /></td></tr>
			<tr><td width="150">Page Description (optional):</td><td><textarea name="meta_desc" rows="5" cols="30" class="mceNoEditor">'.stripslashes($edit_page['meta_desc']).'</textarea></td></tr>
			<tr><td width="150">Parent Page</td><td>'.$parent_page.'</td></tr>
			<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" '.$show_title.'/></td></tr>
			<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="hidden" '.$hidden.'/></td></td></tr>
			<tr class="row2"><td valign="top">Blocks:</td><td>
			<div id="adm_block_list"></div>
			<script type="text/javascript">block_list_update();</script>
			</td></tr>
			<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
			</table>
			</div>
			</form>';
	}
	$tab_layout->add_tab('Edit Page',$tab_content['edit']);
}

// ----------------------------------------------------------------------------

function adm_page_manage_list_row($id) {
	global $acl;
	global $db;

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
	for ($i = 0; $i < page_level($page_info['id']); $i++) {
		$return .= '<img src="<!-- $IMAGE_PATH$ -->child.png" />';
	}
	if (strlen($page_info['text_id']) == 0 && $page_info['type'] != 0) {
		$return .= '<img src="<!-- $IMAGE_PATH$ -->info.png" alt="Information" /> ';
	}
	$return .= stripslashes($page_info['title']).' ';
	if ($page_info['id'] == get_config('home')) {
		$return .= '(Default)';
	}
	if ($page_info['menu'] == 0) {
		$return .= '(Hidden)';
	}
	$return .= '</td>';
	if ($acl->check_permission('page_delete')) {
		$return .= '
			<td><a href="?module=page&action=del&id='.$page_info['id'].'">
			<img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" />Delete</a></td>';
	}
	$return .= '
		<td><a href="?module=page&action=move_up&id='.$page_info['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->up.png" alt="Move Up" width="16px" height="16px" border="0px" />Move Up</a></td>
		<td><a href="?module=page&action=move_down&id='.$page_info['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->down.png" alt="Move Down" width="16px" height="16px" border="0px" />Move Down</a></td>';
	if ($page_info['type'] != 0) {
		$return .= '<td><a href="?module=page&action=edit&id='.$page_info['id'].'">
			<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" />Edit</a></td>';
		if ($acl->check_permission('page_set_home')) {
			$return .= '<td><a href="?module=page&action=home&id='.$page_info['id'].'">
				<img src="<!-- $IMAGE_PATH$ -->home.png" alt="Make Home" width="16px" height="16px" border="0px" />Make Home</a></td>';
		}
	} else {
		$return .= '<td>&nbsp;</td><td>&nbsp;</td>';
	}
	$return .= '</tr>';
	if (page_has_children($page_info['id']) == true) {
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
$numopts = 3;
if ($acl->check_permission('page_delete')) {
	$numopts++;
}
if ($acl->check_permission('page_set_home')) {
	$numopts++;
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
			stripslashes($parent_page_result['title']).'</option>';
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

// ----------------------------------------------------------------------------


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
			stripslashes($parent_page_result['title']).'</option>';
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

// ----------------------------------------------------------------------------

$tab_content['page_groups'] = NULL;
// Add page group form
$tab_content['page_groups'] .= '<h1>Add Page Group</h1>
	<form method="post" action="?module=page&amp;action=new_page_group">
	Group Name: <input type="text" name="page_group_name" />
	<input type="submit" value="Add Group" />
	</form><br />';

$tab_content['page_groups'] .= '<h1>Page Groups List</h1>';
// FIXME: Finish page group support.
$tab_layout->add_tab('Page Groups',$tab_content['page_groups']);
$content .= $tab_layout;

?>