<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if ($_GET['action'] == 'new') {
	  if($_POST['hidden'] == 'on') { 
	  	$_POST['hidden'] = 0; 
	  	} else { 
	  	$_POST['hidden'] = 1;
	  	}
	  // Add page to database.
		$new_page_query = 'INSERT INTO '.$CONFIG['db_prefix'].'pages (title,type,menu) VALUES ("'.$_POST['title'].'",
	  "'.$_POST['type'].'",'.$_POST['hidden'].')';
		$new_page = $db->query($new_page_query);
		if(!$new_page) {
			echo errormesg(mysqli_error());
			} else {
			$message = 'Successfully added page';
			}
		}
	if ($_GET['action'] == 'del') {
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
				$message = 'Successfully deleted page.';
				}
			}
		}
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
		$content = $content.$move_page_query;
		$i++;
		}	
	$page_list_handle->free();
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
		}
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
		}
// $content = NULL;
$content = $message;
$content = $content.'<form method="POST" action="admin.php?module=pages&action=new">
<h1>Add Page</h1>
<table style="border: 1px solid #000000;">
<tr><td width="150">Title:</td><td><input type="text" name="title" value="" /></td></tr>
<tr><td valign="top">Type:</td><td>';
 	$pagetypes = get_row_from_db("pagetypes","","id,name");
 	$i = 1;
	while ($i <= $pagetypes['num_rows']) {
		$content = $content.'<input type="radio" name="type" value="'.$pagetypes[$i]['id'].'" />'.$pagetypes[$i]['name'].'<br />';
		$i++;
	}
$content = $content.'</td></td></tr>
<tr><td>Hidden:</td><td><input type="checkbox" name="hidden" /></td></td></tr>
<tr><td width="150">&nbsp;</td><td><input type="submit" value="Submit" /></td></tr>
</table>

</form>';

$content = $content.'<h1>Edit Page</h1>
<table style="border: 1px solid #000000;">
<tr><td width="350">Page:</td><td>Del</td><td>Up</td><td>Dn</td></tr>';
	// Get page list in the order defined in the database. First is 0.
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages ORDER BY list ASC';
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
 	$i = 1;
	while ($i <= $page_list_rows) {
		$page_list = $page_list_handle->fetch_assoc();
		$content = $content.'<tr>
<td class="adm_page_list_item">'.$page_list['title'].'</td>
<td><a href="?module=pages&action=del&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=pages&action=move_up&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->up.png" alt="Move Up" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=pages&action=move_down&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->down.png" alt="Move Down" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		$i++;
	}
$content = $content.'</table>';
?>