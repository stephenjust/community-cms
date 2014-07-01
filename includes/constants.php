<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

if (!ini_get('date.timezone')) {
	date_default_timezone_set('UTC');
}

define('COMCMS_VERSION', 'SVN');
define('DATABASE_VERSION', 0.05);
define('FILES_ROOT',ROOT.'files/');

/**
 * Enable debugging
 *
 * Set to '1' to enable, set to '0' to disable
 */
define('DEBUG', 1);

/**#@+
 * Date/time constant
 */
define('DATE_TIME', date('Y-m-d H:i:s'));
define('DATE', date('Y-m-d'));
define('TIME_24_SEC', date('H:i:s'));
define('TIME_24', date('H:i'));
define('TIME_12_SEC', date('h:i:sa'));
define('TIME_12', date('h:ia'));
/**#@-*/

/**#@+
 * Log message levels (for Log class)
 */
define('LOG_LEVEL_ADMIN',1);
define('LOG_LEVEL_USER',2);
define('LOG_LEVEL_ANON',3);
define('LOG_LEVEL_INSTALL',4);
/**#@-*/

/**#@+
 * Database Tables
 */
define('ACL_TABLE', $CONFIG['db_prefix'] . 'acl');
define('ACL_KEYS_TABLE', $CONFIG['db_prefix'] . 'acl_keys');
define('BLOCK_TABLE', $CONFIG['db_prefix'] . 'blocks');
define('CALENDAR_TABLE', $CONFIG['db_prefix'] . 'calendar');
define('CALENDAR_CATEGORY_TABLE', $CONFIG['db_prefix'] . 'calendar_categories');
define('CONFIG_TABLE', $CONFIG['db_prefix'] . 'config');
define('CONTACTS_TABLE', $CONFIG['db_prefix'] . 'contacts');
define('CONTENT_TABLE', $CONFIG['db_prefix'] . 'content');
define('DIR_PROP_TABLE', $CONFIG['db_prefix'] . 'dir_props');
define('FILE_TABLE', $CONFIG['db_prefix'] . 'files');
define('GALLERY_TABLE', $CONFIG['db_prefix'] . 'galleries');
define('GALLERY_IMAGE_TABLE', $CONFIG['db_prefix'] . 'gallery_images');
define('LOCATION_TABLE', $CONFIG['db_prefix'] . 'locations');
define('LOG_TABLE', $CONFIG['db_prefix'] . 'logs');
define('NEWS_TABLE', $CONFIG['db_prefix'] . 'news');
define('NEWSLETTER_TABLE', $CONFIG['db_prefix'] . 'newsletters');
define('PAGE_TABLE', $CONFIG['db_prefix'] . 'pages');
define('PAGE_MESSAGE_TABLE', $CONFIG['db_prefix'] . 'page_messages');
define('PAGE_TYPE_TABLE', $CONFIG['db_prefix'] . 'pagetypes');
define('PLUGIN_TABLE', $CONFIG['db_prefix'] . 'plugins');
define('SESSION_TABLE', $CONFIG['db_prefix'] . 'sessions');
define('TEMPLATE_TABLE', $CONFIG['db_prefix'] . 'templates');
define('USER_TABLE', $CONFIG['db_prefix'] . 'users');
define('USER_GROUPS_TABLE', $CONFIG['db_prefix'] . 'user_groups');
/**#@-*/
