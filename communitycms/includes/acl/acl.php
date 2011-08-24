<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

/**
 * Class for managing user permissions within the CMS
 * @package CommunityCMS.main
 */
class acl {
	/**#@+
	 * @var array
	 */
	public $permission_list;
	private $acl_cache = array();
	/**#@-*/

	function acl() {
		$this->permission_list = $this->get_acl_key_names();
	}

	/**
	 * check_permission - Read from the ACL and check if user is allowed to complete action
	 * @param string $acl_key Name of property in Access Control List
	 * @param int $group Group to check (current user's group if not set)
	 * @param boolean $true_if_all Automatically return true if the group has 'All Permissions' set
	 * @global db $db Database connection object
	 * @global Debug $debug Debug object
	 * @return boolean True if allowed to complete action, false if not.
	 */
	public function check_permission($acl_key, $group = 0, $true_if_all = true) {
		global $db;
		global $debug;

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

		// See if permission exists
		if (!isset($this->permission_list[$acl_key])) {
			$debug->addMessage('Permission \''.$acl_key.'\' does not exist',true);
		}

		if ($true_if_all == true) {
			foreach ($group_array AS $cur_group) {
				// Check cache
				if (isset($this->acl_cache[$cur_group][$this->permission_list['all']['id']])) {
					if ($this->acl_cache[$cur_group][$this->permission_list['all']['id']] === true) {
						return $this->acl_cache[$cur_group][$this->permission_list['all']['id']];
					} else {
						break;
					}
				}

				// Check if group has the dangerous 'all' property
				$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
					WHERE `acl_id` = '.$this->permission_list['all']['id'].'
					AND `group` = '.$cur_group;
				$acl_all_handle = $db->sql_query($acl_all_query);
				if ($db->error[$acl_all_handle] === 1) {
					return false;
				}
				if ($db->sql_num_rows($acl_all_handle) === 1) {
					$acl_all_result = $db->sql_fetch_assoc($acl_all_handle);
					if ($acl_all_result['value'] == 1) {
						$this->acl_cache[$cur_group][$this->permission_list['all']['id']] = true;
						return true;
					} else {
						$this->acl_cache[$cur_group][$this->permission_list['all']['id']] = false;
					}
				} else {
					$this->acl_cache[$cur_group][$this->permission_list['all']['id']] = false;
					unset($acl_all_result);
					unset($acl_all_query);
					unset($acl_all_handle);
					unset($cur_group);
				}
			}
		}
		foreach ($group_array AS $cur_group) {
			// Check if permission exists
			if (!isset($this->permission_list[$acl_key])) {
				return false;
			}

			// Check cache
			if (isset($this->acl_cache[$cur_group][$this->permission_list[$acl_key]['id']])) {
				return $this->acl_cache[$cur_group][$this->permission_list[$acl_key]['id']];
			}

			// Check if user group has the requested property
			$acl_all_query = 'SELECT `value` FROM `' . ACL_TABLE . '`
				WHERE `acl_id` = '.$this->permission_list[$acl_key]['id'].'
				AND `group` = '.$cur_group;
			$acl_all_handle = $db->sql_query($acl_all_query);
			if ($db->error[$acl_all_handle] === 1) {
				return false;
			}
			if ($db->sql_num_rows($acl_all_handle) === 1) {
				$result = $db->sql_fetch_assoc($acl_all_handle);
				if ($result['value'] == 1) {
					$this->acl_cache[$cur_group][$this->permission_list[$acl_key]['id']] = true;
					return true;
				}
			}
			$this->acl_cache[$cur_group][$this->permission_list[$acl_key]['id']] = false;
			unset($cur_group);
			unset($acl_all_query);
			unset($acl_all_handle);
		}
		return false;
	}

	/**
	 * set_permission - Set permissions for a certain group
	 * @global db $db
	 * @global Debug $debug
	 * @param string $acl_key
	 * @param integer $value
	 * @param integer $group
	 * @return boolean Success
	 */
	public function set_permission($acl_key, $value, $group) {
		global $db;
		global $debug;

		$value = (int)$value;
		if (!array_key_exists($acl_key,$this->permission_list)) {
			$debug->addMessage('The key \''.$acl_key.'\' does not exist.',true);
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
			$debug->addMessage('Set permission \''.$acl_key.'\' for group '.$group.' to '.$value,false);
		} else {
			$set_permission_query = 'INSERT INTO `' . ACL_TABLE . '`
				(`acl_id`,`group`,`value`)
				VALUES (\''.$this->permission_list[$acl_key]['id'].'\','.$group.','.$value.')';
		}
		$set_permission_handle = $db->sql_query($set_permission_query);
		if ($db->error[$set_permission_handle] === 1) {
			return false;
		}

		// Update cache
		$this->acl_cache[$group][$this->permission_list[$acl_key]['id']] = (bool)$value;

		// Make sure that you did not remove the permission necessary to change permissions
		if (!$this->check_permission('set_permissions')) {
			$debug->addMessage('Removed vital permission \''.$acl_key.'.\' Reverting.',true);
			$revert_permission_query = 'UPDATE `' . ACL_TABLE . '`
				SET `value` = 1
				WHERE `acl_record_id` = '.$check_if_exists['acl_record_id'];
			$revert_permission_handle = $db->sql_query($revert_permission_query);
			if ($db->error[$revert_permission_handle] === 1) {
				die('You no longer have the necessary permission to edit
					permissions. This is a fatal error. Please repair the
					database manually.');
			}
			echo 'You cannot remove the permission \''.$acl_key.'\' because doing
				so will prevent you from making further changes.<br />';
			// Update cache
			$this->acl_cache[$group][$this->permission_list[$acl_key]['id']] = true;
		}

		return true;
	}

	/**
	 * get_acl_key_names - Load the list of permissions from the database
	 * @global db $db Database connection object
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
	 * @global db $db Database connection object
	 * @global Debug $debug Debug object
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
			$debug->addMessage('$name is not a string',true);
			return false;
		}
		if (!is_string($longname)) {
			$debug->addMessage('$longname is not a string',true);
			return false;
		}
		if (!is_string($description)) {
			$debug->addMessage('$description is not a string',true);
			return false;
		}
		if (!is_int($default_value)) {
			$debug->addMessage('$default_value is not an integer',true);
			return false;
		}
		// Check if key already exists
		if (isset($this->permission_list[$name])) {
			$debug->addMessage('The ACL key '.$name.' already exists',true);
			return false;
		}
		
		// Add key
		$new_key_query = 'INSERT INTO '.ACL_KEYS_TABLE.'
			(acl_name,acl_longname,acl_description,acl_value_default)
			VALUES (\''.$name.'\',\''.addslashes($longname).'\',\''.addslashes($description).'\','.(int)$default_value.')';
		$new_key_handle = $db->sql_query($new_key_query);
		if ($db->error[$new_key_handle] === 1) {
			return false;
		}
		// Update permission list
		$this->permission_list = $this->get_acl_key_names();
		return true;
	}
}

?>
