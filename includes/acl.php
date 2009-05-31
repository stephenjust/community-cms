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
	private $permission_list = array();

	/**
	 * check_permission - Read from the ACL and check if user is allowed to complete action
	 * @param string $acl_key Name of property in Access Control List
	 * @param int $usr User to check (current user if not set)
	 * @param array $groups Groups to check (current groups if not set)
	 * @global object $db Database connection object
	 * @return bool True if allowed to complete action, false if not.
	 */
	public function check_permission($acl_key, $usr = 0, $groups = NULL) {
		global $db;
		$user = 0;
		if ($groups == NULL) {
			$user = ($usr == 0 && isset($_SESSION['userid'])) ? $_SESSION['userid'] : $usr;
			if ($user != 0) {
				// Check if user has the dangerous 'all' property
				$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
					WHERE `acl_key` = \'all\'
					AND `user` = '.$user.'
					AND `is_group` = 0';
				$acl_all_handle = $db->sql_query($acl_all_query);
				if ($db->error[$acl_all_handle] === 1) {
					return false;
				} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
					return true;
				} else {
					unset($acl_all_query);
					unset($acl_all_handle);
				}
			}
		}
		// Check group properties
		$group_allow = false;
		if (!isset($_SESSION['groups'])) {
			$_SESSION['groups'] = array();
		}
		$group_list = ($groups == NULL) ? $_SESSION['groups'] : $groups;
		for ($i = 0; $i < count($group_list); $i++) {
			// Check if group has the 'all' property
			$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
				WHERE `acl_key` = \'all\' 
				AND `user` = '.$group_list[$i].'
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
				AND `user` = '.$group_list[$i].'
				AND `is_group` = 1';
			$acl_group_handle = $db->sql_query($acl_group_query);
			if ($db->error[$acl_group_handle] === 1) {
				return false;
			} elseif ($db->sql_num_rows($acl_group_handle) != 0) {
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
		// Check if user has the requested property
		$acl_all_query = 'SELECT `allow` FROM `' . ACL_TABLE . '`
			WHERE `acl_key` = \''.$acl_key.'\'
			AND `user` = '.$user.'
			AND `is_group` = 0';
		$acl_all_handle = $db->sql_query($acl_all_query);
		if ($db->error[$acl_all_handle] === 1) {
			return false;
		} elseif ($db->sql_num_rows($acl_all_handle) === 1) {
			$result = $db->sql_fetch_assoc($acl_all_handle);
			if ($result['allow'] == 1) {
				return true;
			}
		} elseif ($db->sql_num_rows($acl_all_handle) === 0 && $group_allow == 1) {
			return true;
		} else {
			unset($acl_all_query);
			unset($acl_all_handle);
		}
		return $group_allow;
	}
	
	public function set_permission($acl_key, $allow, $usr = 0, $group = 0) {
		global $db;
		$allow = (int)$allow;
		if (!$this->check_permission('set_permissions')) {
			return false;
		}
		if ($group != 0) {
			$user = $group;
			$is_group = 1;
		} elseif ($usr != 0) {
			$user = $usr;
			$is_group = 0;
		} else {
			$user = $_SESSION['userid'];
			$is_group = 0;
		}
		$check_if_exists_query = 'SELECT id,allow FROM `' . ACL_TABLE . '`
			WHERE `acl_key` = \''.$acl_key.'\'
			AND `user` = '.$user.'
			AND `is_group` = '.$is_group;
		$check_if_exists_handle = $db->sql_query($check_if_exists_query);
		if ($db->error[$check_if_exists_handle] === 1) {
			return false;
		}
		if ($db->sql_num_rows($check_if_exists_handle) == 1) {
			$check_if_exists = $db->sql_fetch_assoc($check_if_exists_handle);
			$set_permission_query = 'UPDATE `' . ACL_TABLE . '`
				SET `allow` = '.$allow.'
				WHERE `id` = '.$check_if_exists['id'];
		} else {
			$set_permission_query = 'INSERT INTO `' . ACL_TABLE . '`
				(`acl_key`,`user`,`is_group`,`allow`)
				VALUES (\''.$acl_key.'\','.$user.','.$is_group.','.$allow.')';
		}
		$set_permission_handle = $db->sql_query($set_permission_query);
		if ($db->error[$set_permission_handle] === 1) {
			return false;
		}
		return true;
	}

	/**
	 * load_permission_list - Load the list of permissions from an XML file
	 */
	public function load_permission_list() {
		$xml = new xml;
		$xml->open_file('includes/acl.xml');
		$xml_structure = $xml->parse();
		$xml_values = $xml_structure['values'];
		$xml_index = $xml_structure['index'];
		unset($xml_structure);
		$propcount = 0;
		$proplist = array();
		foreach ($xml_values as $tag_num => $tag) {
			if ($tag['tag'] == 'CATEGORY' && $tag['type'] == 'open') {
				$last_cat = $tag['attributes']['NAME'];
			} elseif ($tag['tag'] == 'ITEM') {
				$proplist[$propcount]['name'] = $tag['attributes']['NAME'];
				$proplist[$propcount]['category'] = $last_cat;
				$proplist[$propcount]['label'] = (isset($tag['attributes']['LABEL']))
					? $tag['attributes']['LABEL'] : NULL;
				$propcount++;
			}
		}
		unset($tag_num);
		unset($tag);
		unset($xml);
		$this->permission_list = $proplist;
	}
}

?>
