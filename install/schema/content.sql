INSERT INTO `<!-- $DB_PREFIX$ -->acl` (`acl_id`, `group`, `value`) VALUES
(1, 1, 1);;
INSERT INTO `<!-- $DB_PREFIX$ -->acl_keys` (`acl_name`,`acl_longname`,`acl_description`,`acl_value_default`) VALUES
('all','All Permissions','Grant this permission to allow all actions within the CMS',0),
('admin_access','Admin Access','Allow a user to access the administrative section of the CMS',0),
('set_permissions','Set Permissions','Allow a user to modify the permission settings for user groups',0),
('adm_help','Admin Help Module','Allow a user to access the help module',0),
('adm_feedback','Admin Feedback Module','Allow a user to access the admin feedback module',0),
('adm_site_config','Site Configuration','Allow a user to modify the CMS configuration',0),
('adm_block_manager','Block Module','Allow a user to access the block manager module',0),
('adm_contacts_manage','Contacts Module','Allow a user to access the contacts manager module',0),
('adm_filemanager','File Manager','Allow a user to access the file manager module',0),
('adm_gallery_manager','Gallery Manager','Allow a user to access the gallery manager module',0),
('adm_gallery_settings','Gallery Settings','Allow a user to configure image galleries',0),
('adm_news','News Module','Allow a user to access the news article module',0),
('adm_news_edit_article','News Edit Module','Allow a user to access the news edit module',0),
('adm_news_settings','News Settings','Allow a user to configure news settings',0),
('adm_newsletter','Newsletter Module','Allow a user to access the newsletter module',0),
('adm_page','Page Module','Allow a user to access the page manager module',0),
('adm_page_message','Page Message Module','Allow a user to access the page message module',0),
('adm_page_message_edit','Edit Page Messages','Allow a user to edit page messages',0),
('adm_page_message_new','New Page Messages','Allow a user to create new page messages',0),
('adm_user','User Module','Allow a user to access the user manager module',0),
('adm_user_create','New User Module','Allow a user to access the new user module',0),
('adm_user_edit','Edit User Module','Allow a user to access the edit user module',0),
('adm_user_groups','User Groups Module','Allow a user to access the user groups module',0),
('adm_log_view','View Logs','Allow a user to access the admin activity logs',0),
('adm_config_view','View Configuration','Allow a user to view all of the CMS configuration values',0),
('block_create','Create Blocks','Allow a user to create new blocks',0),
('block_delete','Delete Blocks','Allow a user to delete blocks',0),
('calendar_settings','Calendar Settings','Allow a user to modify calendar settings',0),
('log_post_custom_message','Post Custom Log Messages','Allow a user to post custom log messages',0),
('news_create','Create Articles','Allow a user to create new news articles',0),
('news_delete','Delete Articles','Allow a user to delete news articles',0),
('news_edit','Edit Articles','Allow a user to edit news articles',0),
('news_fe_manage','Manage News from Front-End','Allow a user to manage news articles from the front-end',0),
('newsletter_create','Create Newsletter','Allow a user to create a new newsletter',0),
('newsletter_delete','Delete Newsletter','Allow a user to delete a newsletter',0),
('show_fe_errors','Show Front-End Errors','Allow a user to view error messages in the CMS front-end that would normally be hidden from users',0),
('page_set_home','Change Default Page','Allow a user to change the defualt CMS page',0),
('page_order','Change Page Order','Allow a user to rearrange pages on the CMS menu',0),
('pagegroupedit-1','Edit Page Group \'Default Group\'','Allow user to edit pages in the group \'Default Group\'',0),
('group_create','Create User Groups','Allow a user to create a new user group',0);;
INSERT INTO `<!-- $DB_PREFIX$ -->calendar_categories` (`cat_id`, `label`, `colour`, `description`) VALUES
(0, 'Default Category', 'red', ''),
(1, 'Other', 'yellow', '');;
INSERT INTO `<!-- $DB_PREFIX$ -->config` (`config_name`, `config_value`) VALUES
('admin_email','<!-- $ADMIN_EMAIL$ -->'),
('calendar_month_day_format','1'),
('calendar_default_view','month'),
('calendar_month_show_cat_icons','1'),
('calendar_month_show_stime','1'),
('calendar_save_locations','1'),
('comment','Downloaded from SourceForge.net'),
('cookie_name','cms_session'),
('cookie_path','/'),
('db_version', '0.05'),
('footer','<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a><br />Powered by Community CMS'),
('home','1'),
('news_default_date_setting','1'),
('news_num_articles','10'),
('news_show_author','1'),
('news_show_edit_time','0'),
('site_active','1'),
('site_name','<!-- $SITE_NAME$ -->'),
('site_template','1'),
('site_url','http://localhost/'),
('time_format','h:i A');;
INSERT INTO `<!-- $DB_PREFIX$ -->news` (`page`, `name`, `description`, `author`, `date`, `image`) VALUES
(1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, a contact information managing tool, and an administration system to make editing your content easy. To see what\'s new in this release, click <a href="http://communitycms.sourceforge.net/whatsnew-0.6.html">here</a>.</p>', 'Administrator', '2008-06-20 22:25:38', NULL);;
INSERT INTO `<!-- $DB_PREFIX$ -->pages` (text_id, title, meta_desc, type, menu, list, hidden) VALUES
('home', 'Home', '', 1, 1, 0, 0),
('calendar', 'Calendar', '', 3, 1, 1, 0),
('newsletters', 'Newsletters', '', 2, 1, 2, 0);;
INSERT INTO `<!-- $DB_PREFIX$ -->page_groups` (`label`) VALUES
('Default Group');;
INSERT INTO `<!-- $DB_PREFIX$ -->pagetypes` (id, name, description, author, filename) VALUES
(1, 'News', 'A simple news posting system that acts as the main content distribution system for Community CMS', 'stephenjust', 'news.php'),
(2, 'Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by timestamp. It is most useful for a monthly newsletter scenario.', 'stephenjust', 'newsletter.php'),
(3, 'Calendar', 'A complex timestamp management system supporting a full month view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', 'calendar.php'),
(4, 'Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php');;
INSERT INTO `<!-- $DB_PREFIX$ -->templates` (`id`, `path`, `name`, `description`, `author`) VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');;
INSERT INTO `<!-- $DB_PREFIX$ -->user_groups`
(`name`,`label_format`) VALUES
('Administrator','font-weight: bold; color: #009900;');;
INSERT INTO `<!-- $DB_PREFIX$ -->users`
(id, type, username, password, groups, realname, phone, email, address) VALUES
(1, 1, '<!-- $ADMIN_USER$ -->', '<!-- $ADMIN_PWD$ -->', '1', 'Administrator', '555-555-5555', 'admin@example.com','Unknown'),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', NULL, 'Default User', '555-555-5555', 'user@example.com','Unknown')