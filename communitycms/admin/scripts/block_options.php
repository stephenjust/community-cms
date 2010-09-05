<?php
/**
 * Community CMS
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

/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../../');
/**#@-*/
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

$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
if($current_directory == $referer_directory.'/admin/scripts') {
	$file_path = ROOT.'includes/blocks.xml';
	$xmlreader = new XMLReader;
	$xmlreader->open($file_path);
	$correct_block = false;
	$options_list = false;
	$attribute_count = 0;
	$attribute_list = array();
	while ($xmlreader->read()) {
		if ($xmlreader->name == 'block' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
			if ($xmlreader->getAttribute('name') == $_GET['blocktype']) {
				$correct_block = true;
			}
		}
		if ($xmlreader->name == 'block' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {
			$correct_block = false;
		}

		if ($xmlreader->name == 'attribute' && $xmlreader->nodeType == XMLREADER::ELEMENT && $correct_block == true) {
			echo $xmlreader->getAttribute('label')." \n";
			$attribute_list[] = $xmlreader->getAttribute('name');
			switch ($xmlreader->getAttribute('type')) {
				default:
					echo 'Not supported.<br />'."\n";
					break;
				case 'int':
					echo '<input type="text" maxlength="9" size="3" name="'
						.$xmlreader->getAttribute('name').'" /><br />';
					break;
				case 'option':
					echo '<select name="'.$xmlreader->getAttribute('name').'">'."\n";
					$options_list = true;
					break;
			}
			$attribute_count++;
		}

		if ($xmlreader->name == 'value'
				&& $xmlreader->nodeType == XMLREADER::ELEMENT
				&& $options_list == true
				&& $correct_block == true) {
			echo '<option value="'.$xmlreader->readString().'">'.$xmlreader->readString().'</option>';
		}

		if ($xmlreader->name == 'values'
				&& $xmlreader->nodeType == XMLREADER::END_ELEMENT
				&& $options_list == true
				&& $correct_block == true) {
			echo '</select><br />'."\n";
			$options_list = false;
		}
	}
	$xmlreader->close();
	$attribute_list = array2csv($attribute_list);
	if ($attribute_count == 0) {
		echo 'No options.<br />';
	}
	echo '<input type="hidden" name="attributes" value="'.$attribute_list.'" />';
} else {
	die('Security breach.');
}
?>