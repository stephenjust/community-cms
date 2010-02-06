<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

function article_url_nopage($id) {
	if (!is_numeric($id)) {
		return '#';
	}

	return 'view.php?article_id='.$id;
}

function article_url_onpage($id) {
	// FIXME: Stub
}

function article_url_ownpage($id) {
	// FIXME: Stub
}
?>