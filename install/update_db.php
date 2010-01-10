<?php
define('SECURITY',1);
define('ROOT','../');
require('../config.php');
require('../include.php');
initialize();
echo "<html>\n<head>\n<title>Community CMS Database Update</title>\n</head><body>";
$query = array();
$error = 0;
// ----------------------------------------------------------------------------
// QUERY ARRAY (VERSION 0.01 -> 0.02)
// ----------------------------------------------------------------------------

// TODO: add contacts table
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

// ----------------------------------------------------------------------------
$num_queries = count($query);

// Skip update if you already have the latest version of the db...
if (get_config('db_version') == 0.02) {
	echo 'You already have the latest version of the database.';
	clean_up();
	echo "</body>\n</html>\n";
	exit;
}

for($i = 0; $i < $num_queries; $i++) {
	$handle = $db->sql_query($query[$i]);
	echo '<div class="query">';
	echo $query[$i];
	if(!$handle) {
		echo ' <span style="color: #CC0000; font-weight: bold;">FAILED</span><br />';
		$error = 1;
	} else {
		echo ' <span style="color: #00CC00; font-weight: bold;">SUCCESS</span><br />';
	}
	echo '</div>';
}
if($error == 1) {
	echo 'Something went wrong. That is bad. You may need to repair the database
		manually.';
} else {
	echo 'Update successful. <a href="../index.php">View Site</a>';
	include('../functions/admin.php');
	log_action('Upgraded Community CMS');
}
clean_up();
echo "</body>\n</html>\n";

?>