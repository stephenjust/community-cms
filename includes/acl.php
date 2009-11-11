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
	 * @param int $group Group to check (current user's group if not set)
	 * @global object $db Database connection object
	 * @return boolean True if allowed to complete action, false if not.
	 */
	public function check_permission($acl_key, $group = 0) {
		global $db;
		if (!is_numeric($group)) {
			return false;
		}
		if ($group == 0) {
			if (!isset($_SESSION['groups'])) {
				$group_array = array();
			} else {
				$group_array = $_SESSION['groups'];
			}
		} else {
			$group_array = array($group);
		}
		if ($this->permission_list == array()) {
			$this->permission_list = $this->get_acl_key_names();
		}
		foreach ($group_array AS $cur_group) {
			// Check if group has the dangerous 'all' property
			$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
				WHERE `acl_id` = \''.$this->permission_list['all']['id'].'\'
				AND `group` = '.$cur_group;
			$acl_all_handle = $db->sql_query($acl_all_query);
			if ($db->error[$acl_all_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
				return true;
			} else {
				unset($acl_all_query);
				unset($acl_all_handle);
				unset($cur_group);
			}
		}
		foreach ($group_array AS $cur_group) {
			// Check if user or group has the requested property
			$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \''.$this->permission_list[$acl_key]['id'].'\'
				AND `group` = '.$cur_group;
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
				unset($cur_group);
			}
		}
		return false;
	}
	
	public function set_permission($acl_key, $value, $group) {
		global $db;
		$value = (int)$value;
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
			AND `group` = '.$group;
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
				(`acl_id`,`group`,`value`)
				VALUES (\''.$this->permission_list[$acl_key]['id'].'\','.$group.','.$value.')';
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
			$return[$key_info['acl_name']]['shortname'] = $key_info['acl_name'];
		}
		return $return;
	}

	/**
	 * create_key - Create an ACL key if it does not exist already
	 * @global object $db Database connection object
	 * @global object $debug Debug object
	 * @param string $name Name of key (lowercase)
	 * @param string $longname More descriptive name
	 * @param string $description Description of what the key allows
	 * @param int $default_value Allow by default? 1 = yes, 0 = no; default 0
	 * @return boolean
	 */
	public function create_key($name,$longname,$description,$default_value = 0) {
		global $db;
		global $debug;
		// Validate parameters
		if (!is_string($name)) {
			$debug->add_trace('$name is not a string',true,'create_key');
			return false;
		}
		if (!is_string($longname)) {
			$debug->add_trace('$longname is not a string',true,'create_key');
			return false;
		}
		if (!is_string($description)) {
			$debug->add_trace('$description is not a string',true,'create_key');
			return false;
		}
		if (!is_int($default_value)) {
			$debug->add_trace('$default_value is not an integer',true,'create_key');
			return false;
		}
		// Check if key already exists
		if ($this->permission_list == array()) {
			$this->permission_list = $this->get_acl_key_names();
		}
		if (isset($this->permission_list[$name])) {
			$debug->add_trace('The ACL key '.$name.' already exists',true,'create_key');
			return false;
		}
		// Make sure that you read permission list on next permission check
		$this->permission_list = array();
		
		// Add key
		$prep = $db->sql_prepare('acl_new_key','INSERT INTO '.ACL_KEYS_TABLE.'
			(acl_name,acl_longname,acl_description,acl_value_default)
			VALUES ( ?, ?, ?, ? )');
		if (!$prep) {
			return false;
		}
		$prep_exec = $db->sql_prepare_exec('acl_new_key',
			array($name,$longname,$description,$default_value),
			array('text','text','text','int'));
		if (!$prep_exec) {
			return false;
		}
	}
}

?>
