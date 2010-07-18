<?php
/**
 * Community CMS
 * $Id$
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

if (!page_has_children($page->id)) {
	return false;
}

return $content;
?>