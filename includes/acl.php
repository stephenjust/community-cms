<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

class acl {
	/**
	 * $allow_all - Allows bypassing permission checks
	 * @var bool If true, bypass all permission checks
	 */
	private $allow_all = false;

	/**
	 * check_permission - Read from the ACL and check if user is allowed to complete action
	 * @param string $acl_key Name of property in Access Control List
	 * @param int $user Not Used
	 * @param int $group Not Used
	 * @global object $db Database connection object
	 * @return bool True if allowed to complete action, false if not.
	 */
	public function check_permission($acl_key, $user = 0, $group = 0) {
		if ($this->allow_all === true) {
			return true;
		}
		global $db;
		if (isset($_SESSION['userid'])) {
			// Check if user has the dangerous 'all' property
			$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \'all\'
				AND `user` = '.$_SESSION['userid'].'
				AND `is_group` = 0';
			$acl_all_handle = $db->sql_query($acl_all_query);
			if ($db->error[$acl_all_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
				$this->allow_all = true;
				return true;
			} else {
				unset($acl_all_query);
				unset($acl_all_handle);
			}
		}
		// Check group properties
		$group_allow = false;
		$group_list = (isset($_SESSION['groups'])) ? $_SESSION['groups'] : array(0);
		foreach ($group_list as $group) {
			// Check if group has the 'all' property
			$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \'all\' 
				AND `user` = '.$group.' 
				AND `is_group` = 1';
			$acl_all_handle = $db->sql_query($acl_all_query);
			if ($db->error[$acl_all_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
				$result = $db->sql_fetch_assoc($acl_all_handle);
				if ($result['allow'] == 1) {
					$group_allow = true;
					unset($result);
					break;
				}
			} else {
				unset($acl_all_query);
				unset($acl_all_handle);
			}
			// Check if group has requested property
			$acl_group_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \''.$acl_key.'\' 
				AND `user` = '.$group.' 
				AND `is_group` = 1';
			$acl_group_handle = $db->sql_query($acl_group_query);
			if ($db->error[$acl_group_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_group_handle)) {
				$result = $db->sql_fetch_assoc($acl_group_handle);
				if ($result['allow'] == 1) {
					$group_allow = true;
					unset($result);
					break;
				}
			} else {
				unset($acl_group_query);
				unset($acl_group_handle);
			}
		}
		unset($group);
		if (isset($_SESSION['userid'])) {
			// Check if user has the requested property
			$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \''.$acl_key.'\'
				AND `user` = '.$_SESSION['userid'].'
				AND `is_group` = 0';
			$acl_all_handle = $db->sql_query($acl_all_query);
			if ($db->error[$acl_all_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
				$result = $db->sql_fetch_assoc($acl_all_handle);
				if ($result['allow'] == 1) {
					return true;
				}
			} elseif ($db->sql_num_rows($acl_all_handle) === 0 && $group_allow === 1) {
				return true;
			} else {
				unset($acl_all_query);
				unset($acl_all_handle);
			}
		} else {
			return $group_allow;
		}
		return false;
	}
	
	public function set_permission($acl_key, $user = 0, $group = 0) {
		// FIXME: Stub
	}
}

?>
