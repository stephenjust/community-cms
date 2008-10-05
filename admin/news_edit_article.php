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
		$image = $_POST['image'];
		$page = $_POST['page'];
		$edit_article_query = 'UPDATE '.$CONFIG['db_prefix']."news SET description='$edit_content',page='$page',image='$image',date='$date' WHERE id = $edit_id";
		$edit_article = $db->query($edit_article_query);
		if(!$edit_article) {
			$content = 'Failed to edit article. '.mysqli_error($db);
			} else {
			$content = 'Successfully edited article.';
			}
		} else {
		$edit = get_row_from_db('news',"WHERE id = ".$_GET['id']);
		if($edit['num_rows'] != 0) {
			$content .= '<form method="POST" action="admin.php?module=news_edit_article&action=edit">
<h1>Edit Existing Article</h1>
<table class="admintable">
<input type="hidden" name="id" value="'.$edit[1]['id'].'" />
<tr><td width="150" class="row1">Heading:</td><td class="row1">'.stripslashes($edit[1]['name']).'</td></tr>
<tr><td class="row2" valign="top">Content:</td><td class="row2"><textarea name="update_content" rows="30">'.stripslashes($edit[1]['description']).'</textarea></td></tr>
<tr><td width="150" class="row1" valign="top">Page:</td><td class="row1"><select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 1 ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			if($page['id'] == $edit[1]['id']) {
				$content .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
				} else {
				$content .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
				}
			$i++;
			}
		$content .= '</select></td></tr>
<tr><td width="150" class="row2" valign="top">Image:</td><td class="row2">'.file_list('newsicons',2,$edit[1]['image']).'</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Submit" /></td></tr>
</table>';
			} else {
			$content = 'No article selected to edit.';
			}
		}
	?>