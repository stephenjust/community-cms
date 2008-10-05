<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	if ($_GET['action'] == 'new') {
		if(!isset($_POST['file_list'])) {
			$_POST['file_list'] = NULL;
			}
		if(strlen($_POST['file_list']) <= 3) {
			$message = 'No file selected.';
			} else {
	  	$new_article_query = 'INSERT INTO '.$CONFIG['db_prefix']."newsletters (label,page,year,month,path) VALUES ('".$_POST['label']."',".$_POST['page'].",".$_POST['year'].",".$_POST['month'].",'".$_POST['file_list']."')";
			$new_article = $db->query($new_article_query);
			if(!$new_article) {
				$message = 'Failed to add article. '.mysqli_error($db);
				} else {
				$message = 'Successfully added article.';
				}
			}
			if ($_GET['action'] == 'delete') {
			$delete_article_query = 'DELETE FROM '.$CONFIG['db_prefix'].'newsletters WHERE id = '.$_POST['delete'];
			$delete_article = $db->query($delete_article_query);
			if(!$delete_article) {
				$message = 'Failed to delete newsletter entry. '.mysqli_error($db);
				} else {
				$message = 'Successfully deleted newsletter entry.';
				}
			}
		}
		$content = $message;

$content .= '<h1>Add Newsletter</h1>
<form method="POST" action="admin.php?module=newsletter&action=new">
<table>
<tr><td>Label:</td><td><input type="text" name="label" /></td></tr>
<tr><td>File:</td><td>'.file_list('newsletters',1).'</td></tr>
<tr><td>Month:</td><td><input type="text" name="month" /></td></tr>
<tr><td>Year:</td><td><input type="text" name="year" /></td></tr>
<tr><td width="150">Page:</td><td>
<select name="page">';
	$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 2 ORDER BY list ASC';
	$page_query_handle = $db->query($page_query);
 	$i = 1;
	while ($i <= $page_query_handle->num_rows) {
		$page = $page_query_handle->fetch_assoc();
		$content .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		$i++;
	}
$content .= '</select></td></tr>
<tr><td></td><td><input type="submit" value="Submit" /></td></tr>
</table>
</form>

<h1>Delete Newsletter</h1>
<form method="POST" action="admin.php?module=newsletter&action=delete">
<table style="border: 1px solid #000000;">
<tr><td width="150">Page</td><td>Year</td><td>Month</td></tr>
<tr><td colspan="2">';
	$articles = get_row_from_db("newsletters","ORDER BY id desc");
	$i = 1;
	while ($i <= $articles['num_rows']) {
		$nl_page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$articles[$i]['page'];
		$nl_page_query_handle = $db->query($nl_page_query);
		$nl_page = $nl_page_query_handle->fetch_assoc();
		$content .= '<tr><td><input type="radio" name="delete" value="'.$articles[$i]['id'].'" />'.$nl_page['title'].'</td><td>'.$articles[$i]['year'].'</td><td>'.$articles[$i]['month'].'</td></tr>';
		$i++;
		}
	$content .= '</td></tr>
<tr><td width="150">&nbsp;</td><td><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>