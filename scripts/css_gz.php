<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

// Validate parameters
if (!isset($_GET['css'])) {
	header('HTTP/1.1 403 Forbidden');
	die('Error 1');
}
if (!preg_match('/^[a-z0-9]+$/i',$_GET['css'])) {
	header('HTTP/1.1 403 Forbidden');
	die('Error 2');
}
if (!isset($_GET['template'])) {
	header('HTTP/1.1 403 Forbidden');
	die('Error 3');
}
if (!preg_match('/^[a-z0-9]+$/i',$_GET['template'])) {
	header('HTTP/1.1 403 Forbidden');
	die('Error 4');
}

$css_file = '../templates/'.$_GET['template'].'/'.$_GET['css'].'.css';

// Check if CSS file exists
if (!file_exists($css_file)) {
	header('HTTP/1.1 403 Forbidden');
	die('Error 5');
}

// Check file mtime
$mtime = filemtime($css_file);

if (extension_loaded('zlib')) {
	ob_start('ob_gzhandler');
}
header("Content-type: text/css");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT", $mtime);
$handle = fopen($css_file,'r');
echo fread($handle,filesize($css_file));
fclose($handle);

if (extension_loaded('zlib')) {
	ob_end_flush();
}
?>