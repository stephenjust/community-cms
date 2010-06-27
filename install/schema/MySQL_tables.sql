CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->acl` (
	`acl_record_id` INT NOT NULL auto_increment PRIMARY KEY,
	`acl_id` TEXT NOT NULL,
	`group` INT NOT NULL,
	`value` INT(1) NOT NULL DEFAULT 0
) ENGINE=MYISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->acl_keys` (
	`acl_id` INT NOT NULL auto_increment PRIMARY KEY,
	`acl_name` TEXT NOT NULL,
	`acl_longname` TEXT NOT NULL,
	`acl_description` TEXT NOT NULL,
	`acl_value_default` INT(1) NOT NULL DEFAULT 0
) ENGINE=MYISAM CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->blocks` (
	`id` INT NOT NULL auto_increment PRIMARY KEY ,
	`type` TEXT NOT NULL,
	`attributes` TEXT NOT NULL
) ENGINE=MYISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar` (
	`id` int(11) NOT NULL auto_increment,
	`category` int(11) NOT NULL,
	`starttime` time NOT NULL,
	`endtime` time NOT NULL,
	`year` int(4) NOT NULL,
	`month` int(2) NOT NULL,
	`day` int(2) NOT NULL,
	`header` text NOT NULL,
	`description` text,
	`location` text,
	`author` text,
	`image` text default NULL,
	`hidden` tinyint(1) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `category` (`category`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar_categories` (
	`cat_id` int(11) NOT NULL auto_increment,
	`label` text NOT NULL,
	`colour` text NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM CHARACTER SET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar_sources` (
	`id` int(11) NOT NULL auto_increment,
	`desc` TEXT NOT NULL,
	`url` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->config` (
	`config_name` varchar(255) NOT NULL,
	`config_value` varchar(255) NOT NULL,
	PRIMARY KEY (`config_name`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE `<!-- $DB_PREFIX$ -->contacts` (
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
) ENGINE=MYISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->files` (
	`id` int(11) NOT NULL auto_increment,
	`type` int(11) NOT NULL,
	`label` text NOT NULL,
	`path` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->galleries` (
	`id` int(11) NOT NULL auto_increment,
	`title` text NOT NULL,
	`description` text NOT NULL,
	`image_dir` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->gallery_images` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`gallery_id` int(11) NOT NULL,
	`file` text NOT NULL,
	`caption` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `gallery_id` (`gallery_id`)
) ENGINE=MyISAM CHARACTER SET=utf8 ;

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

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->messages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`recipient` INT(5) NOT NULL DEFAULT '1',
	`message` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE = MYISAM CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->news` (
	`id` int(11) NOT NULL auto_increment,
	`page` int(11) default NULL,
	`priority` int(11) NOT NULL default 0,
	`name` text,
	`description` text,
	`author` text,
	`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
	`date_edited` timestamp NULL default NULL,
	`image` text,
	`showdate` int(2) NOT NULL default 1,
	PRIMARY KEY  (`id`),
	KEY `page` (`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->news_settings` (
	`num_articles` INT(3) NOT NULL ,
    `default_date_setting` INT(3) NOT NULL ,
    `show_author` INT(3) NOT NULL ,
    `show_edit_time` INT(3) NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->newsletters` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) NOT NULL,
  `year` int(4) NOT NULL default '2008',
  `month` int(2) NOT NULL default '1',
  `label` text character set utf8 collate utf8_unicode_ci,
  `path` text character set utf8 collate utf8_unicode_ci,
  `hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pages` (
	`id` int(11) NOT NULL auto_increment,
	`text_id` text NOT NULL,
	`title` text NOT NULL,
	`meta_desc` text NOT NULL,
	`show_title` tinyint(1) NOT NULL default '1',
	`type` int(11) NOT NULL,
	`menu` tinyint(1) NOT NULL,
	`page_group` int(11) NOT NULL default '1',
	`parent` int(11) NOT NULL default '0',
	`list` int(6) NOT NULL default '0',
	`blocks_left` text NULL,
	`blocks_right` text NULL,
	`hidden` int(1) NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->page_groups` (
	`id` INT NOT NULL auto_increment PRIMARY KEY,
	`label` TEXT NOT NULL,
	INDEX (`id`)
) ENGINE = MYISAM CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->page_messages` (
	`message_id` INT NOT NULL auto_increment PRIMARY KEY,
	`page_id` INT NOT NULL,
	`start_date` DATE NOT NULL,
	`end_date` DATE NOT NULL,
	`end` BOOL NOT NULL DEFAULT '1',
	`text` TEXT NOT NULL,
	`order` INT NOT NULL,
	INDEX ( `page_id`,`order` )
) ENGINE = MYISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pagetypes` (
	`id` int(4) NOT NULL auto_increment,
	`name` tinytext NOT NULL,
	`description` text NOT NULL,
	`author` tinytext NOT NULL,
	`filename` tinytext NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_questions` (
  `question_id` int(5) NOT NULL auto_increment,
  `question` text NOT NULL,
  `short_name` text NOT NULL,
  `type` int(2) NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_answers` (
  `answer_id` int(6) NOT NULL auto_increment,
  `question_id` int(5) NOT NULL,
  `answer` text NOT NULL,
  `answer_order` int(2) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_responses` (
	`response_id` int(11) NOT NULL auto_increment,
	`question_id` int(5) NOT NULL,
	`answer_id` int(6) NOT NULL,
	`value` text,
	`ip_addr` int(10) NOT NULL,
	PRIMARY KEY  (`response_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->templates` (
	`id` int(3) NOT NULL auto_increment,
	`path` text NOT NULL,
	`name` text NOT NULL,
	`description` text NOT NULL,
	`author` text NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->user_groups` (
	`id` int(5) NOT NULL auto_increment,
	`name` text NOT NULL,
	`label_format` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->users` (
	`id` int(5) NOT NULL auto_increment,
	`type` int(2) NOT NULL default '1',
	`username` text NOT NULL,
	`password` text NOT NULL,
	`realname` text NOT NULL,
	`title` text NULL,
    `groups` text NULL,
	`phone` text NOT NULL,
	`email` text NOT NULL,
	`address` text NOT NULL,
	`lastlogin` INT NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3