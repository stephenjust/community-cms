<?php
/**
 * Community CMS
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

if (!$acl->check_permission('adm_gallery_manager')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

// ----------------------------------------------------------------------------

/**
 * delete_gallery - Deletes a photo gallery
 * @global object $db
 * @global object $debug
 * @param integer $gallery
 * @return boolean
 */
function delete_gallery($gallery) {
	global $db;
	global $debug;

	if (!is_numeric($gallery)) {
		return false;
	}
	$id = (int)$gallery;
	unset($gallery);

	// Read article information for log
	$info_query = 'SELECT * FROM
		`' . GALLERY_TABLE . '` WHERE
		`id` = '.$id.' LIMIT 1';
	$info_handle = $db->sql_query($info_query);
	if ($db->error[$info_handle] === 1) {
		$debug->add_trace('Query failed',true,'delete_gallery');
		return false;
	}
	if ($db->sql_num_rows($info_handle) === 0) {
		$debug->add_trace('Article not found',true,'delete_gallery');
		return false;
	}
	$info = $db->sql_fetch_assoc($info_handle);

	// Delete article
	$delete_query = 'DELETE FROM `' . GALLERY_TABLE . '`
		WHERE `id` = '.$id;
	$delete = $db->sql_query($delete_query);
	if ($db->error[$delete] === 1) {
		return false;
	} else {
		log_action('Deleted photo gallery \''.stripslashes($info['title']).'\' ('.$info['id'].')');
	}

	unset($delete_query);
	unset($delete);
	unset($info_query);
	unset($info_handle);
	unset($info);
	return true;
}

// ----------------------------------------------------------------------------

function gallery_upload_box($gallery_id,$gallery_dir) {
	global $db;
	global $debug;

	if (!is_numeric($gallery_id)) {
		$debug->add_trace('Gallery ID not numeric',true,'gallery_upload_box()');
		return false;
	}
	if (!file_exists(ROOT.'files/'.$gallery_dir)) {
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

	$gallery_info = gallery_info($gallery_id);
	if (!file_exists(ROOT.'files/'.$gallery_info['image_dir'])) {
		$debug->add_trace('Gallery folder does not exist',true,'gallery_photo_manager()');
		return false;
	}
	if (!file_exists(ROOT.'files/'.$gallery_info['image_dir'].'/thumbs')) {
		$debug->add_trace('Gallery thumbnail dir does not exist',true,'gallery_photo_manager()');
		return false;
	}

	$gallery_images = gallery_images($gallery_info['image_dir']);

	if (count($gallery_images) == 0) {
		return 'There are currently no images in this gallery.';
	}
	$image_manager = '<table border="0px">';
	$image_path = ROOT.'files/'.$gallery_info['image_dir'].'/';
	$thumbs_path = $image_path.'thumbs/';
	for ($i = 0; $i < count($gallery_images); $i++) {
		$image_manager .= '<form method="post" action="?module=gallery_manager&amp;
			action=edit&amp;id='.$gallery_info['id'].'&amp;edit=desc">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />';
		$image_manager .= '<tr><td style="vertical-align: middle;"><a href="'.$image_path.$gallery_images[$i]['file'].'">
			<img src="'.$thumbs_path.$gallery_images[$i]['file'].'" border="0px" /></a></td>
			<td><textarea class="mceNoEditor mceSimple" name="desc">'.stripslashes($gallery_images[$i]['caption']).'</textarea></td>
			<td style="vertical-align: middle;"><input type="submit" value="Save Description" /><br /></form></td>
			<td style="vertical-align: middle;">
			<form method="post" action="?module=gallery_manager&amp;action=edit&amp;id='.$gallery_info['id'].'&amp;edit=del">
			<input type="hidden" name="file_id" value="'.$gallery_images[$i]['file_id'].'" />
			<input type="hidden" name="file_name" value="'.$gallery_images[$i]['file'].'" />
			<input type="submit" value="Remove Image" /></form>
			</td></tr>';
	}
	$image_manager .= '</table>';
	return $image_manager;
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

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
		$image_dir = replace_file_special_chars($_POST['image_dir']);
		$create_query = 'INSERT INTO `'.GALLERY_TABLE.'` (`title`,`description`,`image_dir`)
			VALUES (\''.$title.'\',\''.$description.'\',\''.$image_dir.'\')';
		$create_handle = $db->sql_query($create_query);
		if ($db->error[$create_handle] === 1) {
			$content .= 'Failed to create gallery.<br />'."\n";
			break;
		} else {
			if (!file_exists(ROOT.'files/'.$image_dir)) {
				mkdir(ROOT.'files/'.$image_dir);
			}
			if (!file_exists(ROOT.'files/'.$image_dir.'/thumbs')) {
				mkdir(ROOT.'files/'.$image_dir.'/thumbs');
			}
			$content .= 'Successfully created gallery.<br />'."\n";
			log_action('Created gallery \''.$title.'\'');
		}
		$gal_id_query = 'SELECT `id` FROM `'.GALLERY_TABLE.'`
			WHERE `title` = \''.$title.'\'';
		$gal_id_handle = $db->sql_query($gal_id_query);
		$gal_id = $db->sql_fetch_assoc($gal_id_handle);
		$_GET['id'] = $gal_id['id'];
		$_GET['action'] = 'edit';
	case 'edit':
		if (isset($_GET['id']) && !isset($_POST['gallery'])) {
			$_POST['gallery'] = $_GET['id'];
		}
		if (!isset($_POST['gallery'])) {
			$debug->add_trace('No gallery selected.',true,'gallery_manager.php');
			break;
		}
		$gallery_info = gallery_info($_POST['gallery']);

		// Edit description
		if (isset($_GET['edit'])) {
			if ($_GET['edit'] == 'desc' && isset($_POST['file_id']) && isset($_POST['file_name'])) {
				if ($_POST['file_id'] == '') {
					$description_query = 'INSERT INTO `'.GALLERY_IMAGE_TABLE.'`
						(`gallery_id`,`file`,`caption`) VALUES
						('.(int)$_GET['id'].',\''.$_POST['file_name'].'\',\''.addslashes($_POST['desc']).'\')';
				} else {
					$description_query = 'UPDATE `'.GALLERY_IMAGE_TABLE.'`
						SET `caption` = \''.addslashes($_POST['desc']).'\'
						WHERE `id` = '.(int)$_POST['file_id'];
				}
				$description_handle = $db->sql_query($description_query);
				if ($db->error[$description_handle] === 1) {
					$content .= 'Failed to edit image caption.';
				} else {
					$content .= 'Successfully edited image caption.';
					log_action('Changed image caption for \''.$_POST['file_name'].'\'');
				}
			} elseif ($_GET['edit'] == 'del' && isset($_POST['file_id']) && isset($_POST['file_name'])) {
				if ($_POST['file_id'] != '') {
					$description_query = 'DELETE FROM `'.GALLERY_IMAGE_TABLE.'`
						WHERE `id` = '.(int)$_POST['file_id'];
					$description_handle = $db->sql_query($description_query);
					if ($db->error[$description_handle] === 1) {
						$content .= 'Failed to delete image caption.<br />'."\n";
					} else {
						$content .= 'Successfully deleted image caption.<br />'."\n";
					}
				}
				$gallery_info = gallery_info((int)$_GET['id']);
				$image_dir = ROOT.'files/'.$gallery_info['image_dir'].'/';
				$thumb_dir = $image_dir.'thumbs/';
				if (file_exists($image_dir.$_POST['file_name']) && file_exists($thumb_dir.$_POST['file_name'])) {
					$del1 = unlink($image_dir.$_POST['file_name']);
					$del2 = unlink($thumb_dir.$_POST['file_name']);
					if ($del1 && $del2) {
						$content .= 'Successfully deleted image.<br />'."\n";
						log_action('Deleted image from gallery \''.$_POST['file_name'].'\'');
					} else {
						$content .= 'Failed to delete image.<br />'."\n";
					}
				}
			}
		}
		$tab_content['edit'] = '<span style="font-size: large; font-weight: bold;">'.$gallery_info['title'].'</span><br />'."\n";
		$tab_content['edit'] .= 'To add this gallery to your site, copy the following text into the place you would like the gallery to appear:<br />';
		$tab_content['edit'] .= '<input type="text" value="$GALLERY_EMBED-'.$gallery_info['id'].'$" /><br />'."\n";
		$tab_content['edit'] .= gallery_photo_manager($gallery_info['id']);
		$tab_content['edit'] .= gallery_upload_box($gallery_info['id'],$gallery_info['image_dir']);
		$tab_layout->add_tab('Edit Gallery',$tab_content['edit']);
		break;
	case 'delete':
		if (!isset($_GET['id'])) {
			$content .= 'No gallery selected.<br />'."\n";
			break;
		}
		if (delete_gallery((int)$_GET['id'])) {
			$content .= 'Successfully deleted gallery.'."\n";
		} else {
			$content .= 'Failed to delete gallery.<br />'."\n";
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
		// Continue with same procedure as 'built-in'...

	case 'built-in':
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

		$content .= $tab_layout;
		break;
}

?>
