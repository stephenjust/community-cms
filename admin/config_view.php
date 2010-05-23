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

$config_query = 'SELECT `config_name`,`config_value` FROM `' . CONFIG_TABLE . '`
	ORDER BY `config_name` ASC';
$config_handle = $db->sql_query($config_query);
if ($db->error[$config_handle] === 1) {
	$content .= 'Failed to read configuration values.<br />';
}
$i = 1;
$num_entries = $db->sql_num_rows($config_handle);
$config_table_values = array();
for ($i = 1; $i <= $num_entries; $i++) {
	$next_row = $db->sql_fetch_row($config_handle);
	$next_row[0] = stripslashes($next_row[0]);
	$next_row[1] = htmlentities(stripslashes($next_row[1]));
	$config_table_values[] = $next_row;
}
$tab_content['view'] = create_table(array('Name','Value'),
		$config_table_values);
$tab_layout = new tabs;
$tab_layout->add_tab('View Configuration',$tab_content['view']);

$content .= $tab_layout;
?>