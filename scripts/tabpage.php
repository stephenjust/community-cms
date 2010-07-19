<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

if (!isset($_GET['id'])) {
	die('Page not found.');
}
if (!is_numeric($_GET['id'])) {
	die('Invalid ID');
}
$page_id = (int)$_GET['id'];

define('ROOT','../');
define('SECURITY',1);

include(ROOT.'config.php');
include(ROOT.'include.php');

initialize();
$page_query = 'SELECT `page`.*, `pt`.`filename`
	FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
	WHERE `page`.`id` = '.$page_id.'
	AND `page`.`type` = `pt`.`id`
	LIMIT 1';
$page_handle = $db->sql_query($page_query);
$result = $db->sql_fetch_assoc($page_handle);
$page = new page;
$page->set_page($page_id);
echo $page->content;
clean_up();

?>