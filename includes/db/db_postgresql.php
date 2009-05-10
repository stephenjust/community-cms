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
 * Data-Base Abstraction Layer class for PostgreSQL
 * @package CommunityCMS.database
 */
class db_postgresql extends db {
	function __construct() {
		$this->dbms = 'postgresql';
	}
	function sql_connect() {
		global $CONFIG;
		$this->db_connect_id = 'pgsql';
		$this->connect = pg_connect("host={$CONFIG['db_host']}
			port={$CONFIG['db_host_port']} dbname={$CONFIG['db_name']}
			user={$CONFIG['db_user']} password={$CONFIG['db_pass']}");
		return $this->connect;
	}
	function sql_server_info() {
		// FIXME: Stub
	}
	function sql_query($query) {
		$this->query[$this->query_count] = pg_query($this->connect,$query);
		if (!$this->query[$this->query_count]) {
			$this->error[$this->query_count] = 1;
			if (DEBUG == 1) {
				echo $query.'<br />';
			}
		} else {
			$this->error[$this->query_count] = 0;
		}
		$this->query_text[$this->query_count] = $query;
		return $this->query_count++;
	}
	function sql_num_rows($query) {
		return pg_num_rows($this->query[$query]);
	}
	function sql_fetch_assoc($query) {
		return pg_fetch_assoc($this->query[$query]);
	}
	function sql_escape_string($string) {
		return pg_escape_string($this->connect, $string);
	}
	function sql_close() {
		pg_close($this->connect);
	}
}
?>