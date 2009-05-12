<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;

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
	$show_title = checkbox($_POST['show_title']);
	// Add page to database.
	$new_page_query = 'INSERT INTO ' . PAGE_TABLE . '
		(text_id,title,show_title,type,menu)
		VALUES (\''.$text_id.'\',\''.$_POST['title'].'\','.$show_title.',
		\''.(int)$_POST['type'].'\','.$menu.')';
	$new_page = $db->sql_query($new_page_query);
	if ($db->error[$new_page] === 1) {
		$content .= 'Failed to add page.<br />';
	} else {
		$content .= 'Successfully added page.<br />'.log_action('New page \''.$_POST['title'].'\'');
	}
} // IF 'new'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'new_link') {
	$link = $_POST['url'];
	if (strlen($link) > 10) {
		$link = htmlentities($link);
		$name = $_POST['title'];
		if (strlen($name) > 2) {
			$title = $name.'<LINK>'.$link;
			// Add page to database.
			$new_page_query = 'INSERT INTO ' . PAGE_TABLE . '
				(title,type,menu) VALUES ("'.$title.'",0,1)';
			$new_page = $db->sql_query($new_page_query);
			if ($db->error[$new_page] === 1) {
				$content .= 'Failed to create link to external page.<br />';
			} else {
				$content .= 'Successfully created link to external page.<br />'.log_action('New menu link to external page \''.$_POST['title'].'\'');
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
	$check_page_query = 'SELECT id,title FROM ' . PAGE_TABLE . "
		WHERE id = $page_id LIMIT 1";
	$check_page_handle = $db->sql_query($check_page_query);
	if ($db->error[$check_page_handle] === 1) {
		$content .= 'Failed to check if page exists.<br />';
	}
	if ($db->sql_num_rows($check_page_handle) == 1) {
		$home_query = 'UPDATE ' . CONFIG_TABLE . " SET home=$page_id";
		$home = $db->sql_query($home_query);
		if($db->error[$home] === 1) {
			$content .= 'Failed to change home page.<br />';
		} else {
			$check_page = $db->sql_fetch_assoc($check_page_handle);
			$content .= 'Successfully changed home page. '.log_action('Set home page to \''.$check_page['title'].'\'');
			$site_info['home'] = $page_id; // Site info was gathered on admin.php, a while back, so we need to reset it to the current value.
		}
	} else {
		$content .= 'Could not find the page you are trying to delete.';
	}
} // IF 'home'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'del') {
	// Delete page from database if no files exist on that page.
	$page_type_query = 'SELECT page.id, page.title, pagetype.filename 
		FROM ' . PAGE_TABLE . ' page, ' . PAGE_TYPE_TABLE ." pagetype
		WHERE page.id = $page_id AND page.type = pagetype.id";
	$page_type = $db->sql_query($page_type_query);
	$page_type_info = $db->sql_fetch_assoc($page_type);
	if ($page_type_info['filename'] == 'news.php') {
		$check_page_query = 'SELECT * FROM ' . NEWS_TABLE . "
			WHERE page = $page_id";
	} elseif ($page_type_info['filename'] == 'newsletter.php') {
		$check_page_query = 'SELECT * FROM ' . NEWSLETTER_TABLE . "
			WHERE page = $page_id";
	}
	$notempty = 0;
	if (isset($check_page_query)) {
		$check_page = $db->sql_query($check_page_query);
		if ($db->sql_num_rows($check_page) != 0) {
			$notempty = 1;
		}
	}
	if ($notempty != 0) {
		$content .= 'Failed to delete page as it is not empty.<br />';
	} else {
		$del_page_query = 'DELETE FROM ' . PAGE_TABLE . "
			WHERE id = $page_id";
		$del_page = $db->sql_query($del_page_query);
		if ($db->error[$del_page] === 1) {
			$content .= 'Failed to delete page.<br />';
		} else {
			$content .= 'Successfully deleted page.<br />'
				.log_action('Deleted page with id \''.stripslashes($page_type_info['title']).'\'');
		}
	}
} // IF 'del'

// ----------------------------------------------------------------------------

// Clean page list
$page_list_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
$page_list_handle = $db->sql_query($page_list_query);
if($db->error[$page_list_handle] === 1) {
	$content .= 'Failed to read page information to optimize page order.<br />';
} else {
	$page_list_rows = $db->sql_num_rows($page_list_handle);
	// Make sure no page has the same order number.
	$page_list = $db->sql_fetch_assoc($page_list_handle);
	$move_page_query = 'UPDATE ' . PAGE_TABLE . '
		SET list = 0 WHERE id = '.$page_list['id'];
	$move_page = $db->sql_query($move_page_query);
	if ($db->error[$move_page] === 1) {
		$content = 'Failed to optimize page order.<br />';
	} else {
		$last_page = 0;
	}
	//Start with the second, because we set the first to 0.
	$i = 2;
	while ($i <= $page_list_rows) {
		$page_list = $db->sql_fetch_assoc($page_list_handle);
		$last_page++;
		$move_page_query = 'UPDATE ' . PAGE_TABLE . '
			SET list = '.$last_page.' WHERE id = '.$page_list['id'];
		$move_page = $db->sql_query($move_page_query);
		if ($db->error[$move_page] === 1) {
			$content = 'Failed to optimize page order.<br />';
		}
		$i++;
	}
}

// ----------------------------------------------------------------------------

// Move page down if requested.
if ($_GET['action'] == 'move_down') {
	$move_down_query1 = 'SELECT id,list FROM ' . PAGE_TABLE . "
		WHERE id = $page_id LIMIT 1";
	$move_down1 = $db->sql_query($move_down_query1);
	if($db->error[$move_down1] === 1) {
		$content .= 'Failed to read page information.<br />';
	}
	$move_down_handle1 = $db->sql_fetch_assoc($move_down1);
	$start_pos = $move_down_handle1['list'];
	$end_pos = $move_down_handle1['list'] + 1;
	$move_up_query1 = "SELECT id,list FROM " . PAGE_TABLE . " WHERE list = $end_pos LIMIT 1";
	$move_up1 = $db->sql_query($move_up_query1);
	if ($db->sql_num_rows($move_up1) != 1) {
		$content .= 'Failed to move page down.<br />';
	} else {
		$move_up_handle1 = $db->sql_fetch_assoc($move_up1);
		$move_down_query2 = 'UPDATE ' . PAGE_TABLE . '
			SET list = '.$end_pos.' WHERE id = '.$move_down_handle1['id'];
		$move_down_query3 = 'UPDATE ' . PAGE_TABLE . '
			SET list = '.$start_pos.' WHERE id = '.$move_up_handle1['id'];
		$move_down_handle2 = $db->sql_query($move_down_query2);
		$move_down_handle3 = $db->sql_query($move_down_query3);
		if ($db->error[$move_down_handle2] === 1 || $db->error[$move_down_handle3] === 1) {
			$content .= 'Failed to move page down.<br />';
		}
	}
}// IF 'move_down'

// ----------------------------------------------------------------------------

// Move page up if requested.
if ($_GET['action'] == 'move_up') {
	$move_up_query1 = 'SELECT id,list FROM ' . PAGE_TABLE . " WHERE id = $page_id LIMIT 1";
	$move_up1 = $db->sql_query($move_up_query1);
	$move_up_handle1 = $db->sql_fetch_assoc($move_up1);
	$start_pos = $move_up_handle1['list'];
	$end_pos = $move_up_handle1['list'] - 1;
	$move_down_query1 = 'SELECT id,list FROM ' . PAGE_TABLE . " WHERE list = $end_pos LIMIT 1";
	$move_down1 = $db->sql_query($move_down_query1);
	if ($db->sql_num_rows($move_down1) != 1) {
		$content .= 'Failed to move page up.<br />';
	} else {
		$move_down_handle1 = $db->sql_fetch_assoc($move_down1);
		$move_up_query2 = 'UPDATE ' . PAGE_TABLE . '
			SET list = '.$end_pos.' WHERE id = '.$move_up_handle1['id'];
		$move_up_query3 = 'UPDATE ' . PAGE_TABLE . '
			SET list = '.$start_pos.' WHERE id = '.$move_down_handle1['id'];
		$move_up_handle2 = $db->sql_query($move_up_query2);
		$move_up_handle3 = $db->sql_query($move_up_query3);
		if ($db->error[$move_up_handle2] === 1 || $db->error[$move_up_handle3] === 1) {
			$content .= 'Failed to move page up.<br />';
		}
	}
} // IF 'move_up'

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
	$menu = checkbox($_POST['hidden']);
	$show_title = checkbox($_POST['show_title']);
	$blocks_left = addslashes($_POST['blocks_left']);
	$blocks_right = addslashes($_POST['blocks_right']);
	$save_query = 'UPDATE ' . PAGE_TABLE . "
		SET {$set_text_id}title='$title',menu=$menu,show_title=$show_title,
		blocks_left='$blocks_left',blocks_right='$blocks_right'
		WHERE id = $page_id";
	$save_handle = $db->sql_query($save_query);
	if ($db->error[$save_handle] === 1) {
		$content .= 'Failed to edit page.<br />';
	} else {
		$content .= 'Updated page information.<br />'.log_action('Updated information for page \''.$title.'\'');
	}
} // IF 'editsave'

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
			<input type="hidden" name="id" value="'.$page_id.'" />';
		if(strlen($edit_page['text_id']) < 1) {
			$tab_content['edit'] .= '<tr class="row2"><td width="150">Text ID:</td><td><input type="text" name="text_id" value="" /></td></tr>';
		}
		$tab_content['edit'] .= '<tr class="row1"><td width="150">Title:</td><td><input type="text" name="title" value="'.$edit_page['title'].'" /></td></tr>
			<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" '.$show_title.'/></td></tr>
			<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="hidden" '.$hidden.'/></td></td></tr>
			<tr class="row2"><td valign="top">Blocks:<br>(Comma separated block IDs)</td><td>Left:<br />
			<input type="text" name="blocks_left" value="'.$edit_page['blocks_left'].'" /><br />
			Right:<br />
			<input type="text" name="blocks_right" value="'.$edit_page['blocks_right'].'" />
			</td></td></tr>
			<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
			</table>
			</div>
			</form>';
	}
	$tab_layout->add_tab('Edit Page',$tab_content['edit']);
}

// ----------------------------------------------------------------------------

$tab_content['manage'] = NULL;
$tab_content['manage'] .= '<table class="admintable">
<tr><th width="350">Page:</th><th colspan="5">&nbsp;</th></tr>';
// Get page list in the order defined in the database. First is 0.
$page_list_query = 'SELECT * FROM '.PAGE_TABLE.' ORDER BY list ASC';
$page_list_handle = $db->sql_query($page_list_query);
$page_list_rows = $db->sql_num_rows($page_list_handle);
$rowstyle = 'row1';
for ($i = 1; $i <= $page_list_rows; $i++) {
	$page_list = $db->sql_fetch_assoc($page_list_handle);

	if ($page_list['type'] == 0) {
		$page_list['title'] = explode('<LINK>',$page_list['title']);
		$page_list['title'] = $page_list['title'][0].' (Link)';
	}
	global $site_info;
	$tab_content['manage'] .= '<tr class="'.$rowstyle.'"><td>';
	if (strlen($page_list['text_id']) == 0 && $page_list['type'] != 0) {
		$tab_content['manage'] .= '<img src="<!-- $IMAGE_PATH$ -->info.png" alt="Information" /> ';
	}
	$tab_content['manage'] .= $page_list['title'].' ';
	if ($page_list['id'] == $site_info['home']) {
		$tab_content['manage'] .= '(Default)';
	}
	if ($page_list['menu'] == 0) {
		$tab_content['manage'] .= '(Hidden)';
	}
	$tab_content['manage'] .= '</td>
		<td><a href="?module=page&action=del&id='.$page_list['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=page&action=move_up&id='.$page_list['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->up.png" alt="Move Up" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=page&action=move_down&id='.$page_list['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->down.png" alt="Move Down" width="16px" height="16px" border="0px" /></a></td>';
	if ($page_list['type'] != 0) {
		$tab_content['manage'] .= '<td><a href="?module=page&action=edit&id='.$page_list['id'].'">
			<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
			<td><a href="?module=page&action=home&id='.$page_list['id'].'">
			<img src="<!-- $IMAGE_PATH$ -->home.png" alt="Make Home" width="16px" height="16px" border="0px" /></a></td>
			</tr>';
	} else {
		$tab_content['manage'] .= '<td>&nbsp;</td><td>&nbsp;</td>';
	}
	if($rowstyle == 'row1') {
		$rowstyle = 'row2';
	} else {
		$rowstyle = 'row1';
	}
} // FOR
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Pages',$tab_content['manage']);

// ----------------------------------------------------------------------------

$tab_content['add'] = NULL;
$tab_content['add'] .= '<form method="POST" action="admin.php?module=page&action=new">
	<table class="admintable">
	<tr class="row1"><td width="150">Title:</td><td><input type="text" name="title" value="" /></td></tr>
	<tr class="row2"><td width="150">Text ID</td><td><input type="text" name="text_id" value="" /></td></tr>
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

$tab_content['addlink'] = '<div id="tabs-3"><form method="POST" action="admin.php?module=page&action=new_link">
	<table class="admintable" id="adm_pg_table_create_link">
	<tr class="row1"><td width="150">Link Text:</td><td><input type="text" name="title" value="" /></td></tr>
	<tr class="row2"><td valign="top">URL:</td><td>
	<input type="text" name="url" value="http://" /><br />
	</td></td></tr>
	<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Create Link" /></td></tr>
	</table></form></div></div>';
$tab_layout->add_tab('Add Link to External Page',$tab_content['addlink']);
$content .= $tab_layout;

?>