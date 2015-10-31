<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

/**
 * Utility class to handle forms
 */
class FormUtil
{
    /**
     * Wrapper to retrieve variables from a GET request
     * @param string $field
     * @param int $filters
     * @param array $enum
     * @param mixed $default
     * @return mixed
     */
    public static function get($field, $filters = FILTER_DEFAULT, array $enum = null, $default = null)
    {
        $value = filter_input(INPUT_GET, $field, $filters);
        if ($value === null || ($enum !== null && array_search($value, $enum) === false)) {
            return $default;
        }
        return $value;
    }

    /**
     * Wrapper to retrieve variables from a POST request
     * @param string $field
     * @param int $filters
     * @param array $enum
     * @param mixed $default
     * @return mixed
     */
    public static function post($field, $filters = FILTER_DEFAULT, array $enum = null, $default = null)
    {
        $value = filter_input(INPUT_POST, $field, $filters);
        if ($value === null || ($enum !== null && array_search($value, $enum) === false)) {
            return $default;
        }
        return $value;
    }

    /**
     * Get POST data from an array
     * @param string $field
     * @param int $filters
     * @return array
     */
    public static function postArray($field, $filters = FILTER_DEFAULT)
    {
        $value = filter_input(INPUT_POST, $field, $filters, FILTER_REQUIRE_ARRAY);
        if ($value == null) {
            return [];
        } else {
            return $value;
        }
    }

    /**
     * Get checkbox input from a form
     * @param string $field
     * @return boolean
     */
    public static function postCheckbox($field)
    {
        $value = self::post($field);
        if ($value == 'on') {
            return true;
        } else {
            return false;
        }
    }
}
