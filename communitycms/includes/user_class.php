<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Handle all user-related functions
 * 
 * @package CommunityCMS.main
 */
class user {
	public $logged_in = false;

	/**
	 * Check user's login status
	 * @global db $db Database connection object
	 * @global debug $debug Debug object
	 * @return void
	 */
	function __construct() {
		global $db;
		global $debug;
		// Check if any session variables are not set
		if (!isset($_SESSION['expired']) ||
				!isset($_SESSION['userid']) ||
				!isset($_SESSION['user']) ||
				!isset($_SESSION['pass']) ||
				!isset($_SESSION['name']) ||
				!isset($_SESSION['type']) ||
				!isset($_SESSION['groups']) ||
				!isset($_SESSION['last_login'])) {
			// One or more of the session variables was not set, so clear all
			// of the session variables to make sure that the session remains
			// clean
			$debug->add_trace('Forcing logout due to incomplete set of session vars',false);
			$this->logout();
			return;
		}
		// Validate session if complete set of variables is available
		$query = 'SELECT `id`,`username`,`password`,`realname`,`type`
			FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$_SESSION['user'].'\'
			AND `password` = \''.$_SESSION['pass'].'\'
			AND `type` = \''.$_SESSION['type'].'\'
			AND `lastlogin` = \''.addslashes($_SESSION['last_login']).'\'
			AND `realname` = \''.$_SESSION['name'].'\'';
		$access = $db->sql_query($query);
		$num_rows = $db->sql_num_rows($access);
		if($num_rows != 1) {
			$debug->add_trace('No user exists with those login credentials',true);
			$this->logout();
			err_page(3002);
			return false;
		}
		$userinfo = $db->sql_fetch_assoc($access);
		if(!defined('USERINFO')) {
			define('USERINFO',$userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
		}
		$this->logged_in = true;
		$debug->add_trace('Verified logged-in state',false);
	}

	/**
	 * Check given login information and log in a user
	 * @global db $db Database connection object
	 * @global debug $debug Debug object
	 * @param string $username Username provided by input
	 * @param string $password Unencrypted password provided by input
	 * @return boolean Success
	 */
	function login($username,$password) {
		global $db;
		global $debug;

		// Validate parameters
		if (strlen($username) < 4) {
			$debug->add_trace('User name is too short',true);
			err_page(3001);
			return false;
		}
		if (strlen($password) < 8) {
			$debug->add_trace('Password is too short',true);
			err_page(3001);
			return false;
		}
		$username = $db->sql_escape_string($username);
		$password = md5($password);

		// Get user record
		$query = 'SELECT `id`, `username`, `password`, `password_date`,
			`realname`, `type`, `groups`
			FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'
			AND `password` = \''.$password.'\'';
		$access = $db->sql_query($query);
		if ($db->error[$access] === 1) {
			die('There was an error logging you in');
		}
		$num_rows = $db->sql_num_rows($access);
		$result = $db->sql_fetch_assoc($access);
		if($num_rows != 1) {
			$this->logout();
			err_page(3003);
			return false;
		}
		session_destroy();
		session_set_cookie_params(84000000,get_config('cookie_path'));
		session_name(get_config('cookie_name'));
		session_start();

		// Handle upgrade situations where a user may not have a time of last
		// password change set.
		if ($result['password_date'] == 0) {
			$update_password_date_query = 'UPDATE `'.USER_TABLE.'`
				SET `password_date` = '.time().' WHERE `id` = '.$result['id'];
			$update_password_date_handle = $db->sql_query($update_password_date_query);
			if ($db->error[$update_password_date_handle] === 1) {
				die('Failed to set password creation date to today.');
			}
			$result['password_date'] = time();
		}

		// Check to see if password is expired
		// If 'password_expire' is 0, then password expiration is disabled
		if (get_config('password_expire') != 0) {
			$curtime = time();
			$expiretime = $result['password_date'] + get_config('password_expire');
			if ($curtime > $expiretime) {
				$_GET['page'] = NULL;
				$_GET['id'] = 'change_password';
				$debug->add_trace('Password is expired',true);
				$_SESSION['expired'] = true;
				return false;
			}
		}
		$_SESSION['expired'] = false;

		$_SESSION['userid'] = $result['id'];
		$_SESSION['user'] = $username;
		$_SESSION['pass'] = $password;
		$_SESSION['name'] = $result['realname'];
		$_SESSION['type'] = $result['type'];
		$_SESSION['groups'] = csv2array($result['groups']);
		$_SESSION['last_login'] = time();
		if (!defined('USERINFO')) {
			define('USERINFO',$result['id'].','.$result['realname'].','.$result['type']);
		}

		if (!$this->set_login_time()) {
			$this->logout();
			return false;
		}

		$debug->add_trace('Logged in user',false);
		$this->logged_in = true;
	}

	/**
	 * Destroy all session information
	 * @global debug $debug Debug class
	 */
	function logout() {
		global $debug;
		unset($_SESSION['userid']);
		unset($_SESSION['user']);
		unset($_SESSION['pass']);
		unset($_SESSION['name']);
		unset($_SESSION['type']);
		unset($_SESSION['groups']);
		unset($_SESSION['last_login']);
		unset($_SESSION['expired']);
		session_destroy();
		$debug->add_trace('Logged out user',false);
		session_start();
		$this->logged_in = false;
	}

	/**
	 * Record time of login in the database
	 * @global db $db
	 * @global debug $debug
	 * @return boolean Success
	 */
	private function set_login_time() {
		global $db;
		global $debug;

		$set_logintime_query = 'UPDATE `'.USER_TABLE.'`
			SET `lastlogin` = \''.$_SESSION['last_login'].'\'
			WHERE `id` = '.$_SESSION['userid'];
		$set_logintime_handle = $db->sql_query($set_logintime_query);
		if ($db->error[$set_logintime_handle]) {
			$debug->add_trace('Failed to set log-in time',true);
			return false;
		}
		return true;
	}
}
?>
