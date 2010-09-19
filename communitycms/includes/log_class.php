<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class Log {
	/**
	 * Add a log message to the database
	 * @global db $db Database connection object
	 * @param string $message Log message
	 * @param integer $level User level (use constants LOG_LEVEL_*)
	 * @return boolean Success
	 */
	public function new_message($message, $level = LOG_LEVEL_ADMIN) {
		global $db;

		// Validate parameters
		if (strlen($message) == 0) {
			return false;
		}
		if (!is_numeric($level)) {
			return false;
		}

		// Get user's IP address
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_octet = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip_octet = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip_octet = $_SERVER["REMOTE_ADDR"];
		}
		if ($ip_octet == NULL) {
			$ip_octet = 0;
		}
		$ip = ip2long($ip_octet);
		unset($ip_octet);

		// Set user id based on level
		switch ($level) {
			default:
				$user = $_SESSION['userid'];
				break;
			case LOG_LEVEL_ANON:
				$user = 0;
				break;
			case LOG_LEVEL_INSTALL:
				$user = -1;
				break;
		}
		$query = 'INSERT INTO `'.LOG_TABLE.'`
			(`user_id`,`action`,`date`,`ip_addr`)
			VALUES ('.$user.',\''.addslashes($message).'\',\''.DATE_TIME.'\','.$ip.')';
		$log_handle = $db->sql_query($query);
		if ($db->error[$log_handle] === 1) {
			return false;
		}
		return true;
	}

	/**
	 * Clear all log messages
	 * @global acl $acl Permissions object
	 * @global db $db Database connection object
	 * @return boolean Success
	 */
	public function clear() {
		global $acl;
		global $db;

		if (!$acl->check_permission('log_clear')) {
			return false;
			break;
		}
		$delete_query = 'TRUNCATE TABLE `' . LOG_TABLE . '`';
		$delete_handle = $db->sql_query($delete_query);
		if ($db->error[$delete_handle] === 1) {
			return false;
			break;
		}
		$this->new_message('Cleared log messages.',LOG_LEVEL_ADMIN);
		return true;
	}

	/**
	 * Fetch an array of the last n log messages
	 * @global db $db Database connection object
	 * @param integer $count Number of log messages to fetch
	 * @return array Log messages (false if failure)
	 */
	public function get_last_message($count = 1) {
		global $db;

		if (!is_int($count)) {
			return false;
		}

		$query = 'SELECT `user_id`,`action`,`date`,`ip_addr`
			FROM `'.LOG_TABLE.'`
			ORDER BY `log_id` DESC
			LIMIT '.$count;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1) {
			return false;
		}

		$log_messages = array();
		$num_messages = $db->sql_num_rows($handle);
		for ($i = 1; $i <= $num_messages; $i++) {
			$message = $db->sql_fetch_assoc($handle);
			$log_messages[] = $message;
		}

		// Convert User IDs to names and convert IPs to octets
		$uid_cache = array();
		for ($i = 0; $i < count($log_messages); $i++) {
			// Convert IP Address
			$log_messages[$i]['ip_addr'] = long2ip($log_messages[$i]['ip_addr']);

			// Get user names
			if ($log_messages[$i]['user_id'] == 0) {
				$log_messages[$i]['user_name'] = 'Anonymous User';
				continue;
			}
			if ($log_messages[$i]['user_id'] == -1) {
				$log_messages[$i]['user_name'] = 'Installer';
				continue;
			}
			// Check cache so we don't repeat queries where unnecessary
			if (key_exists($log_messages[$i]['user_id'], $uid_cache)) {
				$log_messages[$i]['user_name'] = $uid_cache[$log_messages[$i]['user_id']];
				continue;
			}

			// UID hasn't been seen before so look it up
			$user_query = 'SELECT `realname` FROM `'.USER_TABLE.'`
				WHERE `id` = '.$log_messages[$i]['user_id'];
			$user_handle = $db->sql_query($user_query);
			if ($db->error[$user_handle] === 1) {
				$log_messages[$i]['user_name'] = 'User '.$log_messages[$i]['user_id'];
				$uid_cache[$log_messages[$i]['user_id']] = 'User '.$log_messages[$i]['user_id'];
				continue;
			}
			if ($db->sql_num_rows($user_handle) == 0) {
				$log_messages[$i]['user_name'] = 'User '.$log_messages[$i]['user_id'];
				$uid_cache[$log_messages[$i]['user_id']] = 'User '.$log_messages[$i]['user_id'];
				continue;
			}
			$user_result = $db->sql_fetch_assoc($user_handle);
			$log_messages[$i]['user_name'] = $user_result['realname'];
			$uid_cache[$log_messages[$i]['user_id']] = $user_result['realname'];
		}
		return $log_messages;
	}
}
?>
