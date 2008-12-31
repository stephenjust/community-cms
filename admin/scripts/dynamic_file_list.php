<?php
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	define('SECURITY',1);
	define('ROOT','../../');
	include(ROOT.'include.php');
	$referer = $_SERVER['HTTP_REFERER'];
	if(ereg('/$',$referer)) {
		$referer .= 'index';
		}
	$referer_directory = dirname($referer);
	if($referer_directory == "") {
		die('Security breach.');
		}
	$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	if($current_directory == $referer_directory.'/admin/scripts') {
		echo dynamic_file_list($_GET['newfolder']);
		} else {
		die('Security breach.');
		}
?>