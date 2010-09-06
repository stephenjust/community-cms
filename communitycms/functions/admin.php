<?php
/**
 * Community CMS
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
 * @return string Menu HTML
 */
function admin_nav() {
	global $acl;

	// Read menu XML file
	$xmlreader = new XMLReader;
	$xmlreader->open(ROOT.'admin/page_list.xml');
	$result = NULL;
	while($xmlreader->read()) {
		if ($xmlreader->nodeType == XMLREADER::DOC_TYPE ||
				$xmlreader->nodeType == XMLREADER::COMMENT ||
				$xmlreader->nodeType == XMLREADER::XML_DECLARATION) {
			continue;
		}
		if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
			$cat_name = $xmlreader->getAttribute('name');
			$cat_label = '<h3>'.$cat_name."</h3>\n";
			$item_count = 0;
			$cat_items = NULL;
		}
		if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {
			if ($item_count != 0) {
				$result .= "<div>\n".$cat_label."<div>\n".$cat_items."</div>\n</div>\n";
			}
		}

		if ($xmlreader->name == 'link' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
			if ($xmlreader->getAttribute('hide') != '1') {
				$acl_value = $xmlreader->getAttribute('acl');
				if ($acl_value != '') {
					// Don't show link if you don't have permission to use it
					if (!$acl->check_permission($acl_value)) {
						continue;
					}
				}
				$label = $xmlreader->getAttribute('label');
				$url = $xmlreader->getAttribute('url');
				$module = $xmlreader->getAttribute('module');
				$target = $xmlreader->getAttribute('target');
				if ($target != '') {
					$target = ' target="'.$target.'"';
				}
				if ($url == '' && $module != '') {
					$path = 'admin.php?module='.$module;
				} else {
					$path = $url;
				}
				$cat_items .= '<a href="'.$path.'"'.$target.'>'.$label."</a><br />\n";
				$item_count++;
			}
		}
		if ($xmlreader->name == 'link' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {

		}
	}
	$xmlreader->close();
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
		$debug->add_trace('Column list must be an array',true);
		return NULL;
	}
	if (!is_array($values)) {
		$debug->add_trace('Values must be stored in an array',true);
		return NULL;
	}
	for ($i = 0; $i < count($values); $i++) {
		if (!is_array($values[$i])) {
			$debug->add_trace('List of values is not a 2D array',true);
		}
		if (count($values[$i]) != count($columns)) {
			$debug->add_trace('Number of values and mumbe of columns are not equal',true);
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

	// Handle no content gracefully
	if (count($values) == 0) {
		$return .= "\t<tr>\n";
		$return .= "\t\t<td colspan=\"".count($columns)."\">No data found.</td>";
		$return .= "\t</tr>\n";
	}

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