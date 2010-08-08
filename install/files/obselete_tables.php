<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

// Table names that are no longer in constants.php
define('ADMIN_PAGE_TABLE', $CONFIG['db_prefix'] . 'admin_pages');
define('CALENDAR_SETTINGS_TABLE', $CONFIG['db_prefix'] . 'calendar_settings');
define('NEWS_SETTINGS_TABLE', $CONFIG['db_prefix'] . 'news_settings');
define('PERMISSION_TABLE', $CONFIG['db_prefix'] . 'permissions');
?>
