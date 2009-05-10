<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

define('COMCMS_VERSION', 'SVN');
define('DEBUG', 1);

define('DATE_TIME', date('Y-m-d H:i:s'));

// DATABASE TABLES
define('BLOCK_TABLE', $CONFIG['db_prefix'] . 'blocks');
define('CALENDAR_TABLE', $CONFIG['db_prefix'] . 'calendar');
define('CALENDAR_CATEGORY_TABLE', $CONFIG['db_prefix'] . 'calendar_categories');
define('CONFIG_TABLE', $CONFIG['db_prefix'] . 'config');
define('FILE_TABLE', $CONFIG['db_prefix'] . 'files');
define('LOG_TABLE', $CONFIG['db_prefix'] . 'logs');
define('MESSAGE_TABLE', $CONFIG['db_prefix'] . 'messages');
define('NEWS_TABLE', $CONFIG['db_prefix'] . 'news');
define('NEWS_CONFIG_TABLE', $CONFIG['db_prefix'] . 'news_settings');
define('NEWSLETTER_TABLE', $CONFIG['db_prefix'] . 'newsletters');
define('PAGE_TABLE', $CONFIG['db_prefix'] . 'pages');
define('PAGE_MESSAGE_TABLE', $CONFIG['db_prefix'] . 'page_messages');
define('PAGE_TYPE_TABLE', $CONFIG['db_prefix'] . 'pagetypes');
define('POLL_ANSWER_TABLE', $CONFIG['db_prefix'] . 'poll_answers');
define('POLL_QUESTION_TABLE', $CONFIG['db_prefix'] . 'poll_questions');
define('POLL_RESPONSE_TABLE', $CONFIG['db_prefix'] . 'poll_responses');
define('TEMPLATE_TABLE', $CONFIG['db_prefix'] . 'templates');
define('USER_TABLE', $CONFIG['db_prefix'] . 'users');
define('USER_GROUPS_TABLE', $CONFIG['db_prefix'] . 'user_groups');

?>
