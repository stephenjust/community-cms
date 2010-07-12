<?php
define('SECURITY',1);
define('ROOT','../');
require('../config.php');
require('../include.php');
initialize();
echo "<html>\n<head>\n<title>Community CMS Database Update</title>\n</head><body>";
$query = array();
$error = 0;

$db_version = $_GET['old_ver'];

switch ($db_version) {

// ----------------------------------------------------------------------------
// QUERY ARRAY (VERSION 0.01 -> 0.02)
// ----------------------------------------------------------------------------
	case 0.01:
		$query[] = 'DROP TABLE IF EXISTS '.$CONFIG['db_prefix'].'admin_pages';
		$query[] = 'DROP TABLE IF EXISTS '.$CONFIG['db_prefix'].'permissions';
		$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'pages ADD `text_id` TEXT NULL AFTER `id`';
		$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'pages ADD `meta_desc` TEXT NULL AFTER `title`';
		$query[] = 'CREATE TABLE IF NOT EXISTS '.$CONFIG['db_prefix'].'user_groups (
			`id` int(5) NOT NULL auto_increment,
			`name` text NOT NULL,
			`label_format` text NOT NULL,
			PRIMARY KEY (`id`)
		 ) ENGINE=MyISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO '.$CONFIG['db_prefix'].'user_groups
		 (`name`,`label_format`) VALUES
		 ("Administrator","font-weight: bold; color: #009900;")';
		$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'users ADD `groups` TEXT NULL AFTER `password`';
		$query[] = 'UPDATE '.$CONFIG['db_prefix'].'users SET `groups` = "1" WHERE `id` = 1 LIMIT 1';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'news_settings` (
			`default_date_setting` INT(3) NOT NULL ,
			`show_author` INT(3) NOT NULL ,
			`show_edit_time` INT(3) NOT NULL ,
			`num_articles` INT(3) NOT NULL
		) ENGINE = MYISAM CHARACTER SET=utf8';
		$query[] = 'ALTER TABLE `'.$CONFIG['db_prefix'].'pages`
			ADD `hidden` INT(1) NOT NULL AFTER `blocks_right`';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'calendar_settings` (
			`default_view` TEXT NOT NULL ,
			`month_show_stime` BOOL NOT NULL DEFAULT \'1\',
			`month_show_cat_icons` BOOL NOT NULL DEFAULT \'1\',
			`month_day_format` INT NOT NULL DEFAULT \'1\'
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO `'.$CONFIG['db_prefix'].'calendar_settings` (`default_view`, `month_show_stime`, `month_show_cat_icons`, `month_day_format`) VALUES
			(\'month\',1,1,1)';
		$query[] = 'INSERT INTO `'.$CONFIG['db_prefix'].'news_settings`
			(`default_date_setting` ,`show_author` ,`show_edit_time` ,`num_articles`) VALUES
			(\'1\', \'1\', \'1\', \'10\')';
		$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'config ADD `admin_email` TEXT NULL AFTER `url`';
		$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'config ADD `time_format` TEXT NULL AFTER `comment`';
		$query[] = 'UPDATE '.$CONFIG['db_prefix'].'config SET `time_format` = \'h:i A\'';

		$query[] = 'CREATE TABLE `'.$CONFIG['db_prefix'].'contacts` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`user_id` INT NOT NULL ,
			`name` TEXT NOT NULL ,
			`phone` CHAR( 11 ) NOT NULL ,
			`address` TEXT NOT NULL ,
			`email` TEXT NOT NULL ,
			`phone_hide` BOOL NOT NULL ,
			`address_hide` BOOL NOT NULL ,
			`email_hide` BOOL NOT NULL ,
			`title` TEXT NOT NULL
		) ENGINE=MYISAM CHARACTER SET=utf8';

		// ACL
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'acl` (
			`acl_record_id` INT NOT NULL auto_increment PRIMARY KEY,
			`acl_id` TEXT NOT NULL,
			`group` INT NOT NULL,
			`value` INT(1) NOT NULL DEFAULT 0
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'acl_keys` (
			`acl_id` INT NOT NULL auto_increment PRIMARY KEY,
			`acl_name` TEXT NOT NULL,
			`acl_longname` TEXT NOT NULL,
			`acl_description` TEXT NOT NULL,
			`acl_value_default` INT(1) NOT NULL DEFAULT 0
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO `'.$CONFIG['db_prefix'].'acl` (`acl_id`, `group`, `value`) VALUES
		(1, 1, 1)';
		$query [] = 'INSERT INTO `'.$CONFIG['db_prefix'].'acl_keys` (`acl_name`,`acl_longname`,`acl_description`,`acl_value_default`) VALUES
		(\'all\',\'All Permissions\',\'Grant this permission to allow all actions within the CMS\',0),
		(\'show_fe_errors\',\'Show Front-End Errors\',\'Allow a user to view error messages in the CMS front-end that would normally be hidden from users\',0)';

		$query[] = 'UPDATE '.$CONFIG['db_prefix'].'config SET `db_version` = 0.02';
		execute_queries($query);
		$query = array();
		$db_version = 0.02;
// ----------------------------------------------------------------------------
// QUERY ARRAY (VERSION 0.02 -> 0.03)
// ----------------------------------------------------------------------------
	case 0.02:
		// Get old configuration
		$old_config_query = 'SELECT * FROM `'.CONFIG_TABLE.'`';
		$old_config_handle = $db->sql_query($old_config_query);
		if ($db->error[$old_config_handle] === 1) {
			die ('A database error occured. Please retry the upgrade.');
		}
		if ($db->sql_num_rows($old_config_handle) == 0) {
			die ('Your configuration table is empty. Please reinstall Community CMS.');
		}
		$old_config = $db->sql_fetch_assoc($old_config_handle);

		// Get old calendar configuration
		$old_calconfig_query = 'SELECT * FROM `'.$CONFIG['db_prefix'].'calendar_settings`';
		$old_calconfig_handle = $db->sql_query($old_calconfig_query);
		if ($db->error[$old_calconfig_handle] === 1) {
			die ('A database error occured. Please retry the upgrade.');
		}
		if ($db->sql_num_rows($old_calconfig_handle) == 0) {
			die ('Your calendar configuration table is empty. Please reinstall Community CMS.');
		}
		$old_calconfig = $db->sql_fetch_assoc($old_calconfig_handle);

		switch ($db->dbms) {
			case 'mysqli':
				$query[] = 'DROP TABLE IF EXISTS `'.CONFIG_TABLE.'`';
				$query[] = 'DROP TABLE IF EXISTS `'.$CONFIG['db_prefix'].'calendar_settings`';
				$query[] = 'CREATE TABLE IF NOT EXISTS `'.CONFIG_TABLE.'` (
						`config_name` varchar(255) NOT NULL,
						`config_value` varchar(255) NOT NULL,
						PRIMARY KEY (`config_name`)
					) ENGINE=MyISAM CHARACTER SET=utf8';
				$query[] = 'ALTER TABLE `'.PAGE_TABLE.'`
					ADD `parent` INT NOT NULL DEFAULT \'0\' AFTER `menu`';
				$query[] = 'ALTER TABLE `'.NEWS_TABLE.'`
					ADD `pin` INT(1) NOT NULL DEFAULT \'0\' AFTER `page`';
				break;
			case 'postgresql':
				$query[] = 'DROP TABLE "'.CONFIG_TABLE.'"';
				$query[] = 'DROP TABLE `'.$CONFIG['db_prefix'].'calendar_settings`';
				$query[] = 'CREATE TABLE "'.CONFIG_TABLE.'" (
						"config_name" varchar(255) NOT NULL,
						"config_value" varchar(255) NOT NULL,
						PRIMARY KEY ("config_name"))';
				$query[] = 'ALTER TABLE "'.PAGE_TABLE.'"
					ADD "parent" integer NOT NULL DEFAULT 0 AFTER "menu"';
				$query[] = 'ALTER TABLE "'.NEWS_TABLE.'"
					ADD "pin" integer NOT NULL default 0 AFTER "page"';
				break;
		}
		$query[] = "INSERT INTO `".CONFIG_TABLE."` (`config_name`, `config_value`) VALUES
			('calendar_month_day_format','{$old_calconfig['month_day_format']}'),
			('calendar_default_view','{$old_calconfig['default_view']}'),
			('calendar_month_time_sep',' '),
			('calendar_month_show_cat_icons','{$old_calconfig['month_show_cat_icons']}'),
			('calendar_month_show_stime','{$old_calconfig['month_show_stime']}'),
			('calendar_save_locations','1'),
			('comment','{$old_config['comment']}'),
			('cookie_name','cms_session'),
			('cookie_path','/'),
			('db_version', '0.03'),
			('footer','".addslashes($old_config['footer'])."'),
			('home','{$old_config['home']}'),
			('site_active','{$old_config['active']}'),
			('site_name','".addslashes($old_config['name'])."'),
			('site_template','{$old_config['template']}'),
			('site_url','{$old_config['url']}'),
			('time_format','{$old_config['time_format']}')";
		if (strlen($old_config['admin_email'] > 0)) {
			$query[] = "INSERT INTO `".CONFIG_TABLE."` (`config_name`, `config_value`) VALUES
			('admin_email','{$old_config['admin_email']}')";
		}
		execute_queries($query);
		$query = array();
		$db_version = 0.03;
		echo 'The database has been updated to version 0.03<br />'."\n";
		break;
	case 0.03:
		set_config('db_version','0.04');
		switch ($db->dbms) {
			case 'mysqli':
				$query[] = "CREATE TABLE IF NOT EXISTS `".GALLERY_TABLE."` {
					`id` int(11) NOT NULL auto_increment,
					`title` text NOT NULL,
					`description` text NOT NULL,
					`image_dir` text NOT NULL,
					PRIMARY KEY (`id`)
					} ENGINE=MyISAM CHARACTER SET=utf8";
				break;
			case 'postgresql':
				$query[] = 'CREATE SEQUENCE "'.GALLERY_TABLE.'_id_seq"';
				$query[] = 'CREATE TABLE "'.GALLERY_TABLE.'" {
					"id" integer NOT NULL default nextval(\''.GALLERY_TABLE.'_id_seq\'),
					"title" text NOT NULL,
					"description" text NOT NULL,
					"image_dir" text NOT NULL,
					PRIMARY KEY ("id")
					}';
				$query[] = 'SELECT setval(\''.GALLERY_TABLE.'_id_seq\', (SELECT max("id") FROM "'.GALLERY_TABLE.'"))';
				break;
		}
		$query[] = 'ALTER TABLE `'.NEWS_TABLE.'`
			CHANGE `pin` `priority` int NOT NULL DEFAULT 0';
		execute_queries($query);
		$query = array();
		$db_version = 0.04;
		echo 'The database has been updated to version 0.04<br />'."\n";
		break;
	case 0.04:
		switch ($db->dbms) {
			case 'mysqli':
				$query[] = 'ALTER TABLE `'.PAGE_TABLE.'` ADD `page_group` INT NOT NULL DEFAULT \'1\' AFTER `menu`';
				$query[] = 'ALTER TABLE `'.CALENDAR_TABLE.'` ADD `imported` TEXT NULL DEFAULT NULL AFTER `hidden`';
				$query[] = 'CREATE TABLE IF NOT EXISTS `'.PAGE_GROUP_TABLE.'` (
					`id` INT NOT NULL auto_increment PRIMARY KEY,
					`label` TEXT NOT NULL,
					INDEX (`id`)
					) ENGINE = MYISAM CHARACTER SET=utf8';
				$query[] = 'CREATE TABLE IF NOT EXISTS `'.CALENDAR_SOURCES_TABLE.'` (
					`id` int(11) NOT NULL auto_increment,
					`desc` TEXT NOT NULL,
					`url` TEXT NOT NULL,
					PRIMARY KEY (`id`)
					) ENGINE=MyISAM CHARACTER SET=utf8 ;';
				break;
			case 'postgresql':
				$query[] = 'ALTER TABLE `'.PAGE_TABLE.'` ADD `page_group` integer NOT NULL DEFAULT \'1\' AFTER `menu`';
				$query[] = 'ALTER TABLE `'.CALENDAR_TABLE.'` ADD `imported` text AFTER `hidden`';
				$query[] = 'CREATE SEQUENCE "'.PAGE_GROUP_TABLE.'_id_seq"';
				$query[] = 'CREATE TABLE "'.PAGE_GROUP_TABLE.'" (
					"id" integer NOT NULL default nextval(\''.PAGE_GROUP_TABLE.'_id_seq\') PRIMARY KEY,
					"label" text NOT NULL,
					PRIMARY KEY("id")
					)';
				$query[] = 'SELECT setval(\''.PAGE_GROUP_TABLE.'_id_seq\', (SELECT max(id) FROM "'.PAGE_GROUP_TABLE.'"))';
				$query[] = 'CREATE SEQUENCE "'.CALENDAR_SOURCES_TABLE.'_id_seq"';
				$query[] = 'CREATE TABLE "'.CALENDAR_SOURCES_TABLE.'" (
					"id" integer NOT NULL default nextval(\''.CALENDAR_SOURCES_TABLE.'_id_seq\'),
					"desc" text NOT NULL,
					"url" text NOT NULL,
					PRIMARY KEY ("id")
					)';
				$query[] = 'SELECT setval(\''.CALENDAR_SOURCES_TABLE.'_id_seq\', (SELECT max("id") FROM "'.CALENDAR_SOURCES_TABLE.'"))';
				break;
		}
		$query[] = 'INSERT INTO `'.PAGE_GROUP_TABLE.'` (`label`)
			VALUES (\'Default Group\')';
		$query[] = 'INSERT INTO `'.ACL_KEYS_TABLE.'` (`acl_name`,`acl_longname`,`acl_description`,`acl_value_default`)
			VALUES (\'adm_page\',\'Admin Page Module\',\'Allow a user to access the page manager module\',0),
			(\'admin_access\',\'Admin Access\',\'Allow a user to access the administrative section of the CMS\',0),
			(\'set_permissions\',\'Set Permissions\',\'Allow a user to modify the permission settings for user groups\',0),
			(\'adm_help\',\'Admin Help Module\',\'Allow a user to access the help module\',0),
			(\'adm_feedback\',\'Admin Feedback Module\',\'Allow a user to access the admin feedback module\',0),
			(\'adm_site_config\',\'Site Configuration\',\'Allow a user to modify the CMS configuration\',0),
			(\'adm_filemanager\',\'File Manager\',\'Allow a user to access the file manager module\',0),
			(\'adm_gallery_manager\',\'Gallery Manager\',\'Allow a user to access the gallery manager module\',0),
			(\'adm_gallery_settings\',\'Gallery Settings\',\'Allow a user to configure image galleries\',0),
			(\'adm_news\',\'News Module\',\'Allow a user to access the news article module\',0),
			(\'adm_news_edit_article\',\'News Edit Module\',\'Allow a user to access the news edit module\',0),
			(\'adm_news_settings\',\'News Settings\',\'Allow a user to configure news settings\',0),
			(\'adm_newsletter\',\'Newsletter Module\',\'Allow a user to access the newsletter module\',0),
			(\'adm_block_manager\',\'Block Module\',\'Allow a user to access the block manager module\',0),
			(\'adm_contacts_manage\',\'Contacts Module\',\'Allow a user to access the contacts manager module\',0),
			(\'adm_page\',\'Page Module\',\'Allow a user to access the page manager module\',0),
			(\'adm_page_message\',\'Page Message Module\',\'Allow a user to access the page message module\',0),
			(\'adm_page_message_edit\',\'Edit Page Messages\',\'Allow a user to edit page messages\',0),
			(\'adm_page_message_new\',\'New Page Messages\',\'Allow a user to create new page messages\',0),
			(\'adm_poll_manager\',\'Poll Manager Module\',\'Allow a user to access the poll manager module\',0),
			(\'adm_poll_new\',\'Create Poll\',\'Allow a user to create a new poll\',0),
			(\'adm_poll_results\',\'Poll Results\',\'Allow a user to see the results of polls\',0),
			(\'adm_user\',\'User Module\',\'Allow a user to access the user manager module\',0),
			(\'adm_user_edit\',\'Edit User Module\',\'Allow a user to access the edit user module\',0),
			(\'adm_user_groups\',\'User Groups Module\',\'Allow a user to access the user groups module\',0),
			(\'adm_log_view\',\'View Logs\',\'Allow a user to access the admin activity logs\',0),
			(\'adm_config_view\',\'View Configuration\',\'Allow a user to view all of the CMS configuration values\',0),
			(\'block_create\',\'Create Blocks\',\'Allow a user to create new blocks\',0),
			(\'block_delete\',\'Delete Blocks\',\'Allow a user to delete blocks\',0),
			(\'calendar_settings\',\'Calendar Settings\',\'Allow a user to modify calendar settings\',0),
			(\'log_clear\',\'Clear Logs\',\'Allow a user to clear all log messages\',0),
			(\'log_post_custom_message\',\'Post Custom Log Messages\',\'Allow a user to post custom log messages\',0),
			(\'news_create\',\'Create Articles\',\'Allow a user to create new news articles\',0),
			(\'news_delete\',\'Delete Articles\',\'Allow a user to delete news articles\',0),
			(\'news_edit\',\'Edit Articles\',\'Allow a user to edit news articles\',0),
			(\'news_fe_manage\',\'Manage News from Front-End\',\'Allow a user to manage news articles from the front-end\',0),
			(\'newsletter_create\',\'Create Newsletter\',\'Allow a user to create a new newsletter\',0),
			(\'newsletter_delete\',\'Delete Newsletter\',\'Allow a user to delete a newsletter\',0),
			(\'page_set_home\',\'Change Default Page\',\'Allow a user to change the default CMS page\',0),
			(\'page_order\',\'Change Page Order\',\'Allow a user to rearrange pages on the CMS menu\',0),
			(\'group_create\',\'Create User Groups\',\'Allow a user to create a new user group\',0),
			(\'user_create\',\'Create User\',\'Allow a user to create new users\',0),
			(\'pagegroupedit-1\',\'Edit Page Group \\\'Default Group\\\'\',\'Allow user to edit pages in the group \\\'Default Group\\\'\',0)';

		// Get old news configuration
		$old_nconfig_query = 'SELECT * FROM `'.$CONFIG['db_prefix'].'news_settings`';
		$old_nconfig_handle = $db->sql_query($old_nconfig_query);
		if ($db->error[$old_nconfig_handle] === 1) {
			die ('A database error occured. Please retry the upgrade.');
		}
		if ($db->sql_num_rows($old_nconfig_handle) == 0) {
			die ('Your news configuration table is empty. Please reinstall Community CMS.');
		}
		$old_nconfig = $db->sql_fetch_assoc($old_nconfig_handle);

		// Move news config into global config table
		$query[] = 'INSERT INTO `'.CONFIG_TABLE.'` (\'config_name\',\'config_value\')
			VALUES
			(\'news_num_articles\',\''.$old_nconfig['num_articles'].'\'),
			(\'news_default_date_setting\',\''.$old_nconfig['default_date_setting'].'\'),
			(\'news_show_author\',\''.$old_nconfig['show_author'].'\'),
			(\'news_show_edit_time\',\''.$old_nconfig['show_edit_time'].'\')';
		$query[] = 'DROP TABLE `'.$CONFIG['db_prefix'].'news_settings`';
		execute_queries($query);
		$query = array();
		$db_version = 0.05;
		echo 'The database has been updated to version 0.05<br />'."\n";
		break;
	case 0.05:
		echo 'You already have the latest version of the database.<br />'."\n";
		echo "</body>\n</html>";
		exit;
	default:
		echo 'No database version given.';
		exit;
}

// ----------------------------------------------------------------------------

function execute_queries($query) {
	global $db;
	global $error;
	$num_queries = count($query);
	$_SESSION['userid'] = 1;
	for($i = 0; $i < $num_queries; $i++) {
		$handle = $db->sql_query($query[$i]);
		echo '<div class="query">';
		echo htmlentities($query[$i]);
		if(!$handle) {
			echo ' <span style="color: #CC0000; font-weight: bold;">FAILED</span><br />';
			$error = 1;
		} else {
			echo ' <span style="color: #00CC00; font-weight: bold;">SUCCESS</span><br />';
		}
		echo '</div>';
	}
}


if($error == 1) {
	echo 'Something went wrong. That is bad. You may need to repair the database
		manually.';
} else {
	echo 'Update successful. <a href="../index.php">View Site</a>';
	include('../functions/admin.php');
	set_config('db_version',$db_version);
	log_action('Upgraded Community CMS');
}
clean_up();
echo "</body>\n</html>\n";

?>