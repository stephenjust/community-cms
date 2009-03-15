<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
	define('SECURITY',1);
	define('ROOT','../');
	// Load database configuration
	require_once('../config.php');
	// Try to establish a connection to the MySQL server.
	$connect = mysql_connect($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass']);
	if (!$connect) {
		die('Could not connect to the MySQL server.');
		}
	// Try to open the database that is used by Community CMS.
	$select_db = mysql_select_db($CONFIG['db_name'],$connect);
	if(!$select_db) {
		die('Unable to select mysql database.');
		}
	// Once the database connections are made, include all other necessary files.
	require_once('../include.php');
	if(!checkuser_admin()) {
		die('You do not have sufficient priveleges to perfom that action.');
		}
	$fieldname = $_GET['fieldname'];
	$content = $_GET['content'];
	$fieldname = explode('_',$fieldname);
	if($fieldname[0] == 'title') { // Need to do this to prevent stripping tags from content.
		$content = str_replace('"','&quot;',$content);
		$content = str_replace('<','&lt;',$content);
		$content = str_replace('>','&gt;',$content);
		}
$query = 'UPDATE '.$CONFIG['db_prefix'].'news SET '.$fieldname[0].' = \''.$content.'\' WHERE id = \''.$fieldname[1].'\'';
mysql_query($query,$connect);
echo mysql_error();
mysql_close();

echo stripslashes($content);
?> 