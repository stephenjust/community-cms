<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	// Delete category if requested.
	if ($_GET['action'] == 'delete_category') {
		$check_category_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar_categories WHERE cat_id = '.$_POST['delete_category_id'].' LIMIT 1';
		$check_category_handle = $db->query($check_category_query);
		if(!$check_category_handle) {
			$message .= 'Failed to check if category exists.<br />'.mysqli_error($db);
			}
		if($check_category_handle->num_rows == 1) {
			$delete_category_query = 'DELETE FROM '.$CONFIG['db_prefix'].'calendar_categories WHERE cat_id = '.$_POST['delete_category_id'];
			$delete_category = $db->query($delete_category_query);
			if(!$delete_category) {
				$message .= 'Failed to delete category. '.mysqli_error();
				} else {
				$check_category = $check_category_handle->fetch_assoc();
				$message .= 'Successfully deleted category. '.log_action('Deleted category \''.$check_category['label'].'\'');
				}
			} else {
			$message .= 'Failed to find the category that you are trying to delete.';
			}
		}
	// Create new category if requested.
	if ($_GET['action'] == 'create_category') {
		$category_name = addslashes($_POST['category_name']);
		if($category_name != "") {
			$create_category_query = 'INSERT INTO '.$CONFIG['db_prefix'].'calendar_categories (label,colour) VALUES (\''.$category_name.'\',\''.$_POST['colour'].'\')';
			$create_category = $db->query($create_category_query);
			if(!$create_category) {
				$message = 'Failed to create category \''.$category_name.'\' '.mysqli_error();
				} else {
				$message = 'Successfully created category. '.log_action('New category \''.$category_name.'\'');
				}
			} else {
			$message = 'You did not provide a name for your new category.';
			}
		}
$content = $message.'<form method="POST" action="?module=calendar_settings&action=create_category">
<h1>Create New Category</h1>
<table class="admintable">
<tr><td width="150" class="row1">Name:</td><td class="row1"><input type=\'text\' name=\'category_name\' /></td></tr>
<tr><td width="150" class="row2">Colour:</td><td class="row2">
<input type="radio" name="colour" value="red" /><img src="./admin/templates/default/images/icon_red.png" width="16px" height="16px" alt="Red" />
<input type="radio" name="colour" value="green" /><img src="./admin/templates/default/images/icon_green.png" width="16px" height="16px" alt="Green" />
<input type="radio" name="colour" value="blue" /><img src="./admin/templates/default/images/icon_blue.png" width="16px" height="16px" alt="Blue" /><br />
<input type="radio" name="colour" value="purple" /><img src="./admin/templates/default/images/icon_purple.png" width="16px" height="16px" alt="Purple" />
<input type="radio" name="colour" value="cyan" /><img src="./admin/templates/default/images/icon_cyan.png" width="16px" height="16px" alt="Cyan" />
<input type="radio" name="colour" value="yellow" /><img src="./admin/templates/default/images/icon_yellow.png" width="16px" height="16px" alt="Yellow" />
</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Create" /></td></tr>
</table>
</form>

<form method="POST" action="?module=calendar_settings&action=delete_category">
<h1>Delete Category</h1>
<table class="admintable">
<tr><td width="150" class="row1">Category:</td><td class="row1">&nbsp;</td></tr>
<tr><td colspan="2" class="row2">';
 	$cat = get_row_from_db("calendar_categories");
 	$i = 1;
	while ($i <= $cat['num_rows']) {
		$content = $content.'<input type="radio" name="delete_category_id" value="'.$cat[$i]['cat_id'].'" /><img src="./admin/templates/default/images/icon_'.$cat[$i]['colour'].'.png" width="16px" height="16px" alt="'.$cat[$i]['colour'].'" />'.$cat[$i]['label'].'<br />';
		$i++;
	}

$content = $content.'</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>