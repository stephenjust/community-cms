<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	$date = date('Y-m-d H:i:s');
  if ($_GET['action'] == 'edit') {
		if(strlen($_POST['image']) <= 3) {
			$_POST['image'] = NULL;
			}
		// Clean up variables.
		$edit_content = addslashes($_POST['update_content']);
		$edit_id = addslashes($_POST['id']);
		$name = stripslashes($_POST['title']);
		$name = str_replace('"','&quot;',$name);
		$name = str_replace('<','&lt;',$name);
		$name = str_replace('>','&gt;',$name);
		$showdate = $_POST['date_params'];
		$image = $_POST['image'];
		$page = $_POST['page'];
		$edit_article_query = 'UPDATE '.$CONFIG['db_prefix']."news SET name='$name',description='$edit_content',page='$page',image='$image',date_edited='$date',showdate='$showdate' WHERE id = $edit_id";
		$edit_article = $db->query($edit_article_query);
		if(!$edit_article) {
			$content = 'Failed to edit article. '.mysqli_error($db);
			} else {
			$content = 'Successfully edited article. '.log_action('Edited news article \''.$name.'\'');
			}
		} else {
		$edit_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE id = '.addslashes($_GET['id']).' LIMIT 1';
		$edit_handle = $db->query($edit_query);
		if($edit_handle->num_rows != 0) {
			$edit = $edit_handle->fetch_assoc();
			$content .= '<form method="POST" action="admin.php?module=news_edit_article&action=edit">
<h1>Edit Existing Article</h1>
<table class="admintable">
<input type="hidden" name="id" value="'.$edit['id'].'" />
<tr><td width="150" class="row1">Heading:</td><td class="row1"><input type="text" name="title" value="'.stripslashes($edit['name']).'" /></td></tr>
<tr><td class="row2" valign="top">Content:</td><td class="row2"><textarea name="update_content" rows="30">'.stripslashes($edit['description']).'</textarea></td></tr>
<tr><td width="150" class="row1" valign="top">Page:</td><td class="row1"><select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 1 ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			if($page['id'] == $edit['page']) {
				$content .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
				} else {
				$content .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
				}
			$i++;
			}
		if($edit['showdate'] == 0) {
			$date_params['hide'] = 'selected';
			$date_params['show'] = NULL;
			$date_params['mini'] = NULL;
			} elseif($edit['showdate'] == 1) {
			$date_params['hide'] = NULL;
			$date_params['show'] = 'selected';
			$date_params['mini'] = NULL;
			} else {
			$date_params['hide'] = NULL;
			$date_params['show'] = NULL;
			$date_params['mini'] = 'selected';
			}
		if($edit['page'] == 0) { $no_page = 'selected'; }
		$content .= '<option value="0" '.$no_page.'>No Page</option>
</select></td></tr>
<tr><td width="150" class="row2" valign="top">Image:</td><td class="row2">'.file_list('newsicons',2,$edit['image']).'</td></tr>
<tr><td width="150" class="row1" valign="top">Date:</td><td class="row1">
<select name="date_params">
<option value="0" '.$date_params['hide'].'>Hide Date</option>
<option value="1" '.$date_params['show'].'>Show Date</option>
<option value="2" '.$date_params['mini'].'>Show Mini</option>
</select>
</td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
</table>';
			} else {
			$content = 'No article selected to edit.';
			}
		}
	?>