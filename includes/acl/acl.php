<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;
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
class acl
{
    /**#@+
    * @var array
    */
    public $permission_list;
    private $acl_cache = array();
    /**#@-*/
    private static $instance;

    /**
     * Get instance of acl class
     * @return acl
     */
    public static function get() 
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    function __construct() 
    {
        $this->permission_list = $this->loadKeys();
    }
    
    public function require_permission($acl_key) 
    {
        if (!$this->check_permission($acl_key)) {
            throw new AclException(sprintf("This action requires the '%s' permission.", HTML::schars($acl_key)));
        }
    }

    /**
     * check_permission - Read from the ACL and check if user is allowed to complete action
     * @param string  $acl_key     Name of property in Access Control List
     * @param int     $group       Group to check (current user's group if not set)
     * @param boolean $true_if_all Automatically return true if the group has 'All Permissions' set
     * @return boolean True if allowed to complete action, false if not.
     */
    public function check_permission($acl_key, $group = 0, $true_if_all = true) 
    {
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
            Debug::get()->addMessage('Permission \''.$acl_key.'\' does not exist', true);
        }

        if ($true_if_all == true) {
            foreach ($group_array AS $cur_group) {
                if ($this->query_group_permission("all", $cur_group)) {
                    return true;
                }
            }
        }
        foreach ($group_array AS $cur_group) {
            // Check if permission exists
            if (!isset($this->permission_list[$acl_key])) {
                return false;
            }

            if ($this->query_group_permission($acl_key, $cur_group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a group has a permission granted
     * @param string $permission_key
     * @param int $group
     * @return boolean
     */
    private function query_group_permission($permission_key, $group)
    {
        // Check for cached value
        if (isset($this->acl_cache[$group][$this->permission_list[$permission_key]['id']])) {
            return $this->acl_cache[$group][$this->permission_list[$permission_key]['id']];
        }

        // Look up in database
        $query = "SELECT `value` FROM `".ACL_TABLE."` "
            . "WHERE `acl_id` = :acl_id AND `group` = :group";
        try {
            $result = DBConn::get()->query($query,
                [":acl_id" => $this->permission_list[$permission_key]['id'], ":group" => $group],
                DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            return false;
        }
        if ($result && $result['value'] == 1) {
            $this->acl_cache[$group][$this->permission_list[$permission_key]['id']] = true;
            return true;
        } else {
            $this->acl_cache[$group][$this->permission_list[$permission_key]['id']] = false;
            return false;
        }
    }

    /**
     * set_permission - Set permissions for a certain group
     * @param string  $acl_key
     * @param integer $value
     * @param integer $group
     * @return boolean Success
     */
    public function set_permission($acl_key, $value, $group) 
    {
        $this->require_permission("set_permissions");
        if (!array_key_exists($acl_key, $this->permission_list)) {
            Debug::get()->addMessage("The key '$acl_key' does not exist.", true);
            return false;
        }

        $old_value = $this->check_permission($acl_key, $group, false);

        // Test that we can change this value safely by updating the cache
        $this->acl_cache[$group][$this->permission_list[$acl_key]['id']] = (bool)$value;
        if ($value == 0 && !$this->check_permission('set_permissions')) {
            Debug::get()->addMessage('Removed vital permission \''.$acl_key.'.\' Reverting.', true);
            $this->acl_cache[$group][$this->permission_list[$acl_key]['id']] = $old_value;
            echo "You cannot remove the permission '$acl_key' because doing so "
                . "will prevent you from making further changes.<br />";
            return false;
        }

        // Actually set the new permission value
        $query = "INSERT INTO `".ACL_TABLE."` "
            . "(`acl_id`,`group`,`value`) "
            . "VALUES "
            . "(:acl_id, :group, :value) "
            . "ON DUPLICATE KEY UPDATE `value` = :value";
        try {
            DBConn::get()->query($query, [":acl_id" => $this->permission_list[$acl_key]['id'], ":group" => $group, ":value" => $value], DBConn::NOTHING);
        } catch (Exceptions\DBException $ex) {
            $this->acl_cache[$group][$this->permission_list[$acl_key]['id']] = $old_value;
            return false;
        }

        return true;
    }

    /**
     * Load the list of permissions from the database
     * @return array An array of all existing permission keys
     */
    private function loadKeys()
    {
        $query = 'SELECT * FROM `'.ACL_KEYS_TABLE.'`';
        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            die('Could not load permission information from the database.');
        }
        $keys = [];
        foreach ($results as $key_info) {
            $keys[$key_info['acl_name']] = [
                'id' => $key_info['acl_id'],
                'longname' => $key_info['acl_longname'],
                'description' => $key_info['acl_description'],
                'default' => $key_info['acl_value_default'],
                'shortname' => $key_info['acl_name']
            ];
        }
        return $keys;
    }

    /**
     * Create or update an ACL key record
     * @param string $name          Name of key (lowercase)
     * @param string $longname      More descriptive name
     * @param string $description   Description of what the key allows
     * @param int    $default_value Allow by default? 1 = yes, 0 = no; default 0
     * @return boolean
     */
    public function createKey($name,$longname,$description,$default_value = 0)
    {
        // Validate parameters
        if (!is_string($name)) {
            Debug::get()->addMessage('$name is not a string', true);
            return false;
        }
        if (!is_string($longname)) {
            Debug::get()->addMessage('$longname is not a string', true);
            return false;
        }
        if (!is_string($description)) {
            Debug::get()->addMessage('$description is not a string', true);
            return false;
        }
        if (!is_int($default_value)) {
            Debug::get()->addMessage('$default_value is not an integer', true);
            return false;
        }

        // Add key
        $new_key_query = "INSERT INTO `".ACL_KEYS_TABLE."` "
            . "(`acl_name`, `acl_longname`, `acl_description`, `acl_value_default`) VALUES "
            . "(:name, :longname, :description, :default) "
            . "ON DUPLICATE KEY UPDATE "
            . "`acl_longname`=:longname, `acl_description`=:description, `acl_value_default`=:default";
        try {
            DBConn::get()->query($new_key_query,
                [":name" => $name, ":longname" => $longname, ":description" => $description, ":default" => $default_value],
                DBConn::NOTHING);
        } catch (Exceptions\DBException $ex) {
            return false;
        }
        // Update permission list
        $this->permission_list = $this->loadKeys();
        return true;
    }

    /**
     * Delete an ACL key
     * @param int $acl_id
     * @return boolean
     */
    public function deleteKey($acl_id)
    {
        $query = "DELETE FROM `".ACL_KEYS_TABLE."` WHERE `acl_id` = :id";
        try {
            DBConn::get()->query($query, [":id" => $acl_id]);
        } catch (Exceptions\DBException $ex) {
            return false;
        }
        $this->loadKeys();
        return true;
    }
}

class AclException extends \Exception
{
}
