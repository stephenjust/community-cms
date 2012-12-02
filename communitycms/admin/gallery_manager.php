<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;
global $debug;

if (!$acl->check_permission('adm_gallery_manager'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

// ----------------------------------------------------------------------------

function gallery_upload_box($gallery_id,$gallery_dir) {
	global $debug;

	if (!is_numeric($gallery_id)) {
		$debug->addMessage('Gallery ID not numeric',true);
		return false;
	}
	if (!file_exists(ROOT.$gallery_dir)) {
		return '<span style="font-weight: bold; color: #FF0000;">The gallery folder no longer exists.<br />
			Please delete this gallery.</span>';
	}

	$form = new form;
	$form->set_target('?module=gallery_manager&amp;action=edit&amp;id='.$gallery_id);
	$form->set_method('post');
	$form->add_file_upload('gallery_upload',$gallery_dir,true);
	$form->add_submit('refresh','Refresh Page');
	return $form;
}

// ----------------------------------------------------------------------------

function gallery_photo_manager($gallery_id) {
	global $debug;

	$gallery = new Gallery($gallery_id);
	if (!file_exists(ROOT.$gallery->getImageDir())) {
		$debug->addMessage('Gallery folder does not exist',true);
		return false;
	}
	if (!file_exists(ROOT.$gallery->getImageDir().'/thumbs')) {
		$debug->addMessage('Gallery thumbnail dir does not exist',true);
		return false;
	}

	$gallery_images = $gallery->getImages();;

	if (count($gallery_images) == 0) {
		return 'There are currently no images in this gallery.';
	}
	$image_manager = '<table border="0px">';
	$image_path = ROOT.$gallery->getImageDir().'/';
	$thumbs_path = $image_path.'thumbs/';
	for ($i = 0; $i < count($gallery_images); $i++) {
		$image_manager .= '<form method="post" action="?module=gallery_manager&amp;
			action=edit&amp;id='.$gallery->getID().'&amp;edit=desc">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />';
		$image_manager .= '<tr><td style="vertical-align: middle;"><a href="'.$image_path.$gallery_images[$i]['file'].'">
			<img src="'.$thumbs_path.$gallery_images[$i]['file'].'" border="0px" /></a></td>
			<td><textarea class="mceNoEditor mceSimple" name="desc">'.htmlentities($gallery_images[$i]['caption']).'</textarea></td>
			<td style="vertical-align: middle;"><input type="submit" value="Save Description" /><br /></form></td>
			<td style="vertical-align: middle;">
			<form method="post" action="?module=gallery_manager&amp;action=edit&amp;id='.$gallery->getID().'&amp;edit=del">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />
			<input type="submit" value="Remove Image" />
			</td></tr></form>';
	}
	$image_manager .= '</table>';
	return $image_manager;
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

// Check to make sure a gallery application is selected
if (get_config('gallery_app') == 'disabled' || get_config('gallery_app') == NULL)
	throw new AdminException('There is no image gallery application configured. Please copy '.
		'one of the supported image gallery applications to your server and use '.
		'the "Gallery Settings" page to configure it.');

// Process actions
switch ($_GET['action']) {
	case 'create':
		$title = $_POST['title'];
		$description = $_POST['description'];
		$image_dir = $_POST['image_dir'];
		try {
			$gallery = new Gallery(false,$title,$description,$image_dir);
			echo 'Successfully created gallery.<br />'."\n";
			$_GET['action'] = 'edit';
			$_POST['gallery'] = $gallery->getID();
			unset($gallery);
		}
		catch (GalleryException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
		}

	case 'edit':
		// Set gallery id for future use
		if (isset($_GET['id']) && !isset($_POST['gallery'])) {
			$_POST['gallery'] = $_GET['id'];
			unset($_GET['id']);
		}
		if (!isset($_POST['gallery'])) {
			echo '<span class="errormessage">No gallery selected.</span><br />'."\n";
			break;
		}
		if (!isset($_GET['edit']))
			$_GET['edit'] = NULL;

		$gallery_id = (int)$_POST['gallery'];
		unset($_POST['gallery']);

		try {
			// Get gallery information
			$gallery = new Gallery($gallery_id);
			
			// Save image caption
			if ($_GET['edit'] === 'desc') {
				if (!isset($_POST['desc'])
						|| !isset($_POST['file_name']))
					throw new GalleryException('Unable to set image caption.');
				$gallery->setImageCaption(
						$gallery->getImageID($_POST['file_name']),
						$_POST['desc'], $_POST['file_name']);
				echo 'Successfully edited image caption.<br />'."\n";
			} elseif ($_GET['edit'] === 'del') {
				if (!isset($_POST['file_name']))
					throw new GalleryException('Unable to delete image.');

				// Delete image caption if it exists
				$gallery->deleteImage($_POST['file_name']);
				echo 'Successfully deleted image.<br />'."\n";
			}
		}
		catch (GalleryException $e) {
			echo '<span class="errormessage">'.$e->getMessage()."</span><br />\n";
		}

		// Show gallery manager
		$gallery_reference = '$GALLERY_EMBED-'.$gallery->getID().'$';
		$tab_content['edit'] = '<span style="font-size: large; font-weight: bold;">'.$gallery->getTitle().'</span><br />'."\n";
		$tab_content['edit'] .= 'To add this gallery to your site, copy the following text into the place you would like the gallery to appear:<br />';
		$tab_content['edit'] .= '<input type="text" value="'.$gallery_reference.'" /><br />'."\n";
		$tab_content['edit'] .= gallery_photo_manager($gallery->getID());
		$tab_content['edit'] .= gallery_upload_box($gallery->getID(),$gallery->getImageDir());
		$tab_layout->add_tab('Edit Gallery',$tab_content['edit']);
		break;

	case 'delete':
		if (!isset($_GET['id'])) {
			echo 'No gallery selected.<br />'."\n";
			break;
		}
		try {
			$gallery = new Gallery($_GET['id']);
			$gallery->delete();
			unset($gallery);
			echo 'Successfully deleted gallery.<br />'."\n";
		}
		catch (GalleryException $e) {
			echo $e->getMessage();
		}
		break;

	default:
		break;
}

// ----------------------------------------------------------------------------

switch (get_config('gallery_app')) {
	default:
		echo 'Unknown gallery application selected. Plase reconfigure your gallery.';
		break;

// ----------------------------------------------------------------------------

	case 'simpleviewer':
		// Check if path is correct
		$gallery_dir = get_config('gallery_dir');
		$gallery_file = ROOT.$gallery_dir.'/web/simpleviewer.swf';
		if (!file_exists($gallery_file)) {
			echo 'Could not find SimpleViewer application file. Please
				check your configuration settings. The current configuration
				says to look here: '.$gallery_file;
			return true;
		}

		// Check if 'example' folder still exists
		$example_file = ROOT.$gallery_dir.'/examples/simpleviewer.swf';
		if (file_exists($example_file)) {
			$debug->addMessage('The SimpleViewer example folder still exists',false);
		}
		// Continue with same procedure as 'built-in'...

	case 'built-in':
		$gallery_list_query = 'SELECT * FROM `'.GALLERY_TABLE.'` ORDER BY `id` DESC';
		$gallery_list_handle = $db->sql_query($gallery_list_query);
		if ($db->error[$gallery_list_handle] === 1) {
			echo 'Failed to read galleries table.';
			return true;
		}
		if ($db->sql_num_rows($gallery_list_handle) == 0) {
			$tab_content['manage'] = 'No galleries currently exist.<br />';
		} else {
			// Start gallery list
			$tab_content['manage'] = '<table class="admintable"><tr>
				<th>Title</th><th>Image Directory</th><th colspan="2" width="1px"></th></tr>';

			// Populate table
			for ($i = 1; $i <= $db->sql_num_rows($gallery_list_handle); $i++) {
				$gallery_list = $db->sql_fetch_assoc($gallery_list_handle);
				$tab_content['manage'] .= '<tr>
					<td>'.$gallery_list['title'].'</td>
					<td>'.$gallery_list['image_dir'].'</td>
					<td><a href="?module=gallery_manager&amp;action=edit&amp;id='.$gallery_list['id'].'">Edit</a></td>
					<td><a href="?module=gallery_manager&amp;action=delete&amp;id='.$gallery_list['id'].'">Delete</a></td>';
			}

			$tab_content['manage'] .= '</table>';
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
		$create_form->add_textbox('image_dir','Directory Name');
		// TODO: Add gallery path field
		$create_form->add_submit('submit','Create Gallery');
		$tab_content['create'] .= $create_form;
		$tab_layout->add_tab('Create Gallery',$tab_content['create']);

		echo $tab_layout;
		break;
}

?>
