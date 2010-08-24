<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @version SVN
 * @package CommunityCMS.install
 */

/**
 * @ignore
 */
define('ROOT','../');

require_once('./files/functions.php');

$template_path = './files/';
$template_file = $template_path."index.html";
$handle = fopen($template_file, "r");
$template = fread($handle, filesize($template_file));
fclose($handle);
$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
$image_path = $template_path.'images/';
if (!isset($_GET['page'])) {
	$_GET['page'] = '0';
}
switch ($_GET['page']) {
	default:
		include('./whatsnew.php');
		break;
	case 'license':
		include('./license.php');
		break;
	case '1':
		include('./install.php');
		break;
	case '2':
		include('./install2.php');
		break;
	case '3':
		include('./install3.php');
		break;
	case '4':
		include('./install4.php');
		break;
	case '5':
		include('./install5.php');
		break;
	case '6':
		include('./install6.php');
		break;
}
$template = str_replace('<!-- $PAGE_TITLE$ -->','Community CMS Installer',$template);
$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
// $template = str_replace('<!-- $UPDATE_CONTENT$ -->',include('./update.php'),$template);
$template = str_replace('<!-- $FOOTER$ -->','Community CMS Copyright&copy; 2007 - 2010 Stephen Just',$template);
echo $template;
?>