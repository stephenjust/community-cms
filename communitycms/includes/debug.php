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
class debug {
	public $debug_stack = array();
	public $error_count = 0;

	/**
	 * add_trace - Use this to add entries to the debug error stack
	 * @param string $message Debug message
	 * @param boolean $error True if this is an error trace
	 * @param string $function_name Name of the function adding the trace
	 * @return boolean Success
	 */
	public function add_trace($message,$error,$function_name) {
		// Prevent infinite loops
		if (count($this->error_count) > 100) {
			return false;
		}
		// Check variables
		if (!is_bool($error)) {
			$this->add_trace('Malformed success indicator',true,'debug->add_trace');
			return false;
		}
		if (!is_string($message)) {
			$this->add_trace('Malformed debug message',true,'debug->add_trace');
			return false;
		}
		if (!is_string($function_name)) {
			$this->add_trace('Malformed function name',true,'debug->add_trace');
			return false;
		}
		// Add information to stack
		$this->debug_stack[] = array('message' => $message, 'function' => $function_name, 'error' => $error);
		if ($error === true) {
			$this->error_count++;
		}
		return true;
	}

	/**
	 * Return a list of traces formatted in HTML
	 * @return string List of traces
	 */
	public function display_traces() {
		$stack = NULL;
		if (count($this->debug_stack) === 0) {
			$stack .= "\t".'No errors have occured<br />'."\n";
		}
		for ($i = 0; $i < count($this->debug_stack); $i++) {
			if ($this->debug_stack[$i]['error'] == true) {
				$stack .= "\t".'<p><span style="color: #CC0000;">Error: \''.$this->debug_stack[$i]['message'].'\' reported by \''.$this->debug_stack[$i]['function'].'\'</span></p>'."\n";
			} else {
				$stack .= "\t<p>'".$this->debug_stack[$i]['message'].'\' reported by \''.$this->debug_stack[$i]['function'].'\'</p>'."\n";
			}
		}
		return $stack;
	}
}
?>
