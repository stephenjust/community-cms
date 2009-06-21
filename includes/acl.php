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
	public $permission_list = array();

	/**
	 * check_permission - Read from the ACL and check if user is allowed to complete action
	 * @param string $acl_key Name of property in Access Control List
	 * @param int $usr User to check (current user if not set)
	 * @param boolean $is_group True if $usr refers to a group id
	 * @global object $db Database connection object
	 * @return boolean True if allowed to complete action, false if not.
	 */
	public function check_permission($acl_key, $usr = 0, $is_group = false) {
		global $db;
		if ($this->permission_list == array()) {
			$this->permission_list = $this->get_acl_key_names();
		}
		if ($is_group == false && isset($_SESSION['userid'])) {
			$user = (int)$_SESSION['userid'];
			$is_group = 0;
		} elseif ($usr != 0) {
			$user = (int)$usr;
			$is_group = (int)$is_group;
		} else {
			return false;
		}
		// Check if user or group has the dangerous 'all' property
		$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
			WHERE `acl_id` = \''.$this->permission_list['all']['id'].'\'
			AND `user` = '.$user.'
			AND `is_group` = '.$is_group;
		$acl_all_handle = $db->sql_query($acl_all_query);
		if ($db->error[$acl_all_handle] === 1) {
			return false;
		} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
			return true;
		} else {
			unset($acl_all_query);
			unset($acl_all_handle);
		}
		// Check if user or group has the requested property
		$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
			WHERE `acl_key` = \''.$this->permission_list[$acl_key]['id'].'\'
			AND `user` = '.$user.'
			AND `is_group` = '.$is_group;
		$acl_all_handle = $db->sql_query($acl_all_query);
		if ($db->error[$acl_all_handle] === 1) {
			return false;
		} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
			$result = $db->sql_fetch_assoc($acl_all_handle);
			if ($result['value'] == 1) {
				return true;
			}
		} else {
			unset($acl_all_query);
			unset($acl_all_handle);
		}
		return false;
	}
	
	public function set_permission($acl_key, $value, $user, $is_group = false) {
		global $db;
		$value = (int)$value;
		$is_group = (int)$is_group;
		if (!array_key_exists($acl_key,$this->permission_list)) {
			echo 'The key \''.$acl_key.'\' does not exist.<br />';
			return false;
		}
		if (!$this->check_permission('set_permissions')) {
			echo 'You are not allowed to set permissions.<br />';
			return false;
		}
		$check_if_exists_query = 'SELECT acl_record_id,value FROM `' . ACL_TABLE . '`
			WHERE `acl_id` = \''.$this->permission_list[$acl_key]['id'].'\'
			AND `user` = '.$user.'
			AND `is_group` = '.$is_group;
		$check_if_exists_handle = $db->sql_query($check_if_exists_query);
		if ($db->error[$check_if_exists_handle] === 1) {
			return false;
		}
		if ($db->sql_num_rows($check_if_exists_handle) == 1) {
			$check_if_exists = $db->sql_fetch_assoc($check_if_exists_handle);
			$set_permission_query = 'UPDATE `' . ACL_TABLE . '`
				SET `value` = '.$value.'
				WHERE `acl_record_id` = '.$check_if_exists['acl_record_id'];
		} else {
			$set_permission_query = 'INSERT INTO `' . ACL_TABLE . '`
				(`acl_id`,`user`,`is_group`,`value`)
				VALUES (\''.$this->permission_list[$acl_key]['id'].'\','.$user.','.$is_group.','.$value.')';
		}
		$set_permission_handle = $db->sql_query($set_permission_query);
		if ($db->error[$set_permission_handle] === 1) {
			return false;
		}
		return true;
	}

	/**
	 * get_acl_key_names - Load the list of permissions from the database
	 * @global object $db Database connection object
	 * @return array An array of all existing permission keys
	 */
	private function get_acl_key_names() {
		global $db;
		$load_keys_query = 'SELECT * FROM `' . ACL_KEYS_TABLE . '`';
		$load_keys_handle = $db->sql_query($load_keys_query);
		if ($db->error[$load_keys_handle]) {
			die('Could not load permission information from the database.');
		}
		$return = array();
		for ($i = 0; $i < $db->sql_num_rows($load_keys_handle); $i++) {
			$key_info = $db->sql_fetch_assoc($load_keys_handle);
			$return[$key_info['acl_name']] = array();
			$return[$key_info['acl_name']]['id'] = $key_info['acl_id'];
			$return[$key_info['acl_name']]['longname'] = $key_info['acl_longname'];
			$return[$key_info['acl_name']]['description'] = $key_info['acl_description'];
			$return[$key_info['acl_name']]['default'] = $key_info['acl_value_default'];
		}
		return $return;
	}
}

?>
