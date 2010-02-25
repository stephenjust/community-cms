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

// Process actions
switch ($_GET['action']) {
	case 'create':
		$title = addslashes($_POST['title']);
		$description = addslashes($_POST['description']);
		$create_query = 'INSERT INTO `'.GALLERY_TABLE.'` (`title`,`description`)
			VALUES (\''.$title.'\',\''.$description.'\')';
		$create_handle = $db->sql_query($create_query);
		if ($db->error[$create_handle] === 1) {
			$content .= 'Failed to create gallery.<br />'."\n";
		} else {
			$content .= 'Successfully created gallery.<br />'."\n";
			log_action('Created gallery \''.$title.'\'');
		}
		break;
	case 'change':
		if (!isset($_POST['gallery'])) {
			$content .= 'No gallery selected.<br />'."\n";
			break;
		}
		if (isset($_POST['edit'])) {
			$content .= 'Editing gallery...';
			// TODO: Finish edit gallery
		} elseif (isset($_POST['del'])) {
			$content .= 'Deleting gallery...';
			// TODO: Finish delete gallery
		}
		break;
	default:
		break;
}

// ----------------------------------------------------------------------------

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

		$gallery_list_query = 'SELECT * FROM `'.GALLERY_TABLE.'` ORDER BY `id` DESC';
		$gallery_list_handle = $db->sql_query($gallery_list_query);
		if ($db->error[$gallery_list_handle] === 1) {
			$content = 'Failed to read galleries table.';
			return true;
		}
		if ($db->sql_num_rows($gallery_list_handle) == 0) {
			$tab_content['manage'] = 'No galleries currently exist.<br />';
		} else {
			// Start gallery list
			$tab_content['manage'] = '<form method="post" action="?module=gallery_manager&amp;action=change">
				<table class="admintable"><tr>
				<th width="1px"></th><th>Title</th><th>Image Directory</th></tr>';

			// Populate table
			for ($i = 1; $i <= $db->sql_num_rows($gallery_list_handle); $i++) {
				$gallery_list = $db->sql_fetch_assoc($gallery_list_handle);
				$tab_content['manage'] .= '<tr>
					<td><input type="radio" name="gallery" value="'.$gallery_list['id'].'" /></td>
					<td>'.$gallery_list['title'].'</td>
					<td>'.$gallery_list['image_dir'].'</td></tr>';
			}

			$tab_content['manage'] .= '</table>';
			$tab_content['manage'] .= 'With selected:<br />'."\n";
			$tab_content['manage'] .= '<input type="submit" name="edit" value="Edit" />';
			$tab_content['manage'] .= '<input type="submit" name="del" value="Delete" /></form>';
		}

		$tab_layout->add_tab('Manage Galleries',$tab_content['manage']);

// ----------------------------------------------------------------------------

		// Create a gallery
		$tab_content['create'] = '';
		$create_form = new form;
		$create_form->set_method('post');
		$create_form->set_target('?module=gallery_manager&amp;action=create');
		$create_form->add_textbox('title','Title');
		$create_form->add_textarea('description','Description',NULL,'class="mceNoEditor"');
		// TODO: Add gallery path field
		$create_form->add_submit('submit','Create Gallery');
		$tab_content['create'] .= $create_form;
		$tab_layout->add_tab('Create Gallery',$tab_content['create']);

		$content .= $tab_layout;
		break;
}

?>
