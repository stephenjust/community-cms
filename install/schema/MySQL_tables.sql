CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->acl` (
	`acl_id` INT UNSIGNED NOT NULL,
	`group` INT UNSIGNED NOT NULL,
	`value` INT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`acl_id`, `group`)
) ENGINE=InnoDB CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->acl_keys` (
	`acl_id` INT UNSIGNED NOT NULL auto_increment PRIMARY KEY,
	`acl_name` TEXT NOT NULL,
	`acl_longname` TEXT NOT NULL,
	`acl_description` TEXT NOT NULL,
	`acl_value_default` INT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->blocks` (
	`id` INT NOT NULL auto_increment PRIMARY KEY ,
	`type` TEXT NOT NULL,
	`attributes` TEXT NOT NULL
) ENGINE=MYISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar` (
	`id` int(11) UNSIGNED NOT NULL auto_increment,
	`category` int(11) UNSIGNED,
	`category_hide` tinyint(1) NOT NULL default 0,
	`start` datetime NOT NULL,
	`end` datetime NOT NULL,
	`header` text NOT NULL,
	`description` text,
	`location` text,
	`location_hide` tinyint(1) NOT NULL default 0,
	`author` text,
	`image` text default NULL,
	`hidden` tinyint(1) NOT NULL,
	`clone_of` int(11) UNSIGNED NULL default NULL,
	PRIMARY KEY  (`id`),
	KEY `category` (`category`),
	INDEX (`start`, `end`),
	INDEX (`clone_of`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar_categories` (
	`cat_id` int(11) UNSIGNED NOT NULL auto_increment,
	`label` text NOT NULL,
	`colour` text NOT NULL,
	`description` text NULL default NULL,
	PRIMARY KEY  (`cat_id`)
) ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->config` (
	`config_name` varchar(255) NOT NULL,
	`config_value` varchar(255) NOT NULL,
	PRIMARY KEY (`config_name`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE `<!-- $DB_PREFIX$ -->contacts` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` TEXT NOT NULL ,
	`phone` CHAR( 11 ) NULL default NULL ,
	`address` TEXT NOT NULL ,
	`email` TEXT NOT NULL ,
	`title` TEXT NOT NULL
) ENGINE=MYISAM CHARACTER SET=utf8 ;

CREATE TABLE `<!-- $DB_PREFIX$ -->content` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`page_id` INT NOT NULL default 0,
	`ref_type` TEXT NOT NULL,
	`ref_id` INT NOT NULL default 0,
	`order` INT NOT NULL default 0
) ENGINE=MYISAM CHARACTER SET=utf8;

CREATE TABLE `<!-- $DB_PREFIX$ -->dir_props` (
	`directory` VARCHAR(255) NOT NULL,
	`property` VARCHAR(255) NOT NULL,
	`value` int(4) UNSIGNED default 0,
	INDEX (`directory`),
	INDEX (`property`)
) ENGINE=MYISAM CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->files` (
	`id` int(11) NOT NULL auto_increment,
	`type` int(11) NOT NULL default 0,
	`label` text NOT NULL,
	`path` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->galleries` (
	`id` int(11) UNSIGNED NOT NULL auto_increment,
	`title` text NOT NULL,
	`description` text NOT NULL,
	`image_dir` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->gallery_images` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`gallery_id` int(11) UNSIGNED NOT NULL,
	`file` text NOT NULL,
	`caption` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `gallery_id` (`gallery_id`)
) ENGINE=InnoDb CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->locations` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`value` text COLLATE utf8_general_ci NOT NULL,
	PRIMARY KEY (`id`),
	FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->logs` (
	`log_id` int(11) NOT NULL auto_increment,
	`date` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
	`user_id` int(5) NOT NULL,
	`action` text NOT NULL,
	`ip_addr` INT(10) unsigned NOT NULL,
	PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->news` (
	`id` int(11) NOT NULL auto_increment,
	`page` int(11) UNSIGNED default NULL,
	`priority` int(11) NOT NULL default 0,
	`name` text,
	`description` text,
	`author` text,
	`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
	`date_edited` timestamp NULL default NULL,
	`delete_date` timestamp NULL default NULL,
	`image` text,
	`showdate` int(2) NOT NULL default 1,
	`publish` int(1) NOT NULL default 1,
	PRIMARY KEY  (`id`),
	KEY `page` (`page`)
) ENGINE=InnoDB CHARACTER SET=utf8 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->newsletters` (
	`id` int(11) NOT NULL auto_increment,
	`page` int(11) UNSIGNED default NULL,
	`year` int(4) NOT NULL default '2008',
	`month` int(2) NOT NULL default '1',
	`label` text character set utf8 collate utf8_unicode_ci,
	`path` text character set utf8 collate utf8_unicode_ci,
	`hidden` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB CHARACTER SET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pages` (
	`id` int(11) UNSIGNED NOT NULL auto_increment,
	`text_id` text NOT NULL,
	`title` text NOT NULL,
	`meta_desc` text NOT NULL,
	`show_title` tinyint(1) NOT NULL default '1',
	`type` int(11) NOT NULL,
	`menu` tinyint(1) NOT NULL,
	`parent` int(11) NOT NULL default '0',
	`list` int(6) NOT NULL default '0',
	`blocks_left` text NULL,
	`blocks_right` text NULL,
	`hidden` int(1) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->page_messages` (
	`message_id` INT NOT NULL auto_increment PRIMARY KEY,
	`page_id` INT UNSIGNED NOT NULL,
	`text` TEXT NOT NULL,
	`order` INT NOT NULL,
	INDEX ( `page_id`,`order` )
) ENGINE = InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pagetypes` (
	`id` int(4) NOT NULL auto_increment,
	`name` tinytext NOT NULL,
	`description` text NOT NULL,
	`author` tinytext NOT NULL,
	`filename` tinytext NOT NULL,
	`class` VARCHAR(16) NOT NULL DEFAULT 'GenericPage',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->templates` (
	`id` int(3) NOT NULL auto_increment,
	`path` text NOT NULL,
	`name` text NOT NULL,
	`description` text NOT NULL,
	`author` text NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->user_groups` (
	`id` INT UNSIGNED NOT NULL auto_increment,
	`name` text NOT NULL,
	`label_format` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->users` (
	`id` int(5) NOT NULL auto_increment,
	`type` int(2) NOT NULL default '1',
	`username` text NOT NULL,
	`password` text NOT NULL,
	`password_date` int NOT NULL default '0',
	`realname` text NOT NULL,
	`title` text NULL,
	`groups` text NULL,
	`phone` text NOT NULL,
	`email` text NOT NULL,
	`address` text NOT NULL,
	`lastlogin` INT NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3;

ALTER TABLE `<!-- $DB_PREFIX$ -->acl`
ADD FOREIGN KEY (`acl_id`)
REFERENCES `<!-- $DB_PREFIX$ -->acl_keys` (`acl_id`)
ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `<!-- $DB_PREFIX$ -->acl`
ADD FOREIGN KEY (`group`)
REFERENCES `<!-- $DB_PREFIX$ -->user_groups` (`id`)
ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `<!-- $DB_PREFIX$ -->calendar`
ADD FOREIGN KEY (`category`)
REFERENCES `<!-- $DB_PREFIX$ -->calendar_categories` (`cat_id`)
ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `<!-- $DB_PREFIX$ -->calendar`
ADD FOREIGN KEY (`clone_of`)
REFERENCES `<!-- $DB_PREFIX$ -->calendar` (`id`)
ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE  `<!-- $DB_PREFIX$ -->gallery_images`
ADD FOREIGN KEY (`gallery_id`)
REFERENCES `<!-- $DB_PREFIX$ -->galleries` (`id`)
ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE  `<!-- $DB_PREFIX$ -->news`
ADD FOREIGN KEY (`page`)
REFERENCES `<!-- $DB_PREFIX$ -->pages` (`id`)
ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE  `<!-- $DB_PREFIX$ -->page_messages`
ADD FOREIGN KEY (`page_id`)
REFERENCES `<!-- $DB_PREFIX$ -->pages` (`id`)
ON DELETE CASCADE ON UPDATE NO ACTION;