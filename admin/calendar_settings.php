<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
// Delete category if requested.
if ($_GET['action'] == 'delete_category') {
	$check_category_query = 'SELECT * FROM '. CALENDAR_CATEGORY_TABLE .'
		WHERE cat_id = '.$_POST['delete_category_id'].' LIMIT 1';
	$check_category_handle = $db->sql_query($check_category_query);
	if ($db->error[$check_category_handle] === 1) {
		$content .= 'Failed to check if category exists.<br />';
	}
	if ($db->sql_num_rows($check_category_handle) == 1) {
		$delete_category_query = 'DELETE FROM '.CALENDAR_CATEGORY_TABLE.'
			WHERE cat_id = '.(int)$_POST['delete_category_id'];
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category] === 1) {
			$content .= 'Failed to delete category.<br />';
		} else {
			$check_category = $db->sql_fetch_assoc($check_category_handle);
			$content .= 'Successfully deleted category. '.log_action('Deleted category \''.$check_category['label'].'\'');
		}
	} else {
		$content .= 'Failed to find the category that you are trying to delete.';
	}
} // IF 'delete_category'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'create_category') {
	$category_name = addslashes($_POST['category_name']);
	if ($category_name != "") {
		$create_category_query = 'INSERT INTO ' . CALENDAR_CATEGORY_TABLE . '
			(label,colour) VALUES (\''.$category_name.'\',\''.$_POST['colour'].'\')';
		$create_category = $db->sql_query($create_category_query);
		if($db->error[$create_category] === 1) {
			$content = 'Failed to create category \''.$category_name.'\' ';
		} else {
			$content = 'Successfully created category. '.log_action('New category \''.$category_name.'\'');
		}
	} else {
		$content = 'You did not provide a name for your new category.';
	}
} // IF 'create_category'

// ----------------------------------------------------------------------------

$content .= '<form method="POST" action="?module=calendar_settings&action=create_category">
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
$category_query = 'SELECT * FROM ' . CALENDAR_CATEGORY_TABLE;
$category_handle = $db->sql_query($category_query);
for ($i = 1; $i <= $db->sql_num_rows($category_handle); $i++) {
	$cat = $db->sql_fetch_assoc($category_handle);
	$content .= '<input type="radio" name="delete_category_id" value="'.$cat['cat_id'].'" />
		<img src="./admin/templates/default/images/icon_'.$cat['colour'].'.png"
		width="16px" height="16px" alt="'.$cat['colour'].'" />'.$cat['label'].'<br />';
}

$content .= '</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1">
<input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>