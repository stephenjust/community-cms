<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
$return = NULL;
global $db;
global $page;
$newsletter_query = 'SELECT * FROM ' . NEWSLETTER_TABLE . '
	WHERE page = '.$page->id.' ORDER BY year desc, month desc LIMIT 30 OFFSET 0';
$newsletter_handle = $db->sql_query($newsletter_query);
if($db->sql_num_rows($newsletter_handle) == 0) {
	$return .= "No newsletters to display";
} else {
	$newsletter = $db->sql_fetch_assoc($newsletter_handle);
	$currentyear = $newsletter['year'];
	$return .= "<div class='newsletter'><span class='newsletter_year'>".$currentyear."</span><br />\n";
	for ($i = 1; $db->sql_num_rows($newsletter_handle) >= $i; $i++) {
		if ($currentyear != $newsletter['year']) {
			$currentyear = $newsletter['year'];
			$return .= "<span class='newsletter_year'>".$currentyear."</span><br />\n";
		}
		if ($newsletter['hidden'] != 1) {
			$return .= '<a href="'.$newsletter['path'].'">'.$newsletter['label']."</a><br />\n";
		} else {
			$return .= $newsletter['label']."<br />\n";
		}
		if($i <= $db->sql_num_rows($newsletter_handle)) {
			$newsletter = $db->sql_fetch_assoc($newsletter_handle);
		}
	}
}
return $return."</div>";
?>