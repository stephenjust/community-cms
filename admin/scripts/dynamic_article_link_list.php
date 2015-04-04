<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */
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

$page = (isset($_GET['page'])) ? $_GET['page'] : 0;
if (!is_numeric($page)) {
    $page = 0;
}

if($current_directory == $referer_dir_root.'/admin/scripts') {
    echo dynamic_article_link_list($page);
} else {
    die('Security breach 2.');
}
?>