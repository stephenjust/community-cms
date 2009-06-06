CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->acl` (
	`id` INT NOT NULL auto_increment PRIMARY KEY,
	`acl_key` TEXT NOT NULL,
	`user` INT NOT NULL,
	`is_group` INT(1) NOT NULL DEFAULT 0,
	`allow` INT(1) NOT NULL
) ENGINE=MYISAM CHARACTER SET=utf8 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->acl` (`acl_key`, `user`, `is_group`, `allow`) VALUES
('all', 1, 0, 1);;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->blocks` (
	`id` INT NOT NULL auto_increment PRIMARY KEY ,
	`type` TEXT NOT NULL,
	`attributes` TEXT NOT NULL
) ENGINE=MYISAM CHARACTER SET=utf8 ;;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->calendar_categories` (
	`cat_id` int(11) NOT NULL auto_increment,
	`label` text NOT NULL,
	`colour` text NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->calendar_categories` (`cat_id`, `label`, `colour`, `description`) VALUES
(0, 'Default Category', 'red', ''),
(1, 'Other', 'yellow', '');;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->config` (
	`db_version` decimal(6,2) NOT NULL,
	`name` text NOT NULL,
	`url` text NOT NULL,
	`admin_email` text NULL,
	`comment` text NOT NULL,
	`template` int(11) NOT NULL,
	`footer` text NOT NULL,
	`active` tinyint(1) NOT NULL,
	`home` int(4) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;;

INSERT INTO `<!-- $DB_PREFIX$ -->config` (`db_version`,`name`, `url`, `comment`, `template`, `footer`, `active`) VALUES
('0.02','<!-- $SITE_NAME$ -->', 'http://localhost/', 'Sourceforge.net', 1, '<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a><br />Powered by Community CMS', 1);;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->files` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL,
  `label` text NOT NULL,
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->logs` (
  `log_id` int(11) NOT NULL auto_increment,
  `date` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `user_id` int(5) NOT NULL,
  `action` text NOT NULL,
  `ip_addr` INT(10) unsigned NOT NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->news` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) default NULL,
  `name` text,
  `description` text,
  `author` text,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `date_edited` timestamp NULL default NULL,
  `image` text,
  `showdate` int(2) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `page` (`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;;

INSERT INTO `<!-- $DB_PREFIX$ -->news` (`id`, `page`, `name`, `description`, `author`, `date`, `image`) VALUES
(0, 1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, and an administration system to make editing your content easy. Now you can edit content too! It works really well.</p>', 'Administrator', '2008-06-20 22:25:38', NULL);;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->news_settings` (
	`num_articles` INT(3) NOT NULL ,
    `default_date_setting` INT(3) NOT NULL ,
    `show_author` INT(3) NOT NULL ,
    `show_edit_time` INT(3) NOT NULL
) ENGINE = MYISAM ;;

INSERT INTO `<!-- $DB_PREFIX$ -->news_settings`
    (`num_articles`,`default_date_setting` ,`show_author` ,`show_edit_time`) VALUES
('10','1', '1', '1');;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->newsletters` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) NOT NULL,
  `year` int(4) NOT NULL default '2008',
  `month` int(2) NOT NULL default '1',
  `label` text character set utf8 collate utf8_unicode_ci,
  `path` text character set utf8 collate utf8_unicode_ci,
  `hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pages` (
  `id` int(11) NOT NULL auto_increment,
  `text_id` text NOT NULL,
  `title` text NOT NULL,
  `show_title` tinyint(1) NOT NULL default '1',
  `type` int(11) NOT NULL,
  `menu` tinyint(1) NOT NULL,
  `list` int(6) NOT NULL default '0',
  `blocks_left` text NULL,
  `blocks_right` text NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->pages` (`text_id`, `title`, `type`, `menu`, `list`) VALUES
('home', 'Home', 1, 1, 0),
('calendar', 'Calendar', 3, 1, 1),
('newsletters', 'Newsletters', 2, 1, 2);;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->page_messages` (
	`message_id` INT NOT NULL auto_increment PRIMARY KEY,
	`page_id` INT NOT NULL,
	`start_date` DATE NOT NULL,
	`end_date` DATE NOT NULL,
	`end` BOOL NOT NULL DEFAULT '1',
	`text` TEXT NOT NULL,
	`order` INT NOT NULL,
	INDEX ( `page_id`,`order` )
) ENGINE = MYISAM DEFAULT CHARSET=latin1;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->pagetypes` (
	`id` int(4) NOT NULL auto_increment,
	`name` tinytext NOT NULL,
	`description` text NOT NULL,
	`author` tinytext NOT NULL,
	`filename` tinytext NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->pagetypes` (`name`, `description`, `author`, `filename`) VALUES
('News', 'A simple news posting system that acts as the main message centre for Community CMS', 'stephenjust', 'news.php'),
('Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by date. It is most useful for a monthly newsletter scenario.', 'stephenjust', 'newsletter.php'),
('Calendar', 'A complex date management system supporting a full month view, week view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', 'calendar.php'),
('Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php');;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->permissions` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`user` INT NOT NULL ,
	`files` INT(4) NOT NULL DEFAULT '0',
	INDEX (`user`)
) ENGINE = MYISAM DEFAULT CHARSET=latin1;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_questions` (
  `question_id` int(5) NOT NULL auto_increment,
  `question` text NOT NULL,
  `short_name` text NOT NULL,
  `type` int(2) NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_answers` (
  `answer_id` int(6) NOT NULL auto_increment,
  `question_id` int(5) NOT NULL,
  `answer` text NOT NULL,
  `answer_order` int(2) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->poll_responses` (
	`response_id` int(11) NOT NULL auto_increment,
	`question_id` int(5) NOT NULL,
	`answer_id` int(6) NOT NULL,
	`value` text,
	`ip_addr` int(10) NOT NULL,
	PRIMARY KEY  (`response_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->templates` (
	`id` int(3) NOT NULL auto_increment,
	`path` text NOT NULL,
	`name` text NOT NULL,
	`description` text NOT NULL,
	`author` text NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->templates` (`id`, `path`, `name`, `description`, `author`) VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->messages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`recipient` INT(5) NOT NULL DEFAULT '1',
	`message` TEXT NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE = MYISAM DEFAULT CHARSET=latin1;;

CREATE TABLE IF NOT EXISTS `<!-- $DB_PREFIX$ -->user_groups` (
	`id` int(5) NOT NULL auto_increment,
	`name` text NOT NULL,
	`label_format` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->user_groups`
(`name`,`label_format`) VALUES
('Administrator','font-weight: bold; color: #009900;');;

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
	`phone_hide` BOOL NOT NULL default '1',
	`email_hide` BOOL NOT NULL default '1',
	`address_hide` BOOL NOT NULL default '1',
	`hide` BOOL NOT NULL default '0',
	`message` BOOL NOT NULL default '0',
	`lastlogin` INT NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;;

INSERT INTO `<!-- $DB_PREFIX$ -->users` (`id`, `type`, `username`, `password`, `groups`, `realname`, `phone`, `email`, `address`, `phone_hide`, `email_hide`, `address_hide`, `message`) VALUES
(1, 1, '<!-- $ADMIN_USER$ -->', '<!-- $ADMIN_PWD$ -->', '1', 'Administrator', '555-555-5555', 'admin@example.com','Unknown',1,1,1,1),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', NULL, 'Default User', '555-555-5555', 'user@example.com','Unknown',1,1,1,0)
