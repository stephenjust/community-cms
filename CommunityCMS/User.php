<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
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
     * @global db $db
     * @param Integer $user_id
     * @throws UserException
     */
    public function __construct($user_id) 
    {
        global $db;

        if ($user_id == 0 || !is_numeric($user_id)) {
            throw new UserException('Invalid User ID.'); 
        }
        
        // Query for user
        $query = 'SELECT `type`, `username`, `password_date`, `realname`,
			`title`, `groups`, `phone`, `email`, `address`, `lastlogin`
			FROM `'.USER_TABLE."`
			WHERE `id` = $user_id";
        $handle = $db->sql_query($query);
        if ($db->error[$handle]) {
            throw new UserException('Failed to look up user.'); 
        }
        
        if ($db->sql_num_rows($handle) == 0) {
            throw new UserException('User not found.'); 
        }
        $result = $db->sql_fetch_assoc($handle);
        
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
        $this->groups = csv2array($result['groups']);
        $this->type = $result['type'];
    }
    
    /**
     * Create a user
     * @global db $db
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
     * @throws InsufficientPermissionException
     * @throws UserException
     */
    public static function create($username, $password,
        $f_name = null, $l_name = null, $tel = null,
        $address = null, $email = null, $title = null, $groups = null
    ) {
        global $db;

        acl::get()->requirePermission('user_create');

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
        ? array2csv($groups) : null;
        $address = $db->sql_escape_string($address);

        $time = time();
        
        $query = 'INSERT INTO `'.USER_TABLE."`
			(`type`, `username`, `password`, `password_date`, `realname`,
			`title`, `groups`, `phone`, `email`, `address`)
			VALUES
			(2, '$username', '".md5($password)."', $time, '$real_name',
			'$title', '$groups', '$tel', '$email', '$address')";
        $create_user = $db->sql_query($query);
        if ($db->error[$create_user] === 1) {
            throw new UserException('Failed to create user.'); 
        }
        
        Log::addMessage('Created user \''.$real_name.'\' ('.$username.')');
        return new User(User::exists($username));
    }
    
    /**
     * Remove a user record from the database
     * @global db $db
     * @throws InsufficientPermissionException
     * @throws UserException
     */
    public function delete() 
    {
        global $db;

        acl::get()->requirePermission('user_delete');
        
        if ($this->user_id == 1) {
            throw new UserException('Cannot delete Administrator user.'); 
        }
        
        $query = 'DELETE FROM `'.USER_TABLE.'`
			WHERE `id` = '.$this->user_id;
        $handle = $db->sql_query($query);
        if ($db->error[$handle]) {
            throw new UserException('Failed to delete user.'); 
        }
        
        Log::addMessage("Deleted user '$this->realname' ($this->username)");
        $this->user_id = 0;
        $this->username = null;
    }
    
    /**
     * Check whether a user exists
     * @global db $db
     * @param String $username
     * @return Numeric User ID, 0 if user does not exist
     * @throws UserException
     */
    public static function exists($username) 
    {
        global $db;
        
        if (!Validate::username($username)) {
            throw new UserException('Invalid username.'); 
        }
        
        $query = 'SELECT `id` FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'';
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new UserException('Failed to look up user.'); 
        }
        
        $num_results = $db->sql_num_rows($handle);
        if ($num_results == 0) {
            return 0; 
        }
        $result = $db->sql_fetch_row($handle);
        
        return $result[0];
    }
    
    /**
     * Check if password is past its expiration date
     * @return boolean
     */
    public function isPasswordExpired() 
    {
        if (get_config('password_expire') == 0) {
            return false; // Password expiration disabled
        }        
        // Reset password change date if data came from old database format
        if ($this->password_change_date == 0) {
            $this->setPasswordChangeDate(); 
        }
        
        $curtime = time();
        $expiretime = $this->pass_change_date + get_config('password_expire');
        if ($curtime > $expiretime) {
            return true; 
        }
    }
    
    /**
     * Set password changed date
     * @global db $db
     * @throws UserException
     */
    private function setPasswordChangeDate() 
    {
        global $db;
        
        $new_time = time();
        $query = 'UPDATE `'.USER_TABLE."`
			SET `password_date` = $new_time
			WHERE `id` = $this->user_id";
        $handle = $db->sql_query($query);
        if ($db->error[$handle]) {
            throw new UserException('Failed to set password creation time.'); 
        }
        $this->pass_change_date = $new_time;
    }
}

class UserException extends \Exception
{
}
