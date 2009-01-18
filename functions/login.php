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
				err_page(3003);
				} else {
				session_set_cookie_params(84000000);
				$_SESSION['userid'] = $result['id'];
				$_SESSION['user'] = $user;
				$_SESSION['pass'] = md5($passwd);
				$_SESSION['name'] = $result['realname'];
				$_SESSION['type'] = $result['type'];
				define('USERINFO',$result['id'].','.$result['realname'].','.$result['type']);
				}
			}
		}
	function logout() {
		unset($_SESSION['userid']);
		unset($_SESSION['user']);
		unset($_SESSION['pass']);
	  unset($_SESSION['name']);
	  unset($_SESSION['type']);
	  session_destroy();
		}
	function checkuser($mustbeloggedin = 0) {
		global $CONFIG;
		global $db;
		if(isset($_SESSION['user'])) {
			$query = 'SELECT id,username,password,realname,type FROM '.$CONFIG['db_prefix'].'users WHERE username = \''.$_SESSION['user'].'\' AND password = \''.$_SESSION['pass'].'\' AND type = \''.$_SESSION['type'].'\' AND realname = \''.$_SESSION['name'].'\'';
			$access = $db->query($query);
			$num_rows = $access->num_rows;
		  if($num_rows != 1) {
				logout();
				err_page(3002);
				return;
				}
			$userinfo = $access->fetch_assoc();
			if(!defined('USERINFO')) {
				define('USERINFO',$userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
				}
			return;
			}
		if($mustbeloggedin == 1) {
			err_page(3004);
			return;
			}
		}
	function checkuser_admin() {
		global $CONFIG;
		global $db;
		if($_SESSION['type'] < 1) {
			err_page(3004);
			}
		$query = 'SELECT username,password,realname,type FROM '.$CONFIG['db_prefix'].'users WHERE username = \''.$_SESSION['user'].'\' AND password = \''.$_SESSION['pass'].'\' AND type = \''.$_SESSION['type'].'\' AND realname = \''.$_SESSION['name'].'\'';
		$access = $db->query($query);
		$num_rows = $access->num_rows;
	  if($num_rows != 1) {
			logout();
			err_page(3002);
			return;
			}
			$userinfo = $access->fetch_assoc();
			define('USERINFO',$userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
		}
?>
