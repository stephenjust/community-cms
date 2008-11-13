<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
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
				$message = 'Successfully added article. '.log_action('New newsletter \''.$_POST['label'].'\'');
				}
			}
		}
	if($_GET['action'] == 'delete') {
		$delete_article_query = 'DELETE FROM '.$CONFIG['db_prefix'].'newsletters WHERE id = '.$_POST['delete'];
		$delete_article = $db->query($delete_article_query);
		if(!$delete_article) {
			$message = 'Failed to delete newsletter entry. '.mysqli_error($db);
			} else {
			$message = 'Successfully deleted newsletter entry. '.log_action('Deleted newsletter with id \''.$_POST['delete'].'\'');
			}
		}
		$content = $message;
		$monthbox = '<select name="month">';
		$monthcount = 1; 
		while ($monthcount <= 12) {
		if(date('m') == $monthcount) {
			$monthbox .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
			$monthcount++;
			} else {
			$monthbox .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
			$monthcount++;
		}
	}
$monthbox .= '</select>';
$content .= '<h1>Add Newsletter</h1>
<form method="POST" action="admin.php?module=newsletter&action=new">
<table class="admintable">
<tr><td class="row1">Label:</td><td class="row1"><input type="text" name="label" /></td></tr>
<tr><td valign="top" class="row2">File:</td><td class="row2"><div id="dynamic_file_list">
'.dynamic_file_list('newsletters').'</div>
<input type="button" value="Upload File" onClick="window.open(\'./admin/upload_mini.php\',\'mywindow\',\'width=400,height=200\')" />
<input type="button" value="Refresh List" onClick="update_dynamic_file_list()" />
</td></tr>
<tr><td class="row1">Date:</td><td class="row1">'.$monthbox.'<input type="text" name="year" maxlength="4" size="4" value="'.date('Y').'" /></td></tr>
<tr><td width="150" class="row2">Page:</td><td class="row2">
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
<tr><td class="row1"></td><td class="row1"><input type="submit" value="Submit" /></td></tr>
</table>
</form>

<h1>Delete Newsletter</h1>
<form method="POST" action="admin.php?module=newsletter&action=delete">
<table class="admintable">
<tr><td width="150" class="row1">Label</td><td class="row1">Page</td><td class="row1">Month</td><td class="row1">Year</td></tr>';
	$articles = get_row_from_db("newsletters","ORDER BY id desc");
	$row = 2;
	$i = 1;
	if($articles['num_rows'] == 0) {
		$delete_disabled = "disabled ";
		}
	while ($i <= $articles['num_rows']) {
		$nl_page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = '.$articles[$i]['page'];
		$nl_page_query_handle = $db->query($nl_page_query);
		$nl_page = $nl_page_query_handle->fetch_assoc();
		$content .= '<tr><td class="row'.$row.'"><input type="radio" name="delete" value="'.$articles[$i]['id'].'" />'.$articles[$i]['label'].'</td><td class="row'.$row.'">'.$nl_page['title'].'</td><td class="row'.$row.'">'.$months[$articles[$i]['month']-1].'</td><td class="row'.$row.'">'.$articles[$i]['year'].'</td></tr>';
		$i++;
		if($row == 1) { $row = 2; } else { $row = 1; }
		}
	$content .= '</td></tr>
<tr><td class="row'.$row.'" colspan="4"><input type="submit" value="Delete" '.$delete_disabled.'/></td></tr>
</table>
</form>';
?>