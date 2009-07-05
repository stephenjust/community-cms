<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * @ignore
 */
if (isset($_GET['mode']) && (!defined('SECURITY') || !defined('ROOT') || !defined('ADMIN'))) {
	define('SECURITY',1);
	define('ADMIN',1);
	define('ROOT','../');
	require(ROOT.'config.php');
	require(ROOT.'include.php');
	require(ROOT.'functions/admin.php');
	initialize('ajax');
	$ajax = 1;
} else {
	$current_mode = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
	unset($_GET['mode']);
	if (!defined('SECURITY') || !defined('ADMIN')) {
		exit;
	}
}
// Check permission
if (!$acl->check_permission('adm_plugins')) {
	echo 'You do not have permission to access this module.<br />'."\n";
	exit;
}

// Validate $mode
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
if ($mode != ('install' || 'remove' || 'manage')) {
	$mode = NULL;
}

switch ($mode) {
	default:
		// Give list of modes
		$menu_items = array('manage' => 'Manage Plugins',
			'install' => 'Install Plugin',
			'remove' => 'Remove Plugin');
		break;

// ----------------------------------------------------------------------------

	case 'install':
		echo '<h1>Install Plugin</h1>'."\n";
		$content = NULL;
		$install = (isset($_GET['action'])) ? $_GET['action'] : NULL;
		if ($install != 'install') {
			$form = new form;
			$form->set_target('admin.php?ui=1&amp;module=plugins&amp;mode=install&amp;action=install');
			$form->set_method('post');
			$form->add_file('plugin_file','Upload Plugin File');
			$form->add_checkbox('upload_only','Upload Only',1,'disabled');
			$form->add_submit('install','Install Plugin');
			$content .= $form;
		} else {

		}
		unset($install);
		unset($form);
		echo $content;
		break;

// ----------------------------------------------------------------------------

	case 'remove':
		echo '<h1>Remove Plugin</h1>'."\n";
		echo '';
		break;

// ----------------------------------------------------------------------------

	case 'manage':
		echo '<h1>Manage Plugins</h1>'."\n";
		echo '<table class="admintable" id="plugintable">'."\n";
		echo "\t".'<tr>'."\n";
		echo "\t\t".'<th width="20px">&nbsp;</th>'."\n";
		echo "\t\t".'<th>Plugin Name</th>'."\n";
		echo "\t\t".'<th>Version</th>'."\n";
		echo "\t\t".'<th>Status</th>'."\n";
		echo "\t\t".'<th>Author</th>'."\n";
		echo "\t".'</tr>'."\n";

		// FIXME: Read plugin info from database
		$plugin_info_query = 'SELECT `plugin_id`,`plugin_db_table`,
			`plugin_name`,`plugin_author`,`plugin_type`,`plugin_description`,
			`plugin_directory`,`plugin_version` FROM `' . PLUGIN_TABLE . '`';
		$plugin_info_handle = $db->sql_query($plugin_info_query);
		if ($db->error[$plugin_info_handle] === 1) {
			echo "\t".'<tr>'."\n";
			echo "\t\t".'<td colspan="5">Failed to load plugin information.</td>'."\n";
			echo "\t".'</tr>'."\n";
		} else {
			if ($db->sql_num_rows($plugin_info_handle) === 0) {
				echo "\t".'<tr>'."\n";
				echo "\t\t".'<td colspan="5">There are no plugins currently installed</td>'."\n";
				echo "\t".'</tr>'."\n";
			} else {
				for ($i = 0; $i < $db->sql_num_rows($plugin_info_handle); $i++) {
					$plugin_info = $db->sql_fetch_assoc($plugin_info_handle);
					echo "\t".'<tr>'."\n";
					echo "\t\t".'<td><input type="checkbox" value="'.$plugin_info['plugin_id'].'" /></td>'."\n";
					echo "\t\t".'<td>'.$plugin_info['plugin_name'].'</td>'."\n";
					echo "\t\t".'<td>'.$plugin_info['plugin_version'].'</td>'."\n";
					echo "\t\t".'<td>Installed</td>'."\n";
					echo "\t\t".'<td>'.$plugin_info['plugin_author'].'</td>'."\n";
					echo "\t".'</tr>'."\n";
				}
				unset($plugin_info);
			}
		}

		echo '</table>'."\n";
		break;
}

if (isset($ajax)) {
	clean_up();
}

?>
