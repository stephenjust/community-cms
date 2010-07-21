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
    /**
     * Stores an array of all executed queries
     */
    var $query = array();
	var $query_text = array();
    var $error = array();

	function print_query_stats() {
		return '<p>Number of queries: '.($this->query_count - 1).'</p>';
	}

	function print_queries() {
		$queries = $this->query_text;
		$return = NULL;
		foreach($queries AS $querynum => $query) {
			if ($this->error[$querynum] === 1) {
				$return .= '<p><span style="color: #CC0000;">'.$query.'</span></p>'."\n";
			} else {
				$return .= '<p>'.$query.'</p>'."\n";
			}
		}
		return $return;
	}
}

require(ROOT.'includes/db/db_'.$CONFIG['db_engine'].'.php');
$db_class = 'db_'.$CONFIG['db_engine'];
$db = new $db_class;
?>
