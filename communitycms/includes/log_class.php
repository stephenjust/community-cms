<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class Log {
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

	public function get_last_message($count = 1) {
		global $db;

		if (!is_int($count)) {
			return false;
		}

		$query = 'SELECT `user_id`,`action`,`date`,`ip_addr`
			FROM `'.LOG_TABLE.'`
			ORDER BY `id` DESC
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

		// TODO: Convert User ID's to names
		return $message;
	}
}
?>
