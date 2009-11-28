<?php
define('SECURITY',1);
require('../config.php');
require('../functions/main.php');
initialize();
echo "<html>\n<head>\n<title>Community CMS Database Update</title>\n</head><body>";
$query = array();
$error = 0;
// ----------------------------------------------------------------------------
// QUERY ARRAY (VERSION 0.01 -> 0.02)
// ----------------------------------------------------------------------------

// TODO: add contacts table
$query[] = 'DROP TABLE IF EXISTS '.$CONFIG['db_prefix'].'admin_pages';
$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'pages ADD `text_id` TEXT NULL AFTER `id`';
$query[] = 'CREATE TABLE IF NOT EXISTS '.$CONFIG['db_prefix'].'user_groups (
 	`id` int(5) NOT NULL auto_increment,
 	`name` text NOT NULL,
 	`label_format` text NOT NULL,
 	PRIMARY KEY (`id`)
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1';
$query[] = 'INSERT INTO '.$CONFIG['db_prefix'].'user_groups
 (`name`,`label_format`) VALUES
 ("Administrator","font-weight: bold; color: #009900;")';
$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'users ADD `groups` TEXT NULL AFTER `password`';
$query[] = 'UPDATE '.$CONFIG['db_prefix'].'users SET `groups` = "1" WHERE `id` = 1 LIMIT 1';
$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'news_settings` (
    `default_date_setting` INT(3) NOT NULL ,
    `show_author` INT(3) NOT NULL ,
    `show_edit_time` INT(3) NOT NULL
) ENGINE = MYISAM';
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
    (`default_date_setting` ,`show_author` ,`show_edit_time`) VALUES
	(\'1\', \'1\', \'1\')';
$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'config ADD `admin_email` TEXT NULL AFTER `url`';
$query[] = 'ALTER TABLE '.$CONFIG['db_prefix'].'config ADD `time_format` TEXT NULL AFTER `comment`';
$query[] = 'UPDATE '.$CONFIG['db_prefix'].'config SET `time_format` = \'h:i A\'';
// TODO: Update with new schema
//$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'acl` (
//	`id` INT NOT NULL auto_increment PRIMARY KEY,
//	`acl_key` TEXT NOT NULL,
//	`user` INT NOT NULL,
//	`is_group` INT(1) NOT NULL DEFAULT 0,
//	`allow` INT(1) NOT NULL
//) ENGINE=MYISAM CHARACTER SET=utf8';
//$query[] = 'INSERT INTO `'.$CONFIG['db_prefix'].'acl` (`acl_key`, `user`, `is_group`, `allow`) VALUES
//(\'all\', 1, 0, 1)';
$query[] = 'CREATE TABLE IF NOT EXISTS `'.$CONFIG['db_prefix'].'sessions` (
	`uid` int(5) NOT NULL,
	`sid` varchar(64) NOT NULL default \'\',
	`timestamp` int NOT NULL default \'0\',
	`ip_addr` int(10) NOT NULL,
	PRIMARY KEY (`sid`),
	KEY (`uid`),
	KEY (`timestamp`)
) ENGINE=MyISAM CHARACTER SET=utf8';
$query[] = 'UPDATE '.$CONFIG['db_prefix'].'config SET `db_version` = 0.02';

// ----------------------------------------------------------------------------
$num_queries = count($query);
for($i = 0; $i < $num_queries; $i++) {
    $handle = $db->query($query[$i]);
    echo $query[$i];
    if(!$handle) {
        echo ' <span style="color: #CC0000; font-weight: bold;">FAILED</span><br />';
        $error = 1;
    } else {
        echo ' <span style="color: #00CC00; font-weight: bold;">SUCCESS</span><br />';
    }
}
if($error == 1) {
    echo 'Something went wrong. That is bad. You may need to repair the database
        manually.';
} else {
    echo 'Update successful.';
}
clean_up();
echo "</body>\n</html>\n";

?>
