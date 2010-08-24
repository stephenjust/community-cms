<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**
 * Check if a library of functions is present
 *
 * This function allows more complex verification without turning the install
 * file into a mess. Tests for specific library versions could be tested for
 * later, or tests for specific functions that would impair functionality if
 * not present.
 *
 * @param string $library Library name
 * @return boolean
 */
function check_library($library) {
	switch ($library) {
		default:
			return false;
			break;

		case 'mysqli':
			if (function_exists('mysqli_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'postgresql':
			if (function_exists('pg_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'gd':
			if (function_exists('imageCreateTrueColor')) {
				return true;
			} else {
				return false;
			}
	}
}
?>
