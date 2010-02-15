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
global $debug;

// ----------------------------------------------------------------------------

// Check to make sure a gallery application is selected
if (get_config('gallery_app') == 'disabled' || get_config('gallery_app') == NULL) {
	$content .= 'There is no image gallery application configured. Please copy
		one of the supported image gallery applications to your server and use
		the "Gallery Settings" page to configure it.';
	return true;
}

switch (get_config('gallery_app')) {
	default:
		$content .= 'Unknown gallery application selected. Plase reconfigure your gallery.';
		break;

// ----------------------------------------------------------------------------

	case 'simpleviewer':
		// Check if path is correct
		$gallery_dir = get_config('gallery_dir');
		$gallery_file = ROOT.$gallery_dir.'/web/simpleviewer.swf';
		if (!file_exists($gallery_file)) {
			$content .= 'Could not find SimpleViewer application file. Please
				check your configuration settings. The current configuration
				says to look here: '.$gallery_file;
			return true;
		}

		// Check if 'example' folder still exists
		$example_file = ROOT.$gallery_dir.'/examples/simpleviewer.swf';
		if (file_exists($example_file)) {
			$debug->add_trace('The SimpleViewer example folder still exists',false,'gallery_manager.php');
		}

		$tab_layout = new tabs;

		// Start gallery list
		$tab_content['manage'] = '';

		$tab_layout->add_tab('Manage Galleries',$tab_content['manage']);

// ----------------------------------------------------------------------------

		// Create a gallery
		$tab_content['create'] = '';
		
		$tab_layout->add_tab('Create Gallery',$tab_content['create']);

		$content .= $tab_layout;
		break;
}

?>
