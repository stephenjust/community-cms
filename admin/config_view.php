<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;

if (!$acl->check_permission('adm_config_view'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

// ----------------------------------------------------------------------------

// Get all configuration values
$config_query = 'SELECT `config_name`,`config_value`
	FROM `'.CONFIG_TABLE.'`
	ORDER BY `config_name` ASC';
$config_handle = $db->sql_query($config_query);
if ($db->error[$config_handle] === 1)
	throw new AdminException('Failed to read configuration values.');

// Populate an array with the configuration values
$num_entries = $db->sql_num_rows($config_handle);
$config_table_values = array();
for ($i = 1; $i <= $num_entries; $i++) {
	$next_row = $db->sql_fetch_row($config_handle);
	$next_row[0] = HTML::schars($next_row[0]);
	$next_row[1] = HTML::schars($next_row[1]);
	$config_table_values[] = $next_row;
}

// Draw the interface
$tab_layout = new tabs;
$tab_layout->add_tab('View Configuration',create_table(array('Name','Value'),$config_table_values));

echo $tab_layout;
?>