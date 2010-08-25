<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**
 * Check if a library of functions is present
 *
 * This function allows more complex verification without turning the install
 * file into a mess. Tests for specific library versions could be tested for
 * later, or tests for specific functions that would impair functionality if
 * not present.
 *
 * @param string $library Library name
 * @return boolean
 */
function check_library($library) {
	switch ($library) {
		default:
			return false;
			break;

		case 'mysqli':
			if (function_exists('mysqli_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'postgresql':
			if (function_exists('pg_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'gd':
			if (function_exists('imageCreateTrueColor')) {
				return true;
			} else {
				return false;
			}
	}
}

/**
 * Write to config.php
 *
 * Using this function allows the formatting of the configuration file to be
 * separated from the installation script and removes the need to maintain
 * the formatting for the configuration file in multiple locations.
 *
 * @param string $engine Database engine
 * @param string $host Database host
 * @param integer $port Database host port
 * @param string $database_name Database name
 * @param string $database_user Database user
 * @param string $password Database user's password
 * @param string $table_prefix Prefix for database tables
 * @return boolean
 */
function config_file_write($engine,$host,$port,$database_name,
		$database_user,$password,$table_prefix) {
	// Validate parameters
	if (!is_numeric($port)) {
		return false;
	}
	$port = (int)$port;
	$engine = addslashes($engine);
	$host = addslashes($host);
	$database_name = addslashes($database_name);
	$database_user = addslashes($database_user);
	$password = addslashes($password);
	$table_prefix = addslashes($table_prefix);

	$config_file = ROOT.'config.php';

	if (!file_exists($config_file)) {
		return false;
	}

	$file_handle = fopen($config_file,'w');
	if (!$file_handle) {
		// Failed to open file for writing
		return false;
	}

	$config_file = <<< END
<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
// Turn of 'register_globals'
ini_set('register_globals',0);
\$CONFIG['SYS_PATH'] = 'Unused'; // Path to Community CMS on server
\$CONFIG['db_engine'] = '$engine'; // Database Engine
\$CONFIG['db_host'] = '$host'; // Database server host (usually localhost)
\$CONFIG['db_host_port'] = $port; // Database server port (default 3306 for mysqli)
\$CONFIG['db_user'] = '$database_user'; // Database user
\$CONFIG['db_pass'] = '$password'; // Database password
\$CONFIG['db_name'] = '$database_name'; // Database
\$CONFIG['db_prefix'] = '$table_prefix'; // Database table prefix

// Set the value below to '1' to disable Community CMS
\$CONFIG['disabled'] = 0;
?>
END;
	if (fwrite($file_handle,$config_file)) {
		fclose($file_handle);
		return true;
	} else {
		fclose($file_handle);
		return false;
	}
}

function update_permission_records() {
	global $db;

	// Define list of permissions
	// $permission[id] = array('name'=>'','label'=>'','description'=>'','default'=>'')
	$permission = array();
	$permission[] = array('all','All Permissions','Grant this permission to allow all actions within the CMS',0);
	$permission[] = array('admin_access','Admin Access','Allow a user to access the administrative section of the CMS',0);
	$permission[] = array('set_permissions','Set Permissions','Allow a user to modify the permission settings for user groups',0);
	$permission[] = array('adm_help','Admin Help Module','Allow a user to access the help module',0);
	$permission[] = array('adm_feedback','Admin Feedback Module','Allow a user to access the admin feedback module',0);
	$permission[] = array('adm_site_config','Site Configuration','Allow a user to modify the CMS configuration',0);
	$permission[] = array('adm_block_manager','Block Module','Allow a user to access the block manager module',0);
	$permission[] = array('adm_calendar','Calendar Module','Allow a user to access the calendar manager module',0);
	$permission[] = array('adm_calendar_edit_date','Calendar Edit','Allow a user to access the calendar edit module',0);
	$permission[] = array('adm_calendar_import','Import Events','Allow a user to import calendar events',0);
	$permission[] = array('adm_calendar_locations','Manage Locations','Allow a user to manage calendar locations',0);
	$permission[] = array('adm_contacts_manage','Contacts Module','Allow a user to access the contacts manager module',0);
	$permission[] = array('adm_filemanager','File Manager','Allow a user to access the file manager module',0);
	$permission[] = array('adm_gallery_manager','Gallery Manager','Allow a user to access the gallery manager module',0);
	$permission[] = array('adm_gallery_settings','Gallery Settings','Allow a user to configure image galleries',0);
	$permission[] = array('adm_news','News Module','Allow a user to access the news article module',0);
	$permission[] = array('adm_news_settings','News Settings','Allow a user to configure news settings',0);
	$permission[] = array('adm_newsletter','Newsletter Module','Allow a user to access the newsletter module',0);
	$permission[] = array('adm_page','Page Module','Allow a user to access the page manager module',0);
	$permission[] = array('adm_page_message','Page Message Module','Allow a user to access the page message module',0);
	$permission[] = array('adm_page_message_edit','Edit Page Messages','Allow a user to edit page messages',0);
	$permission[] = array('adm_page_message_new','New Page Messages','Allow a user to create new page messages',0);
	$permission[] = array('adm_poll_manager','Poll Manager Module','Allow a user to access the poll manager module',0);
	$permission[] = array('adm_poll_new','Create Poll','Allow a user to create a new poll',0);
	$permission[] = array('adm_poll_results','Poll Results','Allow a user to see the results of polls',0);
	$permission[] = array('adm_user','User Module','Allow a user to access the user manager module',0);
	$permission[] = array('adm_user_edit','Edit User Module','Allow a user to access the edit user module',0);
	$permission[] = array('adm_user_groups','User Groups Module','Allow a user to access the user groups module',0);
	$permission[] = array('adm_log_view','View Logs','Allow a user to access the admin activity logs',0);
	$permission[] = array('adm_config_view','View Configuration','Allow a user to view all of the CMS configuration values',0);
	$permission[] = array('block_create','Create Blocks','Allow a user to create new blocks',0);
	$permission[] = array('block_delete','Delete Blocks','Allow a user to delete blocks',0);
	$permission[] = array('calendar_settings','Calendar Settings','Allow a user to modify calendar settings',0);
	$permission[] = array('file_create_folder','Create Folders','Allow a user to create new folders',0);
	$permission[] = array('file_upload','Upload Files','Allow a user to upload files',0);
	$permission[] = array('log_clear','Clear Logs','Allow a user to clear all log messages',0);
	$permission[] = array('log_post_custom_message','Post Custom Log Messages','Allow a user to post custom log messages',0);
	$permission[] = array('news_create','Create Articles','Allow a user to create new news articles',0);
	$permission[] = array('news_delete','Delete Articles','Allow a user to delete news articles',0);
	$permission[] = array('news_edit','Edit Articles','Allow a user to edit news articles',0);
	$permission[] = array('news_fe_manage','Manage News from Front-End','Allow a user to manage news articles from the front-end',0);
	$permission[] = array('news_fe_show_unpublished','Show Unpublished News on Site','Allow a user to see unpublished articles from the site front-end',0);
	$permission[] = array('news_publish','Publish/Unpublish Articles','Allow a user to publish or unpublish news articles',0);
	$permission[] = array('newsletter_create','Create Newsletter','Allow a user to create a new newsletter',0);
	$permission[] = array('newsletter_delete','Delete Newsletter','Allow a user to delete a newsletter',0);
	$permission[] = array('show_fe_errors','Show Front-End Errors','Allow a user to view error messages in the CMS front-end that would normally be hidden from users',0);
	$permission[] = array('page_set_home','Change Default Page','Allow a user to change the defualt CMS page',0);
	$permission[] = array('page_order','Change Page Order','Allow a user to rearrange pages on the CMS menu',0);
	$permission[] = array('pagegroupedit-1','Edit Page Group \'Default Group\'','Allow user to edit pages in the group \'Default Group\'',0);
	$permission[] = array('user_create','Create User','Allow a user to create new users',0);
	$permission[] = array('user_delete','Delete User','Allow a user to delete other users',0);
	$permission[] = array('group_create','Create User Groups','Allow a user to create a new user group',0);

	// Get list of current permissions
	$list_query = 'SELECT `acl_id`,`acl_name`,`acl_longname`,
		`acl_description`,`acl_value_default`
		FROM `'.ACL_KEYS_TABLE.'` ORDER BY `acl_id` ASC';
	$list_handle = $db->sql_query($list_query);
	if ($db->error[$list_handle] === 1) {
		return false;
	}

	// Compare existing permissions to permission list above
	for ($i = 1; $i < $db->sql_num_rows($list_handle); $i++) {
		$list = $db->sql_fetch_assoc($list_handle);
		// Scan through each permission record for changes
		for ($j = 0; $j < count($permission); $j++) {
			// Check if the permission already exists
			if ($list['acl_name'] == $permission[$j][0]) {
				// Check if all of its parameters are the same
				if ($list['acl_longname'] == $permission[$j][1] &&
						$list['acl_description'] == $permission[$j][2] &&
						$list['acl_value_default'] == $permission[$j][3]) {
				} else {
					// Parameters are different so update
					$update_query = 'UPDATE TABLE `'.ACL_KEYS_TABLE.'`
						SET `acl_longname` = \''.addslashes($permission[$j][1]).'\',
						`acl_description` = \''.addslashes($permission[$j][2]).'\',
						`acl_value_default` = '.(int)$permission[$j][3].'
						WHERE `acl_id` = '.$list['acl_id'];
					$update = $db->sql_query($update_query);
					if ($db->error[$update_query] === 1) {
						return false;
					}
				}
				unset($permission[$j]);
			}
		}
	}
	// Create permissions that still don't exist
	foreach ($permission AS $current_permission) {
		$create_query = 'INSERT INTO `'.ACL_KEYS_TABLE.'`
			(`acl_name`,`acl_longname`,`acl_description`,`acl_value_default`)
			VALUES
			(\''.addslashes($current_permission[0]).'\',
			\''.addslashes($current_permission[1]).'\',
			\''.addslashes($current_permission[2]).'\',
			'.(int)$current_permission[3].')';
		$create_handle = $db->sql_query($create_query);
		if ($db->error[$create_handle] === 1) {
			return false;
		}
	}
	return true;
}
?>
