<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/Validate.class.php');

class User {
	private $user_id;
	
	public function __construct($user_id) {
		if ($user_id == 0)
			throw new UserException('Invalid User ID.');
		
		$this->user_id = $user_id;
	}
	
	/**
	 * Create a user
	 * @global acl $acl
	 * @global db $db
	 * @param String $username
	 * @param String $password
	 * @param String $f_name
	 * @param String $l_name
	 * @param String $tel
	 * @param String $address
	 * @param String $email
	 * @param String $title
	 * @param int[] $groups
	 * @return \User
	 * @throws AclException
	 * @throws UserException
	 */
	public static function create($username, $password,
			$f_name = null, $l_name = null, $tel = null,
			$address = null, $email = null, $title = null, $groups = null) {
		global $acl;
		global $db;

		// Check permissions
		if (!$acl->check_permission('user_create'))
			throw new AclException('You do not have the necessary permissions to create a new user.');

		// Validate input
		if (!strlen($username) || !strlen($password))
			throw new UserException('Username and password may not be blank.');
		if (!Validate::username($username))
			throw new UserException('Username is invalid. Usernames must be between 6 and 30 alphanumeric characters.');
		if (User::exists($username))
			throw new UserException('Username already taken.');
		if (!Validate::password($password))
			throw new UserException('Password is invalid. Passwords must be at least 8 characters long.');
		if ($email != null && !Validate::email($email))
			throw new UserException('Email address is invalid.');
		if ($tel != null && !Validate::telephone($tel))
			throw new UserException('Telephone number is invalid. Most 10 or 11 digit formats are acceptable.');
		$tel = Validate::telephone($tel);
		if (!Validate::name($l_name) || !Validate::name($f_name))
			throw new UserException('Name contains invalid characters.');		
		$real_name = $db->sql_escape_string("$l_name, $f_name");
		$title = $db->sql_escape_string($title);
		$groups = (is_array($groups))
			? array2csv($groups) : NULL;
		$address = $db->sql_escape_string($address);

		$time = time();
		
		$query = 'INSERT INTO `'.USER_TABLE."`
			(`type`, `username`, `password`, `password_date`, `realname`,
			`title`, `groups`, `phone`, `email`, `address`)
			VALUES
			(2, '$username', '".md5($password)."', $time, '$real_name',
			'$title', '$groups', '$tel', '$email', '$address')";
		$create_user = $db->sql_query($query);
		if ($db->error[$create_user] === 1)
			throw new UserException('Failed to create user.');
		
		Log::addMessage('Created user \''.$real_name.'\' ('.$username.')');
		return new User(User::exists($username));
	}
	
	/**
	 * Check whether a user exists
	 * @global db $db
	 * @param String $username
	 * @return Numeric User ID, 0 if user does not exist
	 * @throws UserException
	 */
	public static function exists($username) {
		global $db;
		
		if (!Validate::username($username))
			throw new UserException('Invalid username.');
		
		$query = 'SELECT `id` FROM `'.USER_TABLE.'`
			WHERE `username` = \''.$username.'\'';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new UserException('Failed to look up user.');
		
		$num_results = $db->sql_num_rows($handle);
		if ($num_results == 0)
			return 0;
		$result = $db->sql_fetch_row($handle);
		
		return $result[0];
	}
	
}

class UserException extends Exception {}
?>
