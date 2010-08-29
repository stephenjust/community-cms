<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../');
/**#@-*/

require('../config.php');
require('../include.php');
require('./files/obselete_tables.php');
require('./files/functions.php');
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
		$query[] = 'DROP TABLE IF EXISTS '.ADMIN_PAGE_TABLE;
		$query[] = 'DROP TABLE IF EXISTS '.PERMISSION_TABLE;
		$query[] = 'ALTER TABLE '.PAGE_TABLE.' ADD `text_id` TEXT NULL AFTER `id`';
		$query[] = 'ALTER TABLE '.PAGE_TABLE.' ADD `meta_desc` TEXT NULL AFTER `title`';
		$query[] = 'CREATE TABLE IF NOT EXISTS '.USER_GROUPS_TABLE.' (
			`id` int(5) NOT NULL auto_increment,
			`name` text NOT NULL,
			`label_format` text NOT NULL,
			PRIMARY KEY (`id`)
		 ) ENGINE=MyISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO '.USER_GROUPS_TABLE.'
		 (`name`,`label_format`) VALUES
		 ("Administrator","font-weight: bold; color: #009900;")';
		$query[] = 'ALTER TABLE '.USER_TABLE.' ADD `groups` TEXT NULL AFTER `password`';
		$query[] = 'UPDATE '.USER_TABLE.' SET `groups` = "1" WHERE `id` = 1 LIMIT 1';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.NEWS_SETTINGS_TABLE.'` (
			`default_date_setting` INT(3) NOT NULL ,
			`show_author` INT(3) NOT NULL ,
			`show_edit_time` INT(3) NOT NULL ,
			`num_articles` INT(3) NOT NULL
		) ENGINE = MYISAM CHARACTER SET=utf8';
		$query[] = 'ALTER TABLE `'.PAGE_TABLE.'`
			ADD `hidden` INT(1) NOT NULL AFTER `blocks_right`';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.CALENDAR_SETTINGS_TABLE.'` (
			`default_view` TEXT NOT NULL ,
			`month_show_stime` BOOL NOT NULL DEFAULT \'1\',
			`month_show_cat_icons` BOOL NOT NULL DEFAULT \'1\',
			`month_day_format` INT NOT NULL DEFAULT \'1\'
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO `'.CALENDAR_SETTINGS_TABLE.'` (`default_view`, `month_show_stime`, `month_show_cat_icons`, `month_day_format`) VALUES
			(\'month\',1,1,1)';
		$query[] = 'INSERT INTO `'.NEWS_SETTINGS_TABLE.'`
			(`default_date_setting` ,`show_author` ,`show_edit_time` ,`num_articles`) VALUES
			(\'1\', \'1\', \'1\', \'10\')';
		$query[] = 'ALTER TABLE '.CONFIG_TABLE.' ADD `admin_email` TEXT NULL AFTER `url`';
		$query[] = 'ALTER TABLE '.CONFIG_TABLE.' ADD `time_format` TEXT NULL AFTER `comment`';
		$query[] = 'UPDATE '.CONFIG_TABLE.' SET `time_format` = \'h:i A\'';

		$query[] = 'CREATE TABLE `'.CONTACTS_TABLE.'` (
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
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.ACL_TABLE.'` (
			`acl_record_id` INT NOT NULL auto_increment PRIMARY KEY,
			`acl_id` TEXT NOT NULL,
			`group` INT NOT NULL,
			`value` INT(1) NOT NULL DEFAULT 0
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'CREATE TABLE IF NOT EXISTS `'.ACL_KEYS_TABLE.'` (
			`acl_id` INT NOT NULL auto_increment PRIMARY KEY,
			`acl_name` TEXT NOT NULL,
			`acl_longname` TEXT NOT NULL,
			`acl_description` TEXT NOT NULL,
			`acl_value_default` INT(1) NOT NULL DEFAULT 0
		) ENGINE=MYISAM CHARACTER SET=utf8';
		$query[] = 'INSERT INTO `'.ACL_TABLE.'` (`acl_id`, `group`, `value`) VALUES
		(1, 1, 1)';

		$query[] = 'UPDATE '.CONFIG_TABLE.' SET `db_version` = 0.02';
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
		$old_calconfig_query = 'SELECT * FROM `'.CALENDAR_SETTINGS_TABLE.'`';
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
				$query[] = 'DROP TABLE IF EXISTS `'.CALENDAR_SETTINGS_TABLE.'`';
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
				$query[] = 'DROP TABLE `'.CALENDAR_SETTINGS_TABLE.'`';
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
			('gallery_app','built-in'),
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
				$query[] = 'ALTER TABLE `'.NEWS_TABLE.'` ADD `publish` INT(1) NOT NULL DEFAULT \'1\' AFTER `showdate`';
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
				$query[] = 'ALTER TABLE `'.USER_TABLE.'` ADD `password_date` INT NOT NULL DEFAULT \'0\' AFTER `password`';
				break;
			case 'postgresql':
				$query[] = 'ALTER TABLE `'.PAGE_TABLE.'` ADD `page_group` integer NOT NULL DEFAULT \'1\' AFTER `menu`';
				$query[] = 'ALTER TABLE `'.CALENDAR_TABLE.'` ADD `imported` text AFTER `hidden`';
				$query[] = 'ALTER TABLE `'.NEWS_TABLE.'` ADD `publish` integer NOT NULL DEFAULT \'1\' AFTER `showdate`';
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
				$query[] = 'ALTER TABLE `'.USER_TABLE.'` ADD `password_date` integer NOT NULL DEFAULT \'0\' AFTER `password`';
				break;
		}
		$query[] = 'INSERT INTO `'.PAGE_GROUP_TABLE.'` (`label`)
			VALUES (\'Default Group\')';

		$query[] = 'INSERT INTO `'.PAGE_TYPE_TABLE.'` (id, name, description, author, filename) VALUES
			(5, \'Tabs\', \'A page with tabs that display sub-pages to the current page\', \'stephenjust\', \'tabs.php\')';

		// Get old news configuration
		$old_nconfig_query = 'SELECT * FROM `'.NEWS_SETTINGS_TABLE.'`';
		$old_nconfig_handle = $db->sql_query($old_nconfig_query);
		if ($db->error[$old_nconfig_handle] === 1) {
			die ('A database error occured. Please retry the upgrade.');
		}
		if ($db->sql_num_rows($old_nconfig_handle) == 0) {
			die ('Your news configuration table is empty. Please reinstall Community CMS.');
		}
		$old_nconfig = $db->sql_fetch_assoc($old_nconfig_handle);

		// Remove obselete fields in contacts table
		$query[] = 'ALTER TABLE `'.CONTACTS_TABLE.'` DROP `phone_hide`,
			DROP `address_hide`,
			DROP `email_hide`,
			CHANGE `phone` `phone` CHAR( 11 ) NULL DEFAULT NULL';

		// Move news config into global config table (and add new config values)
		$query[] = 'INSERT INTO `'.CONFIG_TABLE.'` (\'config_name\',\'config_value\')
			VALUES
			(\'contacts_display_mode\',\'card\'),
			(\'news_num_articles\',\''.$old_nconfig['num_articles'].'\'),
			(\'news_default_date_setting\',\''.$old_nconfig['default_date_setting'].'\'),
			(\'news_default_publish_value\',\'0\'),
			(\'news_show_author\',\''.$old_nconfig['show_author'].'\'),
			(\'news_show_edit_time\',\''.$old_nconfig['show_edit_time'].'\'),
			(\'password_expire\',\'0\'),
			(\'tel_format\',\'(###) ###-####\')';
		$query[] = 'DROP TABLE `'.NEWS_SETTINGS_TABLE.'`';
		execute_queries($query);
		set_config('db_version','0.05');
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
		if($db->error[$handle] === 1) {
			echo ' <span style="color: #CC0000; font-weight: bold;">FAILED</span><br />';
			$error = 1;
		} else {
			echo ' <span style="color: #00CC00; font-weight: bold;">SUCCESS</span><br />';
		}
		echo '</div><br />';
	}
	echo 'Updating permission keys... ';
	if (update_permission_records() !== false) {
		echo '<span style="color: #00CC00">SUCCESS</span><br />';
	} else {
		echo '<span stype="color: #CC0000">FAILED</span><br />';
		$error = 1;
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