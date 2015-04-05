<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Exceptions
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Exceptions;

/**
 * Exceptions caused by invalid user input
 */
class InvalidInputException extends \Exception
{
    public function __construct($message = null, $code = 0,
        \Exception $previous = null)
    {
        if ($message == null) {
            $message = "You provided invalid input.";
        }
        parent::__construct($message, $code, $previous);
    }
}
