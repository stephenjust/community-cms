<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

/**
 * Class to provide common trace functions throughout the CMS for debugging
 * where the cause of an issue may not be apparent
 *
 * @package CommunityCMS.main
 */
class Debug
{
    /**
     * Array of all messages given to the debugging tool
     * @var array
     */
    private static $message_list = array();
    /**
     * Number of errors that have been recorded
     * @var integer
     */
    private static $error_count = 0;
    private static $instance;
    
    /**
     * Get instance of debug class
     * @return Debug
     */
    public static function get() 
    {
        if (!Debug::$instance) { Debug::$instance = new Debug(); 
        }
        return Debug::$instance;
    }

    /**
     * add_trace - Use this to add entries to the debug error stack
     * @param string  $message Debug message
     * @param boolean $error   True if this is an error trace
     * @return boolean Success
     */
    public function addMessage($message,$error = false) 
    {
        // Prevent infinite loops of message adding
        if (count(Debug::$error_count) > 100) {
            return false;
        }
        // Check variables
        if (!is_bool($error)) {
            $this->addMessage('Malformed success indicator', true);
            return false;
        }
        if (!is_string($message)) {
            $this->addMessage('Malformed debug message', true);
            return false;
        }
        // Get calling function name
        $bt = debug_backtrace();
        if (!isset($bt[1])) {
            $function_name = $bt[0]['file'];
        } else {
            $function_name = $bt[1]['function'];
            if (isset($bt[1]['class'])) {
                $function_name = $bt[1]['class'].'->'.$function_name;
            }
        }
        // Add information to stack
        Debug::$message_list[] = array('message' => $message, 'function' => $function_name, 'error' => $error);
        if ($error === true) {
            Debug::$error_count++;
        }
        return true;
    }

    /**
     * Return a list of traces
     * @return string List of traces
     */
    public function getTraces()
    {
        $logs = [];
        foreach (Debug::$message_list as $debug) {
            $string = "{$debug['message']} ({$debug['function']})";
            if ($debug['error']) {
                $string = "ERROR: $string";
            }
            $logs[] = $string;
        }
        return $logs;
    }
}
