<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}
$content = NULL;

// ----------------------------------------------------------------------------

// Check to make sure a gallery application is selected
if (get_config('gallery_app') == 'disabled' || get_config('gallery_app') == NULL) {
	$content .= 'There is no image gallery application configured. Please copy
		one of the supported image gallery applications to your server and use
		the "Gallery Settings" page to configure it.';
	return;
}

$content .= 'This page is still incomplete.';

?>
