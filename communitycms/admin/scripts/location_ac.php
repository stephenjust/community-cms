<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

header("Content-type: text/plain");

if (!isset($_GET['term'])) {
	exit;
}
/**#@+
 * @ignore
 */
define('ROOT','../../');
define('SECURITY',1);
/**#@-*/

include('../../config.php');
include('../../include.php');

initialize('ajax');

// This won't work with pgSQL, so quit here
if ($db->dbms != 'mysqli') {
	exit;
}

$query = addslashes($_GET['term']);
$sql_query = 'SELECT * FROM `'.LOCATION_TABLE.'` WHERE `value` LIKE \''.$query.'%\' LIMIT 10';
$sql_handle = $db->sql_query($sql_query);
if ($db->error[$sql_handle] === 1) {
	exit;
}
if ($db->sql_num_rows($sql_handle) == 0) {
	exit;
}
$suggestions = array();
for ($i = 1; $i <= $db->sql_num_rows($sql_handle); $i++) {
	$result_set = $db->sql_fetch_assoc($sql_handle);
	$suggestions[] = $result_set['value'];
}
$json_result = json_encode($suggestions);
echo $json_result;
clean_up();
?>