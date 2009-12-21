<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0

/**
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../../');
include(ROOT.'config.php');
include(ROOT.'include.php');
$referer = $_SERVER['HTTP_REFERER'];
if(preg_match('#/$#',$referer)) {
	$referer .= 'index';
}
$referer_directory = dirname($referer);
if($referer_directory == "") {
	die('Security breach.');
}
/*
Block File Format:

blockname#parameter('Parameter Name')=type{comma,separated,values}\n
block2#id('Something ID')=int\n
block3#

*/

$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
if($current_directory == $referer_directory.'/admin/scripts') {
	$file_path = ROOT.'content_blocks/blocks.info';
	$file_handle = fopen($file_path,'r');
	$file = fread($file_handle,filesize($file_path));
	fclose($file_handle);
	$block_entries = explode("\n",$file);
	$num_entries = count($block_entries);
	for ($i = 1; $i <= $num_entries; $i++) {
		$entry = explode('#',$block_entries[$i - 1]);
		if($entry[0] == $_GET['blocktype']) {
			$attributes = explode('&',$entry[1]);
			$num_attributes = count($attributes);
			$j = 1;
			if ($num_attributes == 0 || strlen($attributes[0]) < 1) {
				echo '<input type="hidden" name="attributes" value="" />';
				echo 'No options.';
				$num_attributes = 0;
				}
			$allattributes = NULL;
			for ($j = 1; $j <= $num_attributes; $j++) {
				$atb = explode('=',$attributes[$j - 1]);
				$temp = explode('(\'',$atb[0]);
				$attribute_name = $temp[0];
				$temp = substr($temp[1],0,-2);
				$attribute_description = $temp;
				unset($temp);
				echo $attribute_description.'=';
				if(preg_match('#\{.+\}#',$atb[1])) {
					$temp = explode('{',$atb[1]);
					$attribute_type = $temp[0];
					$atb[1] = $temp[0];
					$possible_responses = substr($temp[1],0,-1);
					}
				switch($atb[1]) { // $atb[1] = attribute type
					case 'int':
						echo '<input type="text" maxlength="9" size="3" name="'.$attribute_name.'" /><br />';
						break;
					case 'option':
						echo '<select name="'.$attribute_name.'">';
						$possible_responses = explode(',',$possible_responses);
						for($i = 1; $i <= count($possible_responses); $i++) {
							echo '<option value="'.$possible_responses[$i - 1].'">'.$possible_responses[$i - 1].'</option>';
							}
						echo '</select>';
						echo '<br />';
						break;
					default:
						echo 'Not supported.<br />';
						break;
					} // SWITCH
				$allattributes .= $attribute_name;
				if ($j != $num_attributes) {
					$allattributes .= ',';
					}
				} // FOR $j
			echo '<input type="hidden" name="attributes" value="'.$allattributes.'" />';
			} // IF $entry[0], $_GET['blocktype']
		} // FOR $i
	} else {
	die('Security breach.');
	}
?>