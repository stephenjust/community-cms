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
require_once('../include.php');
initialize();
// Once the database connections are made, include all other necessary files.
require_once('../include.php');
$user_ip = $_SERVER['REMOTE_ADDR'];
$question_id = stripslashes($_GET['question_id']);
$answer_id = stripslashes($_GET['answer_id']);
$referer = $_SERVER['HTTP_REFERER'];
if (ereg('/$',$referer)) {
	$referer .= 'index';
}
$referer_directory = dirname($referer);
$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
if ($current_directory == $referer_directory.'/scripts') {
	$query = 'INSERT INTO ' . POLL_RESPONSE_TABLE . '
		(question_id ,answer_id ,value ,ip_addr) VALUES
		('.$question_id.', '.$answer_id.', NULL, \''.ip2long($user_ip).'\');';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		echo('Failed to submit your vote.');
	} else {
		echo('Thank you for voting.');
	}
} else {
	die('Security breach.');
}
clean_up();
?> 