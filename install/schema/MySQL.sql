SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS comcms_admin_pages (
	`id` INT NOT NULL auto_increment PRIMARY KEY ,
	`category` TEXT NOT NULL,
	`on_menu` BOOL NOT NULL DEFAULT '1',
	`label` TEXT NOT NULL,
	`file` TEXT NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=latin1 ;


INSERT INTO comcms_admin_pages 
	(`id`,`category`,`on_menu`,`label`,`file`) VALUES 
	(NULL,'Main','1','Configuration','site_config'),
	(NULL,'Help','0','Help','help'),
	(NULL,'Blocks','1','Block Manager','block_manager'),
	(NULL,'News','1','New Article','news_new_article'),
	(NULL,'News','1','Article Manager','news'),
	(NULL,'News','0','Edit Article','news_edit_article'),
	(NULL,'Calendar','1','Dates','calendar'),
	(NULL,'Calendar','1','Settings','calendar_settings'),
	(NULL,'Calendar','1','New Date','calendar_new_date'),
	(NULL,'Files','1','Upload File','upload'),
	(NULL,'Files','1','Manage','filemanager'),
	(NULL,'Newsletters','1','Newsletters','newsletter'),
	(NULL,'Polls','1','Poll Manager','poll_manager'),
	(NULL,'Polls','1','New Poll','poll_new'),
	(NULL,'Polls','0','Poll Results','poll_results'),
	(NULL,'Pages','1','Pages','page'),
	(NULL,'Pages','1','Page Messages','page_message'),
	(NULL,'Pages','0','Create Page Message','page_message_new'),
	(NULL,'Pages','0','Edit Page Message','page_message_edit'),
	(NULL,'Users','1','New User','user_create'),
	(NULL,'Users','1','User List','user'),
	(NULL,'Users','0','Edit User','user_edit'),
	(NULL,'Logs','1','View Logs','log_view');
	
CREATE TABLE IF NOT EXISTS comcms_blocks (
	`id` INT NOT NULL auto_increment PRIMARY KEY ,
	`type` TEXT NOT NULL,
	`attributes` TEXT NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `comcms_calendar` (
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
  `image` int(11) default NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `comcms_calendar_categories`
--

CREATE TABLE IF NOT EXISTS `comcms_calendar_categories` (
  `cat_id` int(11) NOT NULL auto_increment,
  `label` text NOT NULL,
   `colour` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `comcms_calendar_categories`
--

INSERT INTO `comcms_calendar_categories` (`cat_id`, `label`, `colour`, `description`) VALUES
(0, 'Default Category', 'red', ''),
(1, 'Other', 'yellow', '');

-- --------------------------------------------------------

--
-- Table structure for table `comcms_config`
--

CREATE TABLE IF NOT EXISTS `comcms_config` (
	`db_version` decimal(6,2) NOT NULL,
	`name` text NOT NULL,
	`url` text NOT NULL,
	`comment` text NOT NULL,
	`template` int(11) NOT NULL,
	`footer` text NOT NULL,
	`active` tinyint(1) NOT NULL,
	`home` int(4) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `comcms_config`
--

INSERT INTO `comcms_config` (`db_version`,`name`, `url`, `comment`, `template`, `footer`, `active`) VALUES
('0.01','Community CMS Default', 'http://localhost/', 'Sourceforge.net', 1, '<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a><br />Powered by Community CMS', 1);

-- --------------------------------------------------------

--
-- Table structure for table `comcms_files`
--

CREATE TABLE IF NOT EXISTS `comcms_files` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL,
  `label` text NOT NULL,
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `comcms_logs` (
  `log_id` int(11) NOT NULL auto_increment,
  `date` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `user_id` int(5) NOT NULL,
  `action` text NOT NULL,
  `ip_addr` INT(10) unsigned NOT NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `comcms_news`
--

CREATE TABLE IF NOT EXISTS `comcms_news` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;

--
-- Dumping data for table `comcms_news`
--

INSERT INTO `comcms_news` (`id`, `page`, `name`, `description`, `author`, `date`, `image`) VALUES
(0, 1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, and an administration system to make editing your content easy. Now you can edit content too! It works really well.</p>', 'Administrator', '2008-06-20 22:25:38', NULL),
(1, 1, 'AJAX Front-end Content Editing Beta', '<p>Currently in development (but nearly finished): editing contend directly from the front page. BETA available. With this functionality, the admin editing page will not be the only way to edit content. This process is now fully functional! You can even edit from the backend!</p>', 'Administrator', '2008-08-16 12:49:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comcms_newsletters`
--

CREATE TABLE IF NOT EXISTS `comcms_newsletters` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) NOT NULL,
  `year` int(4) NOT NULL default '2008',
  `month` int(2) NOT NULL default '1',
  `label` text character set utf8 collate utf8_unicode_ci,
  `path` text character set utf8 collate utf8_unicode_ci,
  `hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `comcms_pages`
--

CREATE TABLE IF NOT EXISTS `comcms_pages` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `show_title` tinyint(1) NOT NULL default '1',
  `type` int(11) NOT NULL,
  `menu` tinyint(1) NOT NULL,
  `list` int(6) NOT NULL default '0',
  `blocks_left` text NULL,
  `blocks_right` text NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `comcms_pages`
--

INSERT INTO `comcms_pages` (`id`, `title`, `type`, `menu`, `list`) VALUES
(1, 'Home', 1, 1, 0),
(2, 'Calendar', 3, 1, 1),
(3, 'Newsletters', 2, 1, 2);

CREATE TABLE IF NOT EXISTS `comcms_page_messages` (
	`message_id` INT NOT NULL auto_increment PRIMARY KEY,
	`page_id` INT NOT NULL,
	`start_date` DATE NOT NULL,
	`end_date` DATE NOT NULL,
	`end` BOOL NOT NULL DEFAULT '1',
	`text` TEXT NOT NULL,
	`order` INT NOT NULL,
	INDEX ( `page_id`,`order` )
) ENGINE = MYISAM DEFAULT CHARSET=latin1;
--
-- Table structure for table `comcms_pagetypes`
--

CREATE TABLE IF NOT EXISTS `comcms_pagetypes` (
  `id` int(4) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
	`description` text NOT NULL,
  `author` tinytext NOT NULL,
  `filename` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `comcms_pagetypes`
--

INSERT INTO `comcms_pagetypes` (`id`, `name`, `description`, `author`, `filename`) VALUES
(1, 'News', 'A simple news posting system that acts as the main message centre for Community CMS', 'stephenjust', 'news.php'),
(2, 'Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by date. It is most useful for a monthly newsletter scenario.', 'stephenjust', 'newsletter.php'),
(3, 'Calendar', 'A complex date management system supporting a full month view, week view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', 'calendar.php'),
(4, 'Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php');

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comcms_permissions` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`user` INT NOT NULL ,
	`files` INT(4) NOT NULL DEFAULT '0',
	INDEX (`user`)
) ENGINE = MYISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `comcms_poll_questions` (
  `question_id` int(5) NOT NULL auto_increment,
  `question` text NOT NULL,
  `short_name` text NOT NULL,
  `type` int(2) NOT NULL default '1',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;


CREATE TABLE IF NOT EXISTS `comcms_poll_answers` (
  `answer_id` int(6) NOT NULL auto_increment,
  `question_id` int(5) NOT NULL,
  `answer` text NOT NULL,
  `answer_order` int(2) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `comcms_poll_responses` (
  `response_id` int(11) NOT NULL auto_increment,
  `question_id` int(5) NOT NULL,
  `answer_id` int(6) NOT NULL,
  `value` text,
  `ip_addr` int(10) NOT NULL,
  PRIMARY KEY  (`response_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;


CREATE TABLE IF NOT EXISTS `comcms_templates` (
  `id` int(3) NOT NULL auto_increment,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `author` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `comcms_templates`
--

INSERT INTO `comcms_templates` (`id`, `path`, `name`, `description`, `author`) VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');

-- --------------------------------------------------------

--
-- Table structure for table `comcms_messages`
--

CREATE TABLE IF NOT EXISTS `comcms_messages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`recipient` INT(5) NOT NULL DEFAULT '1',
	`message` TEXT NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE = MYISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `comcms_users`
--

CREATE TABLE IF NOT EXISTS `comcms_users` (
	`id` int(5) NOT NULL auto_increment,
	`type` int(2) NOT NULL default '1',
	`username` text NOT NULL,
	`password` text NOT NULL,
	`realname` text NOT NULL,
	`title` text NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `comcms_users`
--

INSERT INTO `comcms_users` (`id`, `type`, `username`, `password`, `realname`, `phone`, `email`, `address`, `phone_hide`, `email_hide`, `address_hide`, `message`) VALUES
(1, 1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'Administrator', '555-555-5555', 'admin@example.com','Unknown',1,1,1,1),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', 'Default User', '555-555-5555', 'user@example.com','Unknown',1,1,1,0);
