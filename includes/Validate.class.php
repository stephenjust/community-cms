<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * This class contains various methods to validate input values
 */
class Validate {
	
	/**
	 * Validate e-mail address format
	 * @param String $string
	 * @return Boolean
	 */
	public static function email($string) {
		return preg_match('/^[a-z0-9_\-\.+]+@[a-z0-9\-\.]+\.[a-z]+$/i', $string);
	}
	
	/**
	 * Validate a name
	 * @param String $string
	 * @return Boolean
	 */
	public static function name($string) {
		return preg_match('/^[a-z\'\\-\\.]+$/i', $string);
	}
	
	/**
	 * Validate password
	 * A valid password is at least 8 characters long
	 * @param String $string
	 * @return Boolean
	 */
	public static function password($string) {
		return (strlen($string) >= 8);
	}
	
	/**
	 * Validate telephone number (North-America style)
	 * @param String $string
	 * @return Numeric Telephone number, or 0 if invalid
	 */
	public static function telephone($string) {
		$matches = array();
		$sep = '[\\-\\. ]?';
		if (preg_match("/^(1)?{$sep}\\(?([0-9]{3})\\)?{$sep}([0-9]{3}){$sep}([0-9]{4})$/i", $string, $matches))
			return $matches[1].$matches[2].$matches[3].$matches[4];
		else
			return 0;
	}
	
	/**
	 * Validate username
	 * A valid username is between 4 and 30 alphanumeric characters, _, - and .
	 * @param String $string
	 * @return Boolean
	 */
	public static function username($string) {
		return preg_match('/^[a-z0-9_\-\.]{4,30}$/i', $string);
	}

	/**
	 * Validate date
	 * @param string $string
	 * @return boolean
	 */
	public static function date($string) {
		return preg_match('/[0-9]+\-[0-9]+\-[0-9]+/', $string);
	}
	
}
