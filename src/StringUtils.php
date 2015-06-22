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

/**
 * Utility class to transform strings
 *
 * @author stephen
 */
class StringUtils
{
    /**
     * Truncate a string, followed by ellipses if necessary
     *
     * @param string $text Text to truncate
     * @param int $max_chars Maximum number of characters to allow
     * @return string Ellipsized string
     */
    public static function ellipsize($text, $max_chars)
    {
        $raw_text = html_entity_decode($text, ENT_QUOTES);
        if (strlen($raw_text) <= $max_chars) {
            return $text;
        }

        $truncated_string = substr($raw_text, 0, $max_chars);

        // Strip the final (possibly partial) word from the string
        $partial_string = substr($truncated_string, 0, strrpos($truncated_string, " "));

        return htmlentities($partial_string . "...", ENT_QUOTES);
    }
}
