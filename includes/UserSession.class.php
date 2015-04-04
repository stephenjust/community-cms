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

/**
 * Handle all user-related functions
 * 
 * @package CommunityCMS.main
 */
class UserSession
{
    private static $session = null;
    public $logged_in = false;

    /**
     * @return \UserSession
     */
    public static function get() 
    {
        if (self::$session == null) {
            self::$session = new self();
        }
        return self::$session;
    }

    /**
     * Check user's login status
     * @global db $db Database connection object
     * @global Debug $debug Debug object
     * @return void
     */
    function __construct() 
    {
        global $db;
        global $debug;
        // Check if any session variables are not set
        if (!isset($_SESSION['expired']) 
            || !isset($_SESSION['userid']) 
            || !isset($_SESSION['user']) 
            || !isset($_SESSION['pass']) 
            || !isset($_SESSION['name']) 
            || !isset($_SESSION['type']) 
            || !isset($_SESSION['groups']) 
            || !isset($_SESSION['last_login'])
        ) {
            // One or more of the session variables was not set, so clear all
            // of the session variables to make sure that the session remains
            // clean
            $debug->addMessage('Forcing logout due to incomplete set of session vars', false);
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
            $debug->addMessage('No user exists with those login credentials', true);
            $this->logout();
            err_page(3002);
            return false;
        }
        $userinfo = $db->sql_fetch_assoc($access);
        if(!defined('USERINFO')) {
            define('USERINFO', $userinfo['id'].','.$userinfo['realname'].','.$userinfo['type']);
        }
        $this->logged_in = true;
        $debug->addMessage('Verified logged-in state', false);
        
        self::$session = $this;
    }

    /**
     * Check given login information and log in a user
     * @global db $db Database connection object
     * @global Debug $debug Debug object
     * @param string $username Username provided by input
     * @param string $password Unencrypted password provided by input
     * @return boolean Success
     */
    function login($username,$password) 
    {
        global $db;
        global $debug;

        // Validate parameters
        if (!Validate::username($username)) {
            $debug->addMessage('Invalid username format', true);
            err_page(3001);
            return false;
        }
        if (!Validate::password($password)) {
            $debug->addMessage('Invalid password format', true);
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
        session_set_cookie_params(84000000, get_config('cookie_path'));
        session_name(get_config('cookie_name'));
        session_start();

        $u = new User($result['id']);
        if ($u->isPasswordExpired()) {
            $_GET['page'] = null;
            $_GET['id'] = 'change_password';
            $debug->addMessage('Password is expired', true);
            $_SESSION['expired'] = true;
            return false;
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
            define('USERINFO', $result['id'].','.$result['realname'].','.$result['type']);
        }

        if (!$this->set_login_time()) {
            $this->logout();
            return false;
        }

        $debug->addMessage('Logged in user', false);
        $this->logged_in = true;
    }

    /**
     * Destroy all session information
     * @global Debug $debug Debug class
     */
    function logout() 
    {
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
        $debug->addMessage('Logged out user', false);
        session_start();
        $this->logged_in = false;
    }

    /**
     * Record time of login in the database
     * @global db $db
     * @global Debug $debug
     * @return boolean Success
     */
    private function set_login_time() 
    {
        global $db;
        global $debug;

        $set_logintime_query = 'UPDATE `'.USER_TABLE.'`
			SET `lastlogin` = \''.$_SESSION['last_login'].'\'
			WHERE `id` = '.$_SESSION['userid'];
        $set_logintime_handle = $db->sql_query($set_logintime_query);
        if ($db->error[$set_logintime_handle]) {
            $debug->addMessage('Failed to set log-in time', true);
            return false;
        }
        return true;
    }
}
