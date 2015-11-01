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
     * @return void
     */
    function __construct()
    {
        // Check if any session variables are not set
        if (!$this->checkSessionVars()) {
            // One or more of the session variables was not set, so clear all
            // of the session variables to make sure that the session remains
            // clean
            Debug::get()->addMessage('Forcing logout due to incomplete set of session vars', false);
            $this->logout();
            return;
        }
        if (!$this->validateSession()) {
            Debug::get()->addMessage('No user exists with those login credentials', true);
            $this->logout();
            err_page(3002);
            return false;
        }
        $this->logged_in = true;
        Debug::get()->addMessage('Verified logged-in state', false);

        self::$session = $this;
    }

    private function checkSessionVars()
    {
        if (!isset($_SESSION['userid'])
            || !isset($_SESSION['user'])
            || !isset($_SESSION['pass'])
            || !isset($_SESSION['name'])
            || !isset($_SESSION['groups'])
            || !isset($_SESSION['last_login'])
        ) {
            // One or more of the session variables was not set
            return false;
        }
        return true;
    }

    private function validateSession()
    {
        $query = 'SELECT `id` FROM `'.USER_TABLE.'` '
            . 'WHERE `username` = :username '
            . 'AND `password` = :password '
            . 'AND `lastlogin` = :last_login '
            . 'AND `realname` = :realname';
        try {
            $count = DBConn::get()->query(
                $query,
                [
                    ':username' => $_SESSION['user'],
                    ':password' => $_SESSION['pass'],
                    ':last_login' => $_SESSION['last_login'],
                    ':realname' => $_SESSION['name']
                ],
                DBConn::ROW_COUNT);
        } catch (Exceptions\DBException $ex) {
            return false;
        }
        if ($count == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check given login information and log in a user
     * @param string $username Username provided by input
     * @param string $password Unencrypted password provided by input
     * @return boolean Success
     */
    function login($username, $password)
    {
        // Validate parameters
        if (!Validate::username($username)) {
            Debug::get()->addMessage('Invalid username format', true);
            err_page(3001);
            return false;
        }
        if (!Validate::password($password)) {
            Debug::get()->addMessage('Invalid password format', true);
            err_page(3001);
            return false;
        }
        $user = User::getByUsername($username);
        if (!$user || !$user->isPasswordCorrect($password)) {
            $this->logout();
            err_page(3003);
            return false;
        }
        session_destroy();
        session_set_cookie_params(84000000, SysConfig::get()->getValue('cookie_path'));
        session_name(SysConfig::get()->getValue('cookie_name'));
        session_start();

        $time = time();
        $_SESSION['userid'] = $user->getId();
        $_SESSION['user'] = $username;
        $_SESSION['pass'] = md5($password);
        $_SESSION['name'] = $user->getName();
        $_SESSION['groups'] = $user->getGroups();
        $_SESSION['last_login'] = $time;

        if (!$user->setLoginTime($time)) {
            $this->logout();
            return false;
        }

        Debug::get()->addMessage('Logged in user', false);
        $this->logged_in = true;
    }

    /**
     * Destroy all session information
     */
    function logout()
    {
        unset($_SESSION['userid']);
        unset($_SESSION['user']);
        unset($_SESSION['pass']);
        unset($_SESSION['name']);
        unset($_SESSION['groups']);
        unset($_SESSION['last_login']);
        unset($_SESSION['expired']);
        session_destroy();
        Debug::get()->addMessage('Logged out user', false);
        session_start();
        $this->logged_in = false;
    }
}
