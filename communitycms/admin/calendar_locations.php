<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;

if (!$acl->check_permission('adm_calendar_locations'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

// This module does not work with PostgreSQL
if ($db->dbms == 'postgresql')
	throw new AdminException('The locations feature does not work with the PostgreSQL database engine.');

/**
 * Include functions necessary to perform operations on this page
 */
include (ROOT.'functions/calendar.php');
require_once(ROOT.'includes/content/CalLocation.class.php');

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
	default:

		break;

	case 'new':
		if (!isset($_POST['location'])) {
			echo '<span class="errormessage">No location given.</span><br />'."\n";
			break;
		}
		try {
			CalLocation::save($_POST['location']);
			echo 'Successfully created new location entry.<br />'."\n";
		}
		catch (CalLocationException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
		}
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		if (!isset($_POST['loc_del'])) {
			echo 'There is no location selected for deletion.<br />'."\n";
			break;
		}
		try {
			CalLocation::delete($_POST['loc_del']);
			echo 'Deleted location.<br />';
		}
		catch (CalLocationException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
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

echo $tab_layout;
?>