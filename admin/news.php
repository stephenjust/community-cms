<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	$date = date('Y-m-d H:i:s');
		if ($_GET['action'] == 'delete') {
		$delete_article_query = 'DELETE FROM '.$CONFIG['db_prefix'].'news WHERE id = '.$_GET['id'];
		$delete_article = $db->query($delete_article_query);
		if(!$delete_article) {
			$message = 'Failed to delete article. '.mysqli_error($db);
			} else {
			$message = 'Successfully deleted article. '.log_action('Deleted article with id \''.$_GET['id'].'\'');
			}
		}
	$content = $message;
$content = $content.'<h1>Edit Article</h1>
<table style="border: 1px solid #000000;">
<tr><td><form method="POST" action="admin.php?module=news"><select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 1 ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			if(!isset($_POST['page'])) {
				$_POST['page'] = $page['id'];
				}
			if($page['id'] == $_POST['page']) {
				$content = $content.'<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
				} else {
				$content = $content.'<option value="'.$page['id'].'" />'.$page['title'].'</option>';
				}
			$i++;
			}
		$content = $content.'</select></td><td colspan="2"><input type="submit" value="Change Page" /></form></td></tr>
<tr><td width="350">Title:</td><td>Del</td><td>Edit</td></tr>';
	// Get page list in the order defined in the database. First is 0.
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE page = '.stripslashes($_POST['page']);
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
 	$i = 1;
 	if($page_list_rows == 0) {
 		$content = $content.'<tr><td class="adm_page_list_item">There are no articles on this page.</td><td></td><td></td></tr>';
 		}
	while ($i <= $page_list_rows) {
		$page_list = $page_list_handle->fetch_assoc();
		$content = $content.'<tr>
<td class="adm_page_list_item">'.stripslashes($page_list['name']).'</td>
<td><a href="?module=news&action=delete&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=news_edit_article&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		$i++;
	}
$content = $content.'</table>';
?>