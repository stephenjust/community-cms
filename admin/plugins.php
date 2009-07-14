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
		// Check if temp and plugin directories are writable
		if (!is_writable(ROOT . 'tmp/') || !is_writable(ROOT . 'plugins/')) {
			echo '<span class="error">A required directory is not writable.'."\n";
			echo 'Check the permissions of <em>/tmp</em> and <em>/plugins</em></span>.<br />'."\n";
			break;
		}
		$install = (isset($_GET['action'])) ? $_GET['action'] : NULL;
		if ($install != 'install') {
			$form = new form;
			$form->set_target('admin.php?ui=1&amp;module=plugins&amp;mode=install&amp;action=install&amp;upload=plugin_file');
			$form->set_method('post');
			$form->add_file('plugin_file','Upload Plugin File');
			$form->add_checkbox('upload_only','Upload Only',1,'disabled');
			$form->add_submit('install','Install Plugin');
			$content .= $form;
		} else {
			if (!isset($_GET['file']) || $_GET['file'] == NULL) {
				echo 'Failed to upload your plugin file.<br />'."\n";
				break;
			}
			if (eregi('/',$_GET['file'])) {
				echo 'The file name submitted contains an invalid character.<br />'."\n";
				break;
			}
			if (file_exists(ROOT . 'tmp/' . $_GET['file'])) {
				$filename = $_GET['file'];
				$file_path = ROOT . 'tmp/' . $filename;
				echo 'Uploaded plugin file.<br />'."\n";
				echo 'Verifying file type... ';
				if (!eregi('\.tar\.gz$',$filename)) {
					echo 'FAILED<br />'."\n";
					unlink($file_path);
					echo 'Deleted temporary file.<br />'."\n";
					break; 
				} else {
					echo 'Success.<br />'."\n";
				}
				echo 'Attempting to extract plugin file... ';

				// Extract TAR
				$tar_archive = new Archive_Tar($file_path);
				$extract = $tar_archive->extract(ROOT . 'tmp');
				if (!$extract) {
					echo 'FAILED<br />'."\n";
					unlink($file_path);
					echo 'Deleted temporary file.<br />'."\n";
					break; 
				} else {
					echo 'Success.<br />'."\n";
				}
				$plugin_folder_name = str_replace('.tar.gz',NULL,$filename);
				if (!file_exists(ROOT . 'tmp/' . $plugin_folder_name)) {
					echo 'This archive does not contain a plugin directory.<br />'."\n";
					break;
				}
				if (!is_dir(ROOT . 'tmp/' . $plugin_folder_name)) {
					echo 'The extracted file is not a directory.<br />'."\n";
					unlink($file_path);
					unlink(ROOT . 'tmp/' . $plugin_folder_name);
					echo 'Deleted temporary files.<br />'."\n";
				}
				// TODO: Attempt to install plugin - first, look for plugin info file
			}
		}
		unset($install);
		unset($form);
		echo $content;
		break;

// ----------------------------------------------------------------------------

	case 'remove':
		echo '<h1>Remove Plugin</h1>'."\n";
		// TODO: Remove Plugins
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
		// TODO: Do something with plugin list
		break;
}

if (isset($ajax)) {
	clean_up();
}

?>
