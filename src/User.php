<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

class User
{
    private $user_id;
    private $username;
    private $realname;
    private $title;
    private $phone;
    private $email;
    private $address;
    private $last_login_date;
    private $pass_change_date;
    private $groups;
    private $type;

    /**
     * Create a User object from a User ID
     * @param Integer $user_id
     * @throws UserException
     */
    public function __construct($user_id)
    {
        $query = 'SELECT `type`, `username`, `password_date`, `realname`,
			`title`, `groups`, `phone`, `email`, `address`, `lastlogin`
			FROM `'.USER_TABLE."`
			WHERE `id` = :id";
        try {
            $result = DBConn::get()->query($query, [":id" => $user_id], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new UserException("Failed to look up user.", $ex);
        }

        if (!$result) {
            throw new UserException("User not found.");
        }

        // Fill class attributes
        $this->user_id = $user_id;
        $this->username = $result['username'];
        $this->realname = $result['realname'];
        $this->title = $result['title'];
        $this->phone = $result['phone'];
        $this->email = $result['email'];
        $this->address = $result['address'];
        $this->last_login_date = $result['lastlogin'];
        $this->pass_change_date = $result['password_date'];
        $this->groups = explode(',', $result['groups']);
        $this->type = $result['type'];
    }

    /**
     * Create a user
     * @param String $username
     * @param String $password
     * @param String $f_name
     * @param String $l_name
     * @param String $tel
     * @param String $address
     * @param String $email
     * @param String $title
     * @param int[]  $groups
     * @return \User
     * @throws UserException
     */
    public static function create($username, $password,
        $f_name = null, $l_name = null, $tel = null,
        $address = null, $email = null, $title = null, $groups = null
    ) {
        acl::get()->require_permission('user_create');

        // Validate input
        if (!strlen($username) || !strlen($password)) {
            throw new UserException('Username and password may not be blank.');
        }
        if (!Validate::username($username)) {
            throw new UserException('Username is invalid. Usernames must be between 4 and 30 alphanumeric characters.');
        }
        if (User::exists($username)) {
            throw new UserException('Username already taken.');
        }
        if (!Validate::password($password)) {
            throw new UserException('Password is invalid. Passwords must be at least 8 characters long.');
        }
        if ($email != null && !Validate::email($email)) {
            throw new UserException('Email address is invalid.');
        }
        if ($tel != null && !Validate::telephone($tel)) {
            throw new UserException('Telephone number is invalid. Most 10 or 11 digit formats are acceptable.');
        }
        $tel = Validate::telephone($tel);
        if (!Validate::name($l_name) || !Validate::name($f_name)) {
            throw new UserException('Name contains invalid characters.');
        }
        $real_name = $db->sql_escape_string("$l_name, $f_name");
        $title = $db->sql_escape_string($title);
        $groups = (is_array($groups))
        ? implode(',', $groups) : null;
        $address = $db->sql_escape_string($address);

        $time = time();

        $query = 'INSERT INTO `'.USER_TABLE."`
			(`type`, `username`, `password`, `password_date`, `realname`,
			`title`, `groups`, `phone`, `email`, `address`)
			VALUES
			(2, :username, :password, :time, :real_name,
			:title, :groups, :tel, :email, :address)";
        try {
            DBConn::get()->query($query,
                [
                    ":username" => $username,
                    ":password" => md5($password),
                    ":time" => $time,
                    ":real_name" => $real_name,
                    ":title" => $title,
                    ":groups" => $groups,
                    ":tel" => $tel,
                    ":email" => $email,
                    ":address" => $address
                ], DBConn::NOTHING);
            Log::addMessage("Created user '$real_name' ($username)");
            return new User(User::exists($username));
        } catch (Exception $ex) {
            throw new UserException("Failed to create user.", $ex);
        }
    }

    /**
     * Get all users
     * @return \CommunityCMS\User
     * @throws UserException
     */
    public static function getAll()
    {
        $query = 'SELECT `id` FROM `'.USER_TABLE.'` ORDER BY `realname` ASC';

        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);

            $users = [];
            foreach ($results as $result) {
                $users[] = new User($result['id']);
            }
            return $users;
        } catch (Exceptions\DBException $ex) {
            throw new UserException("Failed to load users.", $ex);
        }
    }

    /**
     * Remove a user record from the database
     * @throws UserException
     */
    public function delete()
    {
        acl::get()->require_permission('user_delete');
        if ($this->user_id == 1) {
            throw new UserException('Cannot delete Administrator user.');
        }

        $query = 'DELETE FROM `'.USER_TABLE.'`
			WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [':id' => $this->user_id], DBConn::NOTHING);
            Log::addMessage("Deleted user '$this->realname' ($this->username)");
        } catch (Exceptions\DBException $ex) {
            throw new UserException('Failed to delete user.', $ex);
        }
    }

    /**
     * Check whether a user exists
     * @param String $username
     * @return Numeric User ID, 0 if user does not exist
     * @throws UserException
     */
    public static function exists($username)
    {
        if (!Validate::username($username)) {
            throw new UserException('Invalid username.');
        }

        $query = 'SELECT `id` FROM `'.USER_TABLE.'`
            WHERE `username` = :username';
        try {
            $result = DBConn::get()->query($query, [":username" => $username], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new UserException("Failed to look up user.", $ex);
        }
        if (!$result) {
            return 0;
        } else {
            return $result['id'];
        }
    }

    /**
     * Get the user ID
     * @return integer
     */
    public function getId()
    {
        return $this->user_id;
    }

    /**
     * Get the user's username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the user's name
     * @return string
     */
    public function getName()
    {
        return $this->realname;
    }

    /**
     * Check if password is past its expiration date
     * @return boolean
     */
    public function isPasswordExpired()
    {
        if (SysConfig::get()->getValue('password_expire') == 0) {
            return false; // Password expiration disabled
        }
        // Reset password change date if data came from old database format
        if ($this->password_change_date == 0) {
            $this->setPasswordChangeDate();
        }

        $curtime = time();
        $expiretime = $this->pass_change_date + SysConfig::get()->getValue('password_expire');
        if ($curtime > $expiretime) {
            return true;
        }
    }

    /**
     * Set password changed date
     * @throws UserException
     */
    private function setPasswordChangeDate()
    {
        $new_time = time();
        $query = 'UPDATE `'.USER_TABLE."`
			SET `password_date` = :new_time
			WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [":id" => $this->user_id, ":new_time" => $new_time], DBConn::NOTHING);
            $this->pass_change_date = $new_time;
        } catch (Exceptions\DBException $ex) {
            throw new UserException("Failed to set password creation time.", $ex);
        }
    }
}

class UserException extends \Exception
{
}
