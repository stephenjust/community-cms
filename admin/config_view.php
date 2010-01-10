<?php
/**
 * Community CMS
 * $Id: log_view.php 444 2009-11-07 22:57:23Z stephenjust $
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

if (!$acl->check_permission('adm_config_view')) {
	$content .= 'You do not have the necessary permissions to access this module.';
	return true;
}

// ----------------------------------------------------------------------------

$config_query = 'SELECT * FROM `' . CONFIG_TABLE . '`
	ORDER BY `config_name` ASC';
$config_handle = $db->sql_query($config_query);
if ($db->error[$config_handle] === 1) {
	$content .= 'Failed to read configuration values.<br />';
}
$i = 1;
$num_entries = $db->sql_num_rows($config_handle);
$tab_content['view'] = '<table class="admintable">
	<tr><th>Name</th><th>Value</th></tr>';
if ($num_entries == 0) {
	$tab_content['view'] .= '<tr class="row1">
		<td colspan="2">No configuration values</td>
		</tr>';
}
$rowtype = 1;
while ($i <= $num_entries) {
	$config_list = $db->sql_fetch_assoc($config_handle);
	$tab_content['view'] .= '<tr class="row'.$rowtype.'">
		<td>'.stripslashes($config_list['config_name']).'</td>
		<td>'.htmlentities(stripslashes($config_list['config_value'])).'</td>
		</tr>';
	if ($rowtype == 1) {
		$rowtype = 2;
	} else {
		$rowtype = 1;
	}
	$i++;
}
$tab_content['view'] .= '</table>';
$tab_layout = new tabs;
$tab_layout->add_tab('View Configuration',$tab_content['view']);

$content .= $tab_layout;
?>