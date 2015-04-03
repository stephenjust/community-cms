<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

/**
 * Class to handle calendar location records
 */
class CalLocation
{
    /**
     * Delete a location entry
     * @global db $db
     * @param integer $loc_id
     * @throws CalLocationException
     */
    public static function delete($loc_id) 
    {
        global $db;
        
        if (!is_numeric($loc_id)) {
            return; 
        }
        
        $del_query = 'DELETE FROM `'.LOCATION_TABLE.'`
			WHERE `id` = '.(int)$_POST['loc_del'];
        $del_handle = $db->sql_query($del_query);
        if ($db->error[$del_handle] === 1) {
            throw new CalLocationException('Error deleting location.'); 
        }

        Log::addMessage('Deleted location entry');
    }
    
    /**
     * Get all location entries
     * @global db $db
     * @return array
     * @throws CalLocationException
     */
    public static function getAll() 
    {
        global $db;

        $query = 'SELECT `id`, `value`
			FROM `'.LOCATION_TABLE.'`
			ORDER BY `value` ASC';
        $handle = $db->sql_query($query);
        if ($db->error[$handle]) {
            throw new CalLocationException('Error getting all location values.'); 
        }
        
        $results = array();
        for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
            $row = $db->sql_fetch_assoc($handle);
            $results[] = $row;
        }
        
        return $results;
    }
    
    /**
     * Save a new location entry if it does not already exist
     * @global Debug $debug
     * @global db $db
     * @param string $location (unescaped)
     * @return void
     * @throws Exception 
     */
    public static function save($location) 
    {
        global $db;

        // Check if saving is enabled
        if (get_config('calendar_save_locations') != 1) {
            return; 
        }

        if (!isset($location) || strlen($location) < 2) {
            return;
        }
        $location = $db->sql_escape_string($location);

        // Check if the given location is already in the database
        $get_query = 'SELECT `value`
			FROM `'.LOCATION_TABLE.'`
			WHERE `value` = \''.$location.'\'';
        $get_handle = $db->sql_query($get_query);
        if ($db->error[$get_handle] === 1) {
            throw new CalLocationException('An error occurred when reading the list of saved locations.'); 
        }
        if ($db->sql_num_rows($get_handle) == 1) {
            return; 
        }

        // Create new location entry
        $new_query = 'INSERT INTO `'.LOCATION_TABLE.'`
			(`value`) VALUES (\''.$location.'\')';
        $new_loc_handle = $db->sql_query($new_query);
        if ($db->error[$new_loc_handle] === 1) {
            throw new CalLocationException('An error occurred while attempting to save a new location entry.'); 
        }

        Log::addMessage('Created new location \''.stripslashes($location).'\'.');
    }
    
    /**
     * Search for a location entry
     * (fails if not using MySQL backend)
     * @global db $db
     * @param string $string
     * @return array
     * @throws CalLocationException
     */
    public static function search($string) 
    {
        global $db;
        
        // This won't work with pgSQL, so quit here
        if ($db->dbms != 'mysqli') {
            return array(); 
        }
        
        $string = $db->sql_escape_string($string);
        $query = 'SELECT *
			FROM `'.LOCATION_TABLE.'`
			WHERE `value` LIKE \''.$string.'%\' LIMIT 10';
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new CalLocationException('Could not search for term.'); 
        }
        
        $return = array();
        for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
            $result = $db->sql_fetch_assoc($handle);
            $return[] = $result['value'];
        }
        
        return $return;
    }
}

class CalLocationException extends Exception
{
}
?>
