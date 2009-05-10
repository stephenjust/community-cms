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
		return mysqli_get_server_info($this->connect);
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
	function sql_escape_string($string) {
		return mysqli_real_escape_string($this->connect,$string);
	}
    function sql_close() {
        // FIXME: Stub
    }
	function _print_error_query($query) {
		return mysqli_error($this->connect);
	}
}
?>
