<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
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

	public function display_traces() {
		$stack = '<div id="debug_info">'."\n";
		$stack .= "\t".'<h1>Debug Information:</h1>'."\n";
		if (count($this->debug_stack) === 0) {
			$stack .= "\t".'No errors have occured<br />'."\n";
		}
		for ($i = 0; $i < count($this->debug_stack); $i++) {
			if ($this->debug_stack[$i]['error'] == true) {
				$stack .= "\t".'<span style="color: #CC0000;">Error: \''.$this->debug_stack[$i]['message'].'\' reported by \''.$this->debug_stack[$i]['function'].'\'</span><br />'."\n";
			} else {
				$stack .= "\t'".$this->debug_stack[$i]['message'].'\' reported by \''.$this->debug_stack[$i]['function'].'\'<br />'."\n";
			}
		}
		$stack .= '</div>'."\n";
		print $stack;
	}
}
?>
