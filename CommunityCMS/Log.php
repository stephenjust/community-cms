<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2010-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Log function class
 *
 * @package CommunityCMS.main
 */
class Log
{
    /**
     * Add a log message to the database
     * @param string  $message Log message
     * @param integer $level   User level (use constants LOG_LEVEL_*)
     * @return boolean Success
     */
    public static function addMessage($message, $level = LOG_LEVEL_ADMIN) 
    {
        assert(strlen($message) > 0);
        assert(is_numeric($level));

        $ip = Log::getClientIpAddress();
        $user = Log::getUserIdFromLogLevel($level);
        $query = 'INSERT INTO `'.LOG_TABLE.'`
			(`user_id`,`action`,`date`,`ip_addr`)
			VALUES (:user_id, :action, :date, :ip)';
        try {
            DBConn::get()->query(
                $query, array(':user_id' => $user,
                ':action' => $message, ':date' => DATE_TIME, ':ip' => $ip)
            );
        } catch (Exceptions\DBException $e) {
            return false;
        }
        return true;
    }
    
    private static function getUserIdFromLogLevel($log_level) 
    {
        switch ($log_level) {
        default:
            return (isset($_SESSION['userid'])) ? $_SESSION['userid'] : 0;
        case LOG_LEVEL_ANON:
            return 0;
        case LOG_LEVEL_INSTALL:
            return -1;
        }
    }
    
    private static function getClientIpAddress() 
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_octet = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_octet = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $ip_octet = $_SERVER["REMOTE_ADDR"];
        }
        if ($ip_octet == null) {
            $ip_octet = 0;
        }
        return ip2long($ip_octet);
    }

    /**
     * Clear all log messages
     */
    public static function clear() 
    {
        acl::get()->requirePermission('log_clear');
        DBConn::get()->query(sprintf('TRUNCATE TABLE `%s`', LOG_TABLE));
        Log::addMessage('Cleared log messages.', LOG_LEVEL_ADMIN);
    }

    /**
     * Fetch an array of the last n log messages
     * @param integer $count Number of log messages to fetch
     */
    public static function getLastMessages($count = 1) 
    {
        assert(is_int($count));

        $log_messages = Log::getMessageRecords($count);

        for ($i = 0; $i < count($log_messages); $i++) {
            $log_messages[$i]['ip_addr'] = long2ip($log_messages[$i]['ip_addr']);
            $log_messages[$i]['user_name'] = Log::getMessageUsername($log_messages[$i]['user_id']);
        }
        return $log_messages;
    }
    
    private static function getMessageUsername($userid) 
    {
        if ($userid == 0) { return 'Anonymous User'; 
        }
        if ($userid == -1) { return 'Installer'; 
        }
        
        $result = DBConn::get()->query(
            sprintf(
                'SELECT `realname` FROM `%s`
				WHERE `id` = :userid', USER_TABLE
            ), array(':userid' => $userid), DBConn::FETCH
        );
        if (count($result) == 0) {
            return sprintf('User %d', $userid);
        } else {
            return $result['realname'];
        }
    }
    
    private static function getMessageRecords($count) 
    {
        return DBConn::get()->query(
            'SELECT `user_id`, `action`, `date`, `ip_addr`
			FROM `'.LOG_TABLE.'` ORDER BY `log_id` DESC LIMIT '.$count, null, DBConn::FETCH_ALL
        );
    }
}
