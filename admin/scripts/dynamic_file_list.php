<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
/**#@+
 * @ignore
 */
define('SECURITY', 1);
define('ROOT', '../../');
/**#@-*/
require_once ROOT.'vendor/autoload.php';
require ROOT.'include.php';
initialize('ajax');
$referer = $_SERVER['HTTP_REFERER'];
if(preg_match('#/$#', $referer)) {
    $referer .= 'index';
}
$referer_directory = dirname($referer);
if($referer_directory == "") {
    die('Security breach 1.');
}
$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

$referer_dir_root = str_replace('/scripts/tiny_mce/plugins/comcmslink', null, $referer_directory);

if($current_directory == $referer_dir_root.'/admin/scripts') {
    if ($referer_dir_root != $referer_directory) {
        echo dynamic_file_list($_GET['newfolder']);
    } else {
        echo dynamic_file_list($_GET['newfolder']);
    }
} else {
    die('Security breach 2.');
}
?>