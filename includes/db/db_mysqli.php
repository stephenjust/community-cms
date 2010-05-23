<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.database
 */
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Data-Base Abstraction Layer class for MySQLi
 * @package CommunityCMS.database
 */
class db_mysqli extends db {
    function __construct() {
        $this->dbms = 'mysqli';
    }
    function sql_connect() {
		global $CONFIG;
		$this->db_connect_id = 'mysqli';
		$this->connect = mysqli_connect($CONFIG['db_host'],$CONFIG['db_user'],
			$CONFIG['db_pass'],$CONFIG['db_name'],$CONFIG['db_host_port']);
		return $this->connect;
    }
	function sql_server_info() {
		return mysqli_get_server_info($this->connect).' (mysqli)';
	}
	function sql_query($query) {
		if ($this->connect === (false || true)) {
			$this->error[$this->query_count] = 1;
		}
		$this->query[$this->query_count] = mysqli_query($this->connect,$query);
		if (!$this->query[$this->query_count]) {
			$this->error[$this->query_count] = 1;
		} else {
			$this->error[$this->query_count] = 0;
		}
		$this->query_text[$this->query_count] = $query;
		return $this->query_count++;
	}
	function sql_num_rows($query) {
		return mysqli_num_rows($this->query[$query]);
	}
	function sql_affected_rows($query) {
		return mysqli_affected_rows($this->connect);
	}
	function sql_fetch_assoc($query) {
		return mysqli_fetch_assoc($this->query[$query]);
	}
	function sql_fetch_row($query) {
		return mysqli_fetch_row($this->query[$query]);
	}
	function sql_escape_string($string) {
		return mysqli_real_escape_string($this->connect,$string);
	}
	function sql_insert_id($query) {
		return mysqli_insert_id($this->connect);
	}

	function sql_prepare($name,$query) {
		global $debug;
		// Validate parameters
		if (!is_string($name)) {
			$debug->add_trace('$name is not a string',true,'sql_prepare (mysqli)');
			return false;
		}
		if (!is_string($query)) {
			$debug->add_trace('$query is not a string',true,'sql_prepare (mysqli)');
			return false;
		}
		$prepare_query = 'PREPARE `'.$name.'` FROM "'.$query.'"';
		$prepare_handle = $this->sql_query($prepare_query);
		if (!$this->error[$prepare_handle] === 1) {
			$debug->add_trace('Failed to create prepared statement',true,'sql_prepare (pgsql)');
			return false;
		}
		return true;
	}
	function sql_prepare_exec($name,$variables,$datatypes) {
		global $debug;
		// Validate parameters
		if (!is_string($name)) {
			$debug->add_trace('$name is not a string',true,'sql_prepare_exec (mysqli)');
			return false;
		}
		if (!is_array($variables)) {
			$debug->add_trace('$variables is not an array',true,'sql_prepare_exec (mysqli)');
			return false;
		}
		if (!is_array($datatypes)) {
			$debug->add_trace('$datatypes is not an array',true,'sql_prepare_exec (mysqli)');
			return false;
		}
		if (count($variables) !== count($datatypes)) {
			$debug->add_trace('Length of $variables and $datatypes are not equal',true,'sql_prepare_exec (mysqli)');
			return false;
		}
		// Add quotation marks to strings
		foreach ($variables as $var) {
			if (!is_numeric($var)) {
				$var = '"'.$var.'"';
			}
		}
		unset($var);
		$variables_string = implode(', ',$variables);
		$exec_query = 'EXECUTE `'.$name.'` USING "'.$variables_string.'"';
		$exec_handle = $this->sql_query($exec_query);
		if ($this->error[$exec_handle] === 1) {
			$debug->add_trace('Failed to execute prepared statement',true,'sql_prepare_exec (mysqli)');
			return false;
		}
		return true;
	}
	function sql_prepare_close($name) {
		global $debug;
		if (!is_string($name)) {
			$debug->add_trace('$name is not a string',true,'sql_prepare_close (mysqli)');
			return false;
		}
		$prepare_close_query = 'DEALLOCATE PREPARE `'.$name.'`';
		$prepare_close_handle = $this->sql_query($prepare_close_query);
		if ($this->error[$prepare_close_handle] === 1) {
			$debug->add_trace('Failed to deallocate prepared statement',true,'sql_prepare_close (mysqli)');
			return false;
		}
		return true;
	}

	/**
	 * sql_close (mysqli) - Close the MySQLi connection
	 */
    function sql_close() {
        mysqli_close($this->connect);
    }
	function _print_error_query($query) {
		return mysqli_error($this->connect);
	}
}
?>
