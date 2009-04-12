<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function login($user,$passwd) {
		global $CONFIG;
		global $db;
		$user = addslashes(mysqli_real_escape_string($db,$user));
		$passwd = addslashes(mysqli_real_escape_string($db,$passwd));
		if($user == "" || $passwd == "") {
			err_page(3001);
			} else {
			$query = 'SELECT id,username,password,realname,type FROM '.$CONFIG['db_prefix'].'users WHERE username = \''.$user.'\' AND password = \''.md5($passwd).'\'';
			$access = $db->query($query);
			$num_rows = $access->num_rows;
			$result = $access->fetch_assoc();
			if($num_rows != 1) {
				logout();
				err_page(3003);
				} else {
				session_set_cookie_params(84000000);
				$_SESSION['userid'] = $result['id'];
				$_SESSION['user'] = $user;
				$_SESSION['pass'] = md5($passwd);
				$_SESSION['name'] = $result['realname'];
				$_SESSION['type'] = $result['type'];
				$_SESSION['last_login'] = time();
				define('USERINFO',$result['id'].','.$result['realname'].','.$result['type']);
				// Set latest login time
				$set_logintime_query = 'UPDATE '.$CONFIG['db_prefix'].'users SET lastlogin='.$_SESSION['last_login'].' WHERE id = '.$_SESSION['userid'].' LIMIT 1';
				$set_logintime_handle = $db->query($set_logintime_query);
				if(!$set_logintime_handle) {
					logout();
					}
				}
			}
		}
	function logout() {
		unset($_SESSION['userid']);
		unset($_SESSION['user']);
		unset($_SESSION['pass']);
	  unset($_SESSION['name']);
	  unset($_SESSION['type']);
	  unset($_SESSION['lastlogin']);
	  session_destroy();
		}
	function checkuser($mustbeloggedin = 0) {
		global $CONFIG;
		global $db;
		if(isset($_SESSION['user'])) {
			if(!isset($_SESSION['pass']) || !isset($_SESSION['name'])) {
				logout();
				return false;
				}
			$query = 'SELECT id,username,password,realname,type FROM '.$CONFIG['db_prefix'].'users WHERE username = \''.$_SESSION['user'].'\' AND password = \''.$_SESSION['pass'].'\' AND type = \''.$_SESSION['type'].'\' AND realname = \''.$_SESSION['name'].'\'';
			$access = $db->query($query);
			$num_rows = $access->num_rows;
		  if($num_rows != 1) {
				logout();
				err_page(3002);
				return false;
				}
			$userinfo = $access->fetch_assoc();
			if(!defined('USERINFO')) {
				define('USERINFO',$userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
				}
			return true;
			}
		if($mustbeloggedin == 1) {
			err_page(3004);
			return false;
			}
		return false; // Even if you are not required to be logged in, return false
    }
    /**
     * checkuser_admin - Check if a user is logged in as an administrator
     * @global array $CONFIG
     * @global resource $db
     * @return bool
     */
	function checkuser_admin() {
		global $CONFIG;
		global $db;
		if($_SESSION['type'] < 1) {
			err_page(3004);
			}
		$query = 'SELECT username,password,realname,type,lastlogin FROM '
            .$CONFIG['db_prefix'].'users WHERE username = \''.$_SESSION['user'].'\'
            AND password = \''.$_SESSION['pass'].'\' AND type = \''.$_SESSION['type'].'\'
            AND lastlogin = '.addslashes($_SESSION['last_login']).' AND
            realname = \''.$_SESSION['name'].'\'';
		$access = $db->query($query);
		$num_rows = $access->num_rows;
	  if($num_rows != 1) {
			logout();
			err_page(3002);
			return false;
			} else {
			$access_info = $access->fetch_assoc();
			if($access_info['lastlogin'] <= time() - (60 * 60 * 12)) {
				logout();
				err_page(3002);
				return false;
				}
			}
			$userinfo = $access->fetch_assoc();
			define('USERINFO',$userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
		}
?>
