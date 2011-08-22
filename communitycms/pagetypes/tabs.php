<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die('You cannot access this page directly.');
}
global $db;
global $page;
$content = NULL;

$subpage_query = 'SELECT `id`,`title` FROM `'.PAGE_TABLE.'`
	WHERE `parent` = '.$page->id.' ORDER BY `list` ASC';
$subpage_handle = $db->sql_query($subpage_query);

// SQL error = not found
if ($db->error[$subpage_handle] === 1) {
	Page::$exists = false;
	return true;
}

// No subpages = not found
$num_subpages = $db->sql_num_rows($subpage_handle);
if ($num_subpages == 0) {
	Page::$exists = false;
	return true;
}

$content .= '<div id="fe_tabs">'."\n";
$content .= "\t<ul>\n";
$divs = NULL;
for ($i = 1; $i <= $num_subpages; $i++) {
	$subpage = $db->sql_fetch_assoc($subpage_handle);
	$content .= "\t\t<li>\n";
	$content .= "\t\t\t".'<a href="./scripts/tabpage.php?id='.$subpage['id'].'" title="'.stripslashes($subpage['title']).'">'.stripslashes($subpage['title']).'</a>'."\n";
	$content .= "\t\t</li>\n";
	$tab_id = str_replace(' ','_',stripslashes($subpage['title']));
	$divs .= "\t\t<div id=\"#".$tab_id.'"></div>'."\n";
}
$content .= "\t</ul>\n";
$content .= "</div>\n";

return $content;
?>