<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if ($_GET['action'] == 'new') {
		$menu = checkbox($_POST['menu']);
		$show_title = checkbox($_POST['show_title']);
	  $message = NULL;
	  // Add page to database.
		$new_page_query = 'INSERT INTO '.$CONFIG['db_prefix'].'pages (title,show_title,type,menu) 
VALUES ("'.$_POST['title'].'",'.$show_title.',"'.$_POST['type'].'",'.$menu.')';
		$new_page = $db->query($new_page_query);
		if(!$new_page) {
			$message .= mysqli_error($db).'<br />';
			} else {
			$message .= 'Successfully added page.<br />'.log_action('New page \''.$_POST['title'].'\'');
			}
		} // IF 'new'

// ----------------------------------------------------------------------------

	if ($_GET['action'] == 'new_link') {
	  $message = NULL;
	  $link = $_POST['url'];
	  if(strlen($link) > 10) {
	  	$link = htmlentities($link);
	  	$name = $_POST['title'];
	  	if(strlen($name) > 2) {
				$title = $name.'<LINK>'.$link;
				// Add page to database.
				$new_page_query = 'INSERT INTO '.$CONFIG['db_prefix'].'pages (title,type,menu) VALUES ("'.$title.'",0,1)';
				$new_page = $db->query($new_page_query);
				if(!$new_page) {
					$message .= mysqli_error($db).'<br />';
					} else {
					$message .= 'Successfully created link to external page.<br />'.log_action('New menu link to external page \''.$_POST['title'].'\'');
					}
				} else {
				$message .= 'Failed to create link to external page. Invalid link name.<br />';
				}
			} else {
			$message .= 'Failed to create link to external page. Invalid address.<br />';
			}
		} // IF 'new_link'

// ----------------------------------------------------------------------------

	if($_GET['action'] == 'home') {
		$check_page_query = 'SELECT id,title FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'].' LIMIT 1';
		$check_page_handle = $db->query($check_page_query);
		if(!$check_page_handle) {
			$message .= 'Failed to check if page exists.<br />'.mysqli_error($db);
			}
		if($check_page_handle->num_rows == 1) {
			$home_query = 'UPDATE '.$CONFIG['db_prefix'].'config SET home='.$_GET['id'];
			$home = $db->query($home_query);
			if(!$home) {
				$message .= 'Failed to change home page.<br />'.mysqli_error($db);
				} else {
				$check_page = $check_page_handle->fetch_assoc();
				$message .= 'Successfully changed home page. '.log_action('Set home page to \''.$check_page['title'].'\'');
				$site_info['home'] = $_GET['id']; // Site info was gathered on admin.php, a while back, so we need to reset it to the current value.
				}
			} else {
			$message .= 'Could not find the page you are trying to delete.';
			}
		} // IF 'home'

// ----------------------------------------------------------------------------

	if($_GET['action'] == 'del') {
		// Delete page from database if no files exist on that page.
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'];
		$page_type = $db->query($page_type_query);
		$page_type_info = $page_type->fetch_assoc();
		if($page_type_info['type'] == 1) { 
			$check_page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE page = '.$_GET['id']; 
			} elseif($page_type_info['type'] == 2) {
			$check_page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'newsletters WHERE page = '.$_GET['id']; 
			}
		$notempty = 0;
		if(isset($check_page_query)) {
			$check_page = $db->query($check_page_query);
			if($check_page->num_rows != 0) {
				$notempty = 1;
				}
			}
		if($notempty != 0) {
			$message = 'Failed to delete page as it is not empty.';
			} else {
			$del_page_query = 'DELETE FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'];
			$del_page = $db->query($del_page_query);
			if(!$del_page) {
				$message = 'Failed to delete page. '.mysqli_error($db);
				} else {
				$message = 'Successfully deleted page. '.log_action('Deleted page with id \''.$_GET['id'].'\'');
				}
			}
		} // IF 'del'

// ----------------------------------------------------------------------------

	// Clean page list
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages ORDER BY list ASC';
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
	// Make sure no page has the same order number.
	$page_list = $page_list_handle->fetch_assoc();
	$move_page_query = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = 0 WHERE id = '.$page_list['id'].' LIMIT 1';
	$move_page = $db->query($move_page_query);
	if(!$move_page) {
		$message = 'Failed to optimize page order. '.mysqli_error($db);
		} else {
		$last_page = 0;
		}
	//Start with the second, because we set the first to 0.
	$i = 2;
	while($i <= $page_list_rows) {
		$page_list = $page_list_handle->fetch_assoc();
		$last_page++;
		$move_page_query = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = '.$last_page.' WHERE id = '.$page_list['id'];
		$move_page = $db->query($move_page_query);
		if(!$move_page) {
			$message = 'Failed to optimize page order. '.mysqli_error($db);
			}
		$i++;
		}	
	$page_list_handle->free();

// ----------------------------------------------------------------------------

	// Move page down if requested.
	if($_GET['action'] == 'move_down') {
		$move_down_query1 = 'SELECT id,list FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'].' LIMIT 1';
		$move_down1 = $db->query($move_down_query1);
		$move_down_handle1 = $move_down1->fetch_assoc();
		$start_pos = $move_down_handle1['list'];
		$end_pos = $move_down_handle1['list'] + 1;
		$move_up_query1 = 'SELECT id,list FROM '.$CONFIG['db_prefix'].'pages WHERE list = '.$end_pos.' LIMIT 1';
		$move_up1 = $db->query($move_up_query1);
		if($move_up1->num_rows != 1) {
			$message = 'Failed to move page down.';
			} else {
			$move_up_handle1 = $move_up1->fetch_assoc();
			$move_down_query2 = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = '.$end_pos.' WHERE id = '.$move_down_handle1['id'];
			$move_down_query3 = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = '.$start_pos.' WHERE id = '.$move_up_handle1['id'];
			if(!$db->query($move_down_query2) || !$db->query($move_down_query3)) {
				$message = 'Failed to move page down.';
				}
			}
		}// IF 'move_down'

// ----------------------------------------------------------------------------

	// Move page up if requested.
	if($_GET['action'] == 'move_up') {
		$move_up_query1 = 'SELECT id,list FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'].' LIMIT 1';
		$move_up1 = $db->query($move_up_query1);
		$move_up_handle1 = $move_up1->fetch_assoc();
		$start_pos = $move_up_handle1['list'];
		$end_pos = $move_up_handle1['list'] - 1;
		$move_down_query1 = 'SELECT id,list FROM '.$CONFIG['db_prefix'].'pages WHERE list = '.$end_pos.' LIMIT 1';
		$move_down1 = $db->query($move_down_query1);
		if($move_down1->num_rows != 1) {
			$message = 'Failed to move page up.';
			} else {
			$move_down_handle1 = $move_down1->fetch_assoc();
			$move_up_query2 = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = '.$end_pos.' WHERE id = '.$move_up_handle1['id'];
			$move_up_query3 = 'UPDATE '.$CONFIG['db_prefix'].'pages SET list = '.$start_pos.' WHERE id = '.$move_down_handle1['id'];
			if(!$db->query($move_up_query2) || !$db->query($move_up_query3)) {
				$message = 'Failed to move page up.';
				}
			}
		} // IF 'move_up'

// ----------------------------------------------------------------------------

	if($_GET['action'] == 'editsave') {
		$id = addslashes($_POST['id']);
		$title = addslashes($_POST['title']);
		$menu = checkbox($_POST['hidden']);
	  $show_title = checkbox($_POST['show_title']);
		$blocks_left = addslashes($_POST['blocks_left']);
		$blocks_right = addslashes($_POST['blocks_right']);
		$save_query = 'UPDATE '.$CONFIG['db_prefix'].'pages SET title="'.$title.'",menu='.$menu.',show_title='.$show_title.',blocks_left="'.$blocks_left.'",blocks_right="'.$blocks_right.'" WHERE id = '.$id.' LIMIT 1';
		$save_handle = $db->query($save_query);
		if(!$save_handle) {
			$message = 'Failed to edit page. '.mysqli_error($db);
			} else {
			$message = 'Updated page information. '.log_action('Updated information for page \''.$title.'\'');
			}
		} // IF 'editsave'

// ----------------------------------------------------------------------------

	$content = $message;
	if ($_GET['action'] == 'edit') {
		$edit_page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$_GET['id'].' LIMIT 1';
		$edit_page_handle = $db->query($edit_page_query);
		if(!$edit_page_handle) {
			$content .= 'Failed to load page data.';
			} else {
			$edit_page = $edit_page_handle->fetch_assoc();
			$show_title = checkbox($edit_page['show_title'],1);
			$hidden = checkbox($edit_page['menu'],1);
			$content .= '<form method="POST" action="admin.php?module=page&action=editsave">
<h1>Edit Page</h1>
<table class="admintable">
<input type="hidden" name="id" value="'.$edit_page['id'].'" />
<tr class="row1"><td width="150">Title:</td><td><input type="text" name="title" value="'.$edit_page['title'].'" /></td></tr>
<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" '.$show_title.'/></td></tr>
<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="hidden" '.$hidden.'/></td></td></tr>
<tr class="row2"><td valign="top">Blocks:<br>(Comma separated block IDs)</td><td>Left:<br />
<input type="text" name="blocks_left" value="'.$edit_page['blocks_left'].'" /><br />
Right:<br />
<input type="text" name="blocks_right" value="'.$edit_page['blocks_right'].'" />
</td></td></tr>
<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
</table>

</form>';
			}
		}

// ----------------------------------------------------------------------------

	$content .= '<form method="POST" action="admin.php?module=page&action=new">
<h1>Add Page</h1>
<table class="admintable">
<tr class="row1"><td width="150">Title:</td><td><input type="text" name="title" value="" /></td></tr>
<tr class="row2"><td width="150">Show Title:</td><td><input type="checkbox" name="show_title" checked /></td></tr>
<tr class="row1"><td>Show on Menu:</td><td><input type="checkbox" name="menu" checked /></td></td></tr>
<tr class="row2"><td valign="top">Type:</td><td>
<select name="type">';
	$pagetypes_query = 'SELECT id,name FROM '.$CONFIG['db_prefix'].'pagetypes';
	$pagetypes_handle = $db->query($pagetypes_query);
 	$i = 1;
	while ($i <= $pagetypes_handle->num_rows) {
		$pagetypes = $pagetypes_handle->fetch_assoc();
		$content .= '<option value="'.$pagetypes['id'].'">'.$pagetypes['name'].'</option>';
		$i++;
	}
$content .= '</select>
</td></td></tr>
<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
</table>

</form>';

// ----------------------------------------------------------------------------

$content .= '<form method="POST" action="admin.php?module=page&action=new_link">
<h1>Add Link to External Page</h1>
<table class="admintable">
<tr class="row1"><td width="150">Link Text:</td><td><input type="text" name="title" value="" /></td></tr>
<tr class="row2"><td valign="top">URL:</td><td>
<input type="text" name="url" value="http://" /><br />
</td></td></tr>
<tr class="row1"><td width="150">&nbsp;</td><td><input type="submit" value="Create Link" /></td></tr>
</table>
</form>';

// ----------------------------------------------------------------------------

$content .= '<h1>Manage Pages</h1>
<table class="admintable">
<tr><th width="350">Page:</th><th colspan="5">&nbsp;</th></tr>';
	// Get page list in the order defined in the database. First is 0.
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages ORDER BY list ASC';
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
	$rowstyle = 'row1';
	for ($i = 1; $i <= $page_list_rows; $i++) {
		$page_list = $page_list_handle->fetch_assoc();

		if($page_list['type'] == 0) {
			$page_list['title'] = explode('<LINK>',$page_list['title']);
			$page_list['title'] = $page_list['title'][0].' (Link)';
			}
		global $site_info;
		$content .= '<tr class="'.$rowstyle.'"><td>';
		if(strlen($page_list['text_id']) == 0 && $page_list['type'] != 0) {
			$content .= '<img src="<!-- $IMAGE_PATH$ -->info.png" alt="Information" /> ';
			}
		$content .= $page_list['title'].' ';
		if($page_list['id'] == $site_info['home']) {
			$content .= '(Default)';
			}
		if($page_list['menu'] == 0) {
			$content .= '(Hidden)';
			}
		$content .= '</td>
<td><a href="?module=page&action=del&id='.$page_list['id'].'">
<img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=page&action=move_up&id='.$page_list['id'].'">
<img src="<!-- $IMAGE_PATH$ -->up.png" alt="Move Up" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=page&action=move_down&id='.$page_list['id'].'">
<img src="<!-- $IMAGE_PATH$ -->down.png" alt="Move Down" width="16px" height="16px" border="0px" /></a></td>';
		if($page_list['type'] != 0) {
			$content .= '<td><a href="?module=page&action=edit&id='.$page_list['id'].'">
<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=page&action=home&id='.$page_list['id'].'">
<img src="<!-- $IMAGE_PATH$ -->home.png" alt="Make Home" width="16px" height="16px" border="0px" /></a></td>
</tr>'; 
			} else {
			$content .= '<td>&nbsp;</td><td>&nbsp;</td>';
			}
		if($rowstyle == 'row1') {
			$rowstyle = 'row2';
			} else {
			$rowstyle = 'row1';
			}
		} // FOR
$content .= '</table>';
?>