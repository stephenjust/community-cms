<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Exceptions;

/**
 * Class to manage key/value configuration pairs
 */
class SysConfig
{
    private static $instance = null;

    private $config_cache = array();

    /**
     * Get instance of class
     * @return SysConfig
     */
    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->populateCache();
    }

    /**
     * Get a configuration value
     * @param string $key
     * @return string
     */
    public function getValue($key)
    {
        if ($this->isCached($key)) {
            return $this->getCached($key);
        } else {
            return null;
        }
    }

    /**
     * Set a configuration value in the database
     * @param string $key
     * @param string $value
     * @throws \Exception
     */
    public function setValue($key, $value)
    {
        $query = "INSERT INTO ".CONFIG_TABLE." 
            (`config_name`, `config_value`)
            VALUES
            (:name, :value)
            ON DUPLICATE KEY UPDATE `config_value` = :value";
        try {
            DBConn::get()->query($query, [':name' => $key, ':value' => $value]);
            $this->updateCache($key, $value);
        } catch (DBException $ex) {
            throw new \Exception("Failed to update configuration value.", $ex);
        }
    }

    /**
     * Check whether a configuration value is currently cached
     * @param string $key
     * @return boolean
     */
    private function isCached($key)
    {
        return array_key_exists($key, $this->config_cache);
    }

    /**
     * Get a cached configuration value
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getCached($key)
    {
        if ($this->isCached($key)) {
            return $this->config_cache[$key];
        } else {
            throw new \InvalidArgumentException("Configuration item '$key' is not cached.");
        }
    }

    /**
     * Update the cached configuration value
     * @param string $key
     * @param string $value
     */
    private function updateCache($key, $value)
    {
        $this->config_cache[$key] = $value;
    }

    /**
     * Populate cache of configuration pairs
     * @throws \Exception
     */
    private function populateCache()
    {
        $query = 'SELECT `config_name`, `config_value` FROM `'.CONFIG_TABLE.'`';
        try {
            $results = DBConn::get()->query($query, null, DBConn::FETCH_ALL);
            foreach ($results as $result) {
                $this->updateCache($result['config_name'], $result['config_value']);
            }
        } catch (DBException $ex) {
            throw new \Exception("Failed to load configuration.", $ex);
        }
    }
}
