<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
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
define('FILES_ROOT', ROOT.'files/');

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
define('LOG_LEVEL_ADMIN', 1);
define('LOG_LEVEL_USER', 2);
define('LOG_LEVEL_ANON', 3);
define('LOG_LEVEL_INSTALL', 4);
/**#@-*/

/**#@+
 * Page types
 */
define('NEWS_PAGE_TYPE', 1);
define('NEWSLETTER_PAGE_TYPE', 2);
define('CALENDAR_PAGE_TYPE', 3);
define('CONTACTS_PAGE_TYPE', 4);
/**#@-*/

/**#@+
 * Database Tables
 */
define('ACL_TABLE', Config::DB_PREFIX . 'acl');
define('ACL_KEYS_TABLE', Config::DB_PREFIX . 'acl_keys');
define('BLOCK_TABLE', Config::DB_PREFIX . 'blocks');
define('CALENDAR_TABLE', Config::DB_PREFIX . 'calendar');
define('CALENDAR_CATEGORY_TABLE', Config::DB_PREFIX . 'calendar_categories');
define('CONFIG_TABLE', Config::DB_PREFIX . 'config');
define('CONTACTS_TABLE', Config::DB_PREFIX . 'contacts');
define('CONTENT_TABLE', Config::DB_PREFIX . 'content');
define('DIR_PROP_TABLE', Config::DB_PREFIX . 'dir_props');
define('FILE_TABLE', Config::DB_PREFIX . 'files');
define('GALLERY_TABLE', Config::DB_PREFIX . 'galleries');
define('GALLERY_IMAGE_TABLE', Config::DB_PREFIX . 'gallery_images');
define('LOCATION_TABLE', Config::DB_PREFIX . 'locations');
define('LOG_TABLE', Config::DB_PREFIX . 'logs');
define('NEWS_TABLE', Config::DB_PREFIX . 'news');
define('NEWSLETTER_TABLE', Config::DB_PREFIX . 'newsletters');
define('PAGE_TABLE', Config::DB_PREFIX . 'pages');
define('PAGE_MESSAGE_TABLE', Config::DB_PREFIX . 'page_messages');
define('PAGE_TYPE_TABLE', Config::DB_PREFIX . 'pagetypes');
define('PLUGIN_TABLE', Config::DB_PREFIX . 'plugins');
define('SESSION_TABLE', Config::DB_PREFIX . 'sessions');
define('TEMPLATE_TABLE', Config::DB_PREFIX . 'templates');
define('USER_TABLE', Config::DB_PREFIX . 'users');
define('USER_GROUPS_TABLE', Config::DB_PREFIX . 'user_groups');
/**#@-*/
