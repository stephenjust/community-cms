<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Class to provide common trace functions throughout the CMS for debugging
 * where the cause of an issue may not be apparent
 *
 * @package CommunityCMS.main
 */
class Debug {
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

	/**
	 * add_trace - Use this to add entries to the debug error stack
	 * @param string $message Debug message
	 * @param boolean $error True if this is an error trace
	 * @return boolean Success
	 */
	public function add_trace($message,$error,$function_name = NULL) {
		// Prevent infinite loops
		if (count(Debug::$error_count) > 100) {
			return false;
		}
		// Check variables
		if (!is_bool($error)) {
			$this->add_trace('Malformed success indicator',true);
			return false;
		}
		if (!is_string($message)) {
			$this->add_trace('Malformed debug message',true);
			return false;
		}
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
	 * Return a list of traces formatted in HTML
	 * @return string List of traces
	 */
	public function display_traces() {
		$stack = NULL;
		if (count(Debug::$message_list) === 0) {
			$stack .= "\t".'No errors have occured<br />'."\n";
		}
		for ($i = 0; $i < count(Debug::$message_list); $i++) {
			if (Debug::$message_list[$i]['error'] == true) {
				$stack .= "\t".'<p><span style="color: #CC0000;">Error: \''.Debug::$message_list[$i]['message'].'\' reported by \''.Debug::$message_list[$i]['function'].'\'</span></p>'."\n";
			} else {
				$stack .= "\t<p>'".Debug::$message_list[$i]['message'].'\' reported by \''.Debug::$message_list[$i]['function'].'\'</p>'."\n";
			}
		}
		return $stack;
	}
}
?>
