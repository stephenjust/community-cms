<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	// Delete category if requested.
	if ($_GET['action'] == 'delete_category') {
		$delete_category_query = 'DELETE FROM '.$CONFIG['db_prefix'].'calendar_categories WHERE id = '.$_GET['delete_category_id'];
		$delete_category = $db->query($delete_category_query);
		if(!$delete_category) {
			$message = 'Failed to delete category. '.mysqli_error();
			} else {
			$message = 'Successfully deleted category.';
			}
		}
	// Create new category if requested.
	if ($_GET['action'] == 'create_category') {
		$category_name = addslashes($_POST['category_name']);
		if($category_name != "") {
			$create_category_query = 'INSERT INTO '.$CONFIG['db_prefix'].'calendar_categories (label) VALUES (\''.$category_name.'\')';
			$create_category = $db->query($create_category_query);
			if(!$create_category) {
				$message = 'Failed to create category \''.$_POST['category'].'\' '.mysqli_error();
				} else {
				$message = 'Successfully created category.';
				}
			} else {
			$message = 'You did not provide a name for your new category.';
			}
		}
$content = $message.'<form method="POST" action="?module=calendar_settings&action=create_category">
<h1>Create New Category</h1>
<table class="admintable">
<tr><td width="150" class="row1">Name:</td><td class="row1"><input type=\'text\' name=\'category_name\' /></td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Create" /></td></tr>
</table>
</form>

<form method="GET" action="?module=calendar_settings&action=delete_category">
<h1>Delete Category</h1>
<table class="admintable">
<tr><td width="150" class="row1">Category:</td><td class="row1">&nbsp;</td></tr>
<tr><td colspan="2" class="row2">';
 	$cat = get_row_from_db("calendar_categories");
 	$i = 1;
	while ($i <= $cat['num_rows']) {
		$content = $content.'<input type="radio" name="delete_category_id" value="'.$cat[$i]['id'].'" />'.$cat[$i]['label'].'<br />';
		$i++;
	}

$content = $content.'</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>