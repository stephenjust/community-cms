<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * admin_nav - Generate the navigation bar for administration pages
 * @global object $acl Access Control List class
 * @global object $db Database class
 * @return string Menu HTML
 */
function admin_nav() {
	global $db;
	global $acl;
	$pl_file = ROOT.'admin/menu.info';
	$pl_handle = fopen($pl_file,'r');
	$page_list = fread($pl_handle,filesize($pl_file));
	fclose($pl_handle);
	$page_list = str_replace("\t",NULL,$page_list); // Remove tabs
	$admin_pages = explode("\n",$page_list);
	unset($page_list);
	$last_heading = 'Main';
	$result = NULL;
	$list_index = 0;
	$page_index = 0;
	for ($i = 0; $i < count($admin_pages); $i++) {
		if (strlen($admin_pages[$i]) > 3 && !preg_match('#\/\/#',$admin_pages[$i])) { // 1
			$admin_menu_item[$i] = explode('#',$admin_pages[$i]);
			if (isset($admin_menu_item[$i][4]) && $acl->check_permission($admin_menu_item[$i][4])) { // 2
				if ($admin_menu_item[$i][0] != $last_heading
					&& $admin_menu_item[$i][1] == 1) { // 3
					$result .= '</div></div>
						<div><h3><a href="#">'.stripslashes($admin_menu_item[$i][0]).'</a></h3>
						<div>';
					$last_heading = $admin_menu_item[$i][0];
					$list_index++;
				} // 3
				if ($admin_menu_item[$i][1] == 1) { // 4
					if ($_GET['module'] == $admin_menu_item[$i][3]) {
						$page_index = $list_index;
					}
					$result .= '<a href="admin.php?module='
						.$admin_menu_item[$i][3].'">'.$admin_menu_item[$i][2].'</a><br />';
				} // 4
			} // 2
		}
	} // FOR
	$result .= '</div></div></div>';
	$result .= '<script type="text/javascript">
		$(function() {
		$("#menu").accordion({ header: "h3" }).accordion( "activate" , '.$page_index.' );
		});
		</script>';
	return $result;
}

/**
 * log_action - Add a message to the administration log
 * @global object $db Database class
 * @param string $message Message to add to database (not escaped)
 * @return string Error message, if any
 */
function log_action($message) {
	global $db;
	$message_error = NULL;
	$date = date('Y-m-d H:i:s');
	$user = $_SESSION['userid'];
	$ip_octet = '0';
	if ( isset($_SERVER["REMOTE_ADDR"]) )    {
		$ip_octet = $_SERVER["REMOTE_ADDR"];
	} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
		$ip_octet = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
		$ip_octet = $_SERVER["HTTP_CLIENT_IP"];
	}
	$ip_int = ip2long($ip_octet);
	$log_query = 'INSERT INTO ' . LOG_TABLE . '
		(user_id,action,date,ip_addr)
		VALUES ('.$user.',\''.addslashes($message).'\',\''.$date.'\','.$ip_int.')';
	$log_handle = $db->sql_query($log_query);
	if ($db->error[$log_handle] === 1) {
		$message_error = $db->_print_error_query($log_handle);
	}
	return $message_error;
}

/**
 * create_table - Generate styled tables for the admin interface
 * @global object $debug Debug Object
 * @param array $columns Array of column headings
 * @param array $values 2D array of values [row][column]
 * @return string HTML for table or NULL
 */
function create_table($columns, $values) {
	global $debug;

	// Validate input
	if (!is_array($columns)) {
		$debug->add_trace('Column list must be an array',true,'create_table()');
		return NULL;
	}
	if (!is_array($values)) {
		$debug->add_trace('Values must be stored in an array',true,'create_table()');
		return NULL;
	}
	for ($i = 0; $i < count($values); $i++) {
		if (!is_array($values[$i])) {
			$debug->add_trace('List of values is not a 2D array',true,'create_table()');
		}
		if (count($values[$i]) != count($columns)) {
			$debug->add_trace('Number of values and mumbe of columns are not equal',true,'create_table()');
			print_r($columns);
			print_r($values);
			return NULL;
		}
	}

	// Generate table
	$return = "<table class=\"admintable\">\n";
	$return .= "\t<tr>\n";
	for ($i = 0; $i < count($columns); $i++) {
		$return .= "\t\t<th>{$columns[$i]}</th>\n";
	}
	$return .= "\t</tr>\n";

	for ($i = 0; $i < count($values); $i++) {
		$return .= "\t<tr>\n";
		for ($j = 0; $j < count($values[$i]); $j++) {
			$return .= "\t\t<td>{$values[$i][$j]}</td>\n";
		}
		$return .= "\t</tr>\n";
	}

	$return .= "</table>\n";
	return $return;
}
?>