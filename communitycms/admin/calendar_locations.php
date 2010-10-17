<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_calendar_locations')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

// This module does not work with PostgreSQL
if ($db->dbms == 'postgresql') {
	$content = '<span class="errormessage">The locations feature does not work with the PostgreSQL database engine</span>'."\n";
	return true;
}

$content = NULL;

/**
 * Include functions necessary to perform operations on this page
 */
include (ROOT.'functions/calendar.php');

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
	default:

		break;

	case 'new':
		if (!isset($_POST['location'])) {
			$content .= 'No location given.<br />'."\n";
			break;
		}
		$check_dupe_query = 'SELECT `value` FROM `'.LOCATION_TABLE.'`
			WHERE `value` = \''.addslashes($_POST['location']).'\'';
		$check_dupe_handle = $db->sql_query($check_dupe_query);
		if ($db->error[$check_dupe_handle] === 1) {
			$content .= 'Failed to check for duplicate entries.<br />'."\n";
			break;
		}
		if ($db->sql_num_rows($check_dupe_handle) != 0) {
			$content .= 'The location you are trying to add already exists.<br />'."\n";
			break;
		}
		$new_loc_query = 'INSERT INTO `'.LOCATION_TABLE.'`
			(`value`) VALUES (\''.addslashes($_POST['location']).'\')';
		$new_loc_handle = $db->sql_query($new_loc_query);
		if ($db->error[$new_loc_handle] === 1) {
			$content .= 'Failed to create new location.<br />'."\n";
			break;
		}
		$content .= 'Successfully created location.<br />'."\n";
		log_action('Created new location');
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		if (!isset($_POST['loc_del'])) {
			$content .= 'There is no location selected for deletion.<br />'."\n";
			break;
		}
		$del_query = 'DELETE FROM `'.LOCATION_TABLE.'` WHERE `id` = '.(int)$_POST['loc_del'];
		$del_handle = $db->sql_query($del_query);
		if ($db->error[$del_handle] === 1) {
			$content .= 'Failed to delete location.<br />'."\n";
			break;
		}
		$content .= 'Deleted location.<br />'."\n";
		log_action('Deleted location');
		break;
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$tab_content['manage'] = NULL;
$tab_content['manage'] .= '<form method="post" action="?module=calendar_locations&action=new">
New Location: <input type="text" name="location" /><input type="submit" value="Create" /></form>';
$tab_content['manage'] .= '<form method="post" action="?module=calendar_locations&action=delete">
<table class="admintable">
<tr><th width="1px">&nbsp;</th><th>Location:</th></tr>';
$rowcount = 1;
$loc_query = 'SELECT * FROM `' . LOCATION_TABLE . '`
	ORDER BY `value` ASC';
$loc_handle = $db->sql_query($loc_query);
$delete_disabled = NULL;
if ($db->sql_num_rows($loc_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="2" class="row1">There are no saved locations.</td></tr>';
	$delete_disabled = ' disabled';
	$rowcount = 2;
}
for ($i = 1; $i <= $db->sql_num_rows($loc_handle); $i++) {
	$loc = $db->sql_fetch_assoc($loc_handle);
	$tab_content['manage'] .= '<tr><td class="row'.$rowcount.'">
		<input type="radio" name="loc_del" value="'.$loc['id'].'" /></td>
		<td class="row'.$rowcount.'">'.$loc['value'].'</td></tr>';

	// Alternate row styles
	if ($rowcount == 1) {
		$rowcount = 2;
	} else {
		$rowcount = 1;
	}
}
$tab_content['manage'] .= '<tr>
	<td class="row'.$rowcount.'" colspan="2"><input type="submit" value="Delete" '.$delete_disabled.'/></td></tr>
</table>
</form>';
$tab_layout->add_tab('Manage Locations',$tab_content['manage']);

// ----------------------------------------------------------------------------

$content .= $tab_layout;
?>