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

	function print_error_query() {
		$return = '<br /><div>';
		for ($i = 1; $i <= count($this->error); $i++) {
			if($this->error[$i] == 1) {
				$return .= '<span style="color: #CC0000; font-size: small;">[Query '.$i.'] '.
					$this->query_text[$i].'<br />'.$this->_print_error_query($i).'</span><br />'."\n";
			}
		}
		$return .= '</div>'."\n";
		return $return;
	}

	function print_query_stats() {
		$return = '<br /><div>';
		$return .= 'Number of queries: '.($this->query_count - 1).'<br />';
		$return .= '</div>';
		echo $return;
	}

	function print_queries() {
		echo '<div><textarea cols="100" rows="10">';
		print_r($this->query_text);
		echo '</textarea></div><br />';
	}
}

require(ROOT.'includes/db/db_'.$CONFIG['db_engine'].'.php');
$db_class = 'db_'.$CONFIG['db_engine'];
$db = new $db_class;
?>
