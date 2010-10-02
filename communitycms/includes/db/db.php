<?php
/**
 * Community CMS
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
 * Primary Data-Base Abstraction Layer class
 * @package CommunityCMS.database
 */
class db {
    /**
     * Data-Base system to use
     */
    var $dbms = '';
    /**
     * Name of Data-Base connection
     */
    var $db_connect_id = '';
    /**
     * Database connection resource
     */
    var $connect = NULL;
    /**
     * Stores number of executed queries
     */
    var $query_count = 1;
    /**#@+
     * Stores an array of all executed queries
	 * @var array
     */
    var $query = array();
	var $query_text = array();
    var $error = array();
	var $errormsgs = array();
	/**#@-*/

	function print_query_stats() {
		return '<p>Number of queries: '.($this->query_count - 1).'</p>';
	}

	function print_queries() {
		$queries = $this->query_text;
		$return = NULL;
		foreach($queries AS $querynum => $query) {
			if ($this->error[$querynum] === 1) {
				$return .= '<p><span style="color: #CC0000;">'.$query.'</span></p>'."\n";
				if (isset($this->errormsgs[$querynum])) {
					$return .= '<p><em>'.$this->errormsgs[$querynum].'</em></p>'."\n";
				}
			} else {
				$return .= '<p>'.$query.'</p>'."\n";
			}
		}
		return $return;
	}

	/**#@+
	 * Stub functions defined by child classes
	 */
    function sql_connect() {
		return false;
    }
	function sql_server_info() {
		return false;
	}
	function sql_query($query) {
		return false;
	}
	function sql_num_rows($query) {
		return false;
	}
	function sql_affected_rows($query) {
		return false;
	}
	function sql_fetch_assoc($query) {
		return false;
	}
	function sql_fetch_row($query) {
		return false;
	}
	function sql_escape_string($string) {
		return false;
	}
	function sql_insert_id($table,$field) {
		return false;
	}
	function sql_prepare($name,$query) {
		return false;
	}
	function sql_prepare_exec($name,$variables,$datatypes) {
		return false;
	}
	function sql_prepare_close($name) {
		return false;
	}
    function sql_close() {
        return false;
    }
	/**#@-*/
}

require(ROOT.'includes/db/db_'.$CONFIG['db_engine'].'.php');
$db_class = 'db_'.$CONFIG['db_engine'];
$db = new $db_class;
?>
