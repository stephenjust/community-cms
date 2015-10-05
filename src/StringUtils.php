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

    /**
     * Format North American phone numbers
     * @param long $phone_number Phone number with no punctuation
     * @return string Phone number to display
     */
    public static function formatTelephoneNumber($phone_number)
    {
        if (!is_numeric($phone_number)) {
            return $phone_number;
        }

        $format = SysConfig::get()->getValue('tel_format');

        // Strip country code
        if (strlen($phone_number) == 11) {
            $phone_number = preg_replace('/^1/', '', $phone_number);
        }

        if (strlen($phone_number) == 7) {
            return self::format7DigitTelephoneNumber($phone_number, $format);
        }

        if (strlen($phone_number) == 10) {
            return self::format10DigitTelephoneNumber($phone_number, $format);
        }

        // Unrecognized style of phone number
        return $phone_number;
    }

    /**
     * Format a telephone number without an area code
     * @param long $phone_number
     * @param string $format
     * @return string
     */
    private static function format7DigitTelephoneNumber($phone_number, $format)
    {
        switch ($format) {
            case '###.###.####':
                $separator = ".";
                break;
            default:
                $separator = "-";
                break;
        }
        return sprintf(
            "%s%s%s",
            substr($phone_number, 0, 3),
            $separator,
            substr($phone_number, 3, 4)
        );
    }

    /**
     * Format a telephone number with an area code
     * @param long $phone_number
     * @param string $format
     * @return string
     */
    private static function format10DigitTelephoneNumber($phone_number, $format)
    {
        switch ($format) {
            case '(###) ###-####':
                $format = "(%s) %s-%s";
                break;
            case '###-###-####':
                $format = "%s-%s-%s";
                break;
            case '###.###.####':
                $format = "%s.%s.%s";
                break;
            default:
                $format = "%s%s%s";
        }
        return sprintf(
            $format,
            substr($phone_number, 0, 3),
            substr($phone_number, 3, 3),
            substr($phone_number, 6, 4)
        );
    }

    /**
     * Convert possible time formats to H:i format
     * @param string $time Time in any format
     * @return string Time in H:i format
     */
    public static function parseTime($time)
    {
        $matches = array();
        if (preg_match('#^([0-1]?[0-9]):([0-5][0-9]) ?([ap]m?)$#i', $time, $matches)) {
            // Check for time in h:i a format
            $hour = $matches[1];
            $minute = $matches[2];
            if ((stristr($matches[3], "p") && $hour != 12) ||
                (stristr($matches[3], "a") && $hour == 12)) {
                $hour = ($hour + 12) % 24;
            }
            return sprintf("%02d:%02d", $hour, $minute);
        } elseif (preg_match('#^([0-2]?[0-9]):([0-5][0-9])$#i', $time, $matches)) {
            // Check for time in G:i format
            return sprintf("%02d:%02d", $matches[1], $matches[2]);
        } elseif (preg_match('#^([0-1]?[0-9]) ?([ap]m?)$#i', $time, $matches)) {
            // Check for time in g:i a format
            $hour = $matches[1];
            if ((stristr($matches[2], "p") && $hour != 12) ||
                (stristr($matches[2], "a") && $hour == 12)) {
                $hour = ($hour + 12) % 24;
            }
            return sprintf("%02d:%02d", $hour, 0);
        }
        return 0;
    }

    /**
     * Convert a date string in the form mm/dd/yyyy to a unix timestamp
     * @param string $date
     * @return int
     */
    public static function parseDate($date)
    {
        $matches = array();
        if (!preg_match('#^([0-1][0-9])/([0-3][0-9])/([1-2][0-9]{3})$#', $date, $matches)) {
            return 0;
        }

        return gmmktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
    }

    /**
    * Remove HTML comment tags from a string
    * @param string $text Input string(s)
    * @return string Output, without comments
    */
   public static function removeComments($text)
   {
       return preg_replace('/<!--.+-->/', null, $text);
   }
}
