<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Class to handle calendar location records
 */
class CalLocation
{
    /**
     * Delete a location entry
     * @param integer $loc_id
     * @throws CalLocationException
     */
    public static function delete($loc_id) 
    {
        if (!is_numeric($loc_id)) {
            return; 
        }

        $query = "DELETE FROM `".LOCATION_TABLE."` "
            . "WHERE `id` = :id";
        try {
            DBConn::get()->query($query, [":id" => $loc_id]);
            Log::addMessage('Deleted location entry');
        } catch (Exceptions\DBException $ex) {
            throw new CalLocationException('Error deleting location: '.$ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * Get all location entries
     * @return array
     * @throws CalLocationException
     */
    public static function getAll() 
    {
        $query = "SELECT `id`, `value` "
            . "FROM `".LOCATION_TABLE."` "
            . "ORDER BY `value` ASC";
        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new CalLocationException('Error getting all location values: '.$ex->getMessage(), $ex->getCode(), $ex);
        }

        $locations = [];
        foreach ($results as $result) {
            $locations[] = $result;
        }

        return $locations;
    }

    /**
     * Save a new location entry if it does not already exist
     * @param string $location (unescaped)
     * @throws \Exception 
     */
    public static function save($location) 
    {
        // Check if saving is enabled
        if (SysConfig::get()->getValue('calendar_save_locations') != 1) {
            return; 
        }

        if (strlen($location) < 2) {
            return;
        }

        // Check if the given location is already in the database
        if (count(self::search($location, true)) > 0) {
            return;
        }

        // Create new location entry
        $query = "INSERT INTO `".LOCATION_TABLE."` (`value`) "
            . "VALUES (:value)";
        try {
            DBConn::get()->query($query, [":value" => HTML::schars($location)]);
            Log::addMessage("Created new location '$location'");
        } catch (Exceptions\DBException $ex) {
            throw new CalLocationException('An error occurred while attempting to save a new location entry.');
        }
    }

    /**
     * Search for a location entry
     * @param string $string
     * @param bool $exact_match
     * @return array
     * @throws CalLocationException
     */
    public static function search($string, $exact_match = false)
    {
        if ($exact_match) {
            $query = "SELECT * FROM `".LOCATION_TABLE."` "
                . "WHERE `value` = :value";
        } else {
            $query = "SELECT * FROM `".LOCATION_TABLE."` "
                . "WHERE `value` LIKE :value "
                . "LIMIT 10";
        }

        try {
            $results = DBConn::get()->query($query,
                [":value" => (($exact_match) ? $string : "$string%")], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new CalLocationException('Could not search for term: '.$ex->getMessage(), $ex->getCode(), $ex);
        }

        $locations = array();
        foreach ($results as $result) {
            $locations[] = $result;
        }

        return $locations;
    }
}

class CalLocationException extends \Exception
{
}
