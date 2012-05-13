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
include(ROOT.'functions/files_class.php');

// Include PEAR class required for tar file extraction
// FIXME: Do we need this?
//require(ROOT.'includes/Tar.php');

// ----------------------------------------------------------------------------

/**
 * file_upload_box - Create a file upload form
 * @global object $acl
 * @param integer $show_dirs
 * @param string $dir
 * @param string $extra_vars
 * @return string Form HTML
 * @throws Exception
 */
function file_upload_box($show_dirs = 0, $dir = NULL, $extra_vars = NULL) {
	global $acl;
	if (!$acl->check_permission('file_upload'))
		throw new Exception('You are not allowed to upload files.');

	$query_string = $_SERVER['QUERY_STRING'];
	$query_string = str_replace('upload=upload', NULL, $query_string);
	$query_string = preg_replace('/action=.+\&?/i',NULL,$query_string);
	$return = '<form enctype="multipart/form-data"
		action="'.$_SERVER['SCRIPT_NAME'].'?upload=upload&amp;'.
		$query_string.'" method="POST">
		<!-- Limit file size to 64MB -->
		<input type="hidden" name="MAX_FILE_SIZE" value="67108864" />
		Please choose a file: <input name="upload" type="file" /><br />'.
		"\n";
	if ($dir != NULL) {
		$return .= '<input type="hidden" name="path" value="'.$dir.'" />'."\n";
	}
	if ($show_dirs == 1) {
		// Remember path from previous upload
		if (isset($_POST['path'])) {
			$current_dir = $_POST['path'];
		} else {
			$current_dir = '';
		}
		$return .= 'Where would you like to save the file?<br />';
		$dir = ROOT.'files';
		$files = scandir($dir);
		$num_files = count($files);
		$return .= '<select name="path">
			<option value="files">Default</option>';
		for ($i = 1; $i < $num_files; $i++) {
			if($files[$i] != '..' && is_dir(ROOT.'files/'.$files[$i])) {
				if ($files[$i] == $current_dir) {
					$return .= '<option value="files/'.$files[$i].'" selected>'.$files[$i].'</option>';
				} else {
					$return .= '<option value="files/'.$files[$i].'">'.$files[$i].'</option>';
				}
			}
		} // FOR
	}
	$return .= '</select><br />'."\n";
	if (is_array($extra_vars)) {
		for ($i = 0; $i < count($extra_vars); $i++) {
			$return .= '<input type="hidden" name="'.key($extra_vars).'" value="'.current($extra_vars).'" />'."\n";
			if ($i < count($extra_vars)) next($extra_vars);
		}
	}
	$return .= '<input type="submit" value="Upload" />
		</form>';
	// Don't forget to send same 'GET' vars to script!
	return $return;
}

// ----------------------------------------------------------------------------

/**
 * file_upload - Handle files uploaded via a form
 * @global acl $acl Permission object
 * @param string $path Directory to store file - special case if = newsicons
 * @param boolean $contentfile File belongs in file/ heirarchy?
 * @param boolean $thumb Generate a thumbnail (75x75) and make original 800x800 (largest)
 * @return string
 */
function file_upload($path = "", $contentfile = true, $thumb = false) {
	global $acl;
	if (!$acl->check_permission('file_upload'))
		throw new Exception('You are not allowed to upload files.');
	if ($path != "") {
		$path .= '/';
	}
	$target = ROOT.$path;
	$filename = stripslashes(basename($_FILES['upload']['name']));
	$target .= $filename;
	$target = replace_file_special_chars($target);
	$filename = replace_file_special_chars($filename);
	
	// Check if a file by that name already exists
	if (file_exists($target))
		throw new Exception('A file by that name already exists.<br />'.
			'Please use a different file name or delete the old file before '.
			'attempting to upload the file again.');

	// Handle file upload errors sooner rather than later
	if ($_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
		$err = 'Sorry, there was a problem uploading your file.<br />';

		// List of errors
		switch ($_FILES['upload']['error']) {
			case UPLOAD_ERR_INI_SIZE:
				$err .= 'File is too large (limited by php.ini)<br />';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$err .= 'File is too large (limited by form)<br />';
				break;
			case UPLOAD_ERR_PARTIAL:
				$err .= 'File was only partially uploaded<br />';
				break;
			case UPLOAD_ERR_NO_FILE:
				$err .= 'No file was uploaded<br />';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$err .= 'Temporary folder does not exist<br />';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$err .= 'Could not write to temporary folder<br />';
				break;
			case UPLOAD_ERR_EXTENSION:
				$err .= 'A PHP extension prevented the upload<br />';
				break;
			default:
				$err .= 'Error '.$_FILES['upload']['error'].'<br />';
				break;
		}
		throw new Exception($err);
	}

	// Handle uploads to 'newsicons'
	if (basename($path) == 'newsicons') {
		if (preg_match('/(\.png|\.jp[e]?g)$/i',$filename)) {
			@move_uploaded_file($_FILES['upload']['tmp_name'], $target);
			if (generate_thumbnail($target,$target,1,1,100,100)) {
				$return = "The file " . $filename . " has been uploaded. ";
				Log::addMessage('Uploaded icon '.replace_file_special_chars($_FILES['upload']['name']));
			} else {
				throw new Exception('Failed to generate thumbnail.');
			}
			return $return;
		} else {
			throw new Exception('The \'newsicons\' folder can '.
				'only contain PNG and Jpeg images.');
		}
	}

	// Move temporary file to its new location
	move_uploaded_file($_FILES['upload']['tmp_name'], $target);
	$return = "The file " . $filename . " has been uploaded. ";
	Log::addMessage('Uploaded file '.replace_file_special_chars($_FILES['upload']['name']));
	if ($thumb == true) {
		if (generate_thumbnail($target,NULL,75,75,0,0)) {
			$return .= 'Generated thumbnail. ';
		} else {
			$return .= '<span class="errormessage">Failed to generate thumbnail.</span>';
		}
		if (generate_thumbnail($target,$target,1,1,800,800)) {
			$return .= 'Resized original image. ';
		} else {
			$return .= '<span class="errormessage">Failed to resize original.</span>';
		}
	}
	return $return;
}

// ----------------------------------------------------------------------------

/**
 * Generate a list of folders
 * @param string $directory
 * @param string $current
 * @param integer $type
 * @param string $name
 * @param string $extra
 * @return string
 */
function folder_list($directory = "",$current = "",$type = 0,$name='folder_list',$extra='') {
	$folder_root = './files/';
	if (preg_match('#.#',$directory)) {
		return 'Error retreiving folder list.<br />'."\n";
	}
	$folder_open = $folder_root.$directory;
	$files = scandir($folder_open);
	$num_files = count($files);
	$return = NULL;
	if ($num_files == 0) {
		$return .= 'There are no files to display in this folder.';
	}
	if ($type == 1) { // Start listbox if that is the view mode specified.
		$return .= '<select name="'.$name.'" id="'.$name.'" '.$extra.'>
<option value="">Default</option>';
	}
	for ($i = 1; $i < $num_files; $i++) {
		if ($files[$i] == '..' || !is_dir($folder_open.'/'.$files[$i])) {
			continue;
		}
		if ($type == 0) {
			$return .= $files[$i].'<br />';
		} elseif ($type == 1) {
			if ($current == $files[$i]) {
				$return .= '<option value="'.$files[$i].'" selected>'.$files[$i].'</option>';
			} else {
				$return .= '<option value="'.$files[$i].'">'.$files[$i].'</option>';
			}
		}
	}
	if ($type == 1) { // End folder listbox if that was the view mode specified.
		$return .= '</select>';
	}
	return $return;
}

// ----------------------------------------------------------------------------

/**
 * Create a subdirectory in the files/ tree
 * @global acl $acl
 * @param string $folder_name
 * @throws Exception 
 */
function file_create_folder($folder_name) {
	global $acl;
	if (!$acl->check_permission('file_create_folder'))
		throw new Exception('You are not allowed to create folders.');

	$folder_name = trim($folder_name);
	// Validate folder name
	if (strlen($folder_name) > 30
			|| strlen($folder_name) < 4
			|| !preg_match('#^[a-z0-9\_]+$#i',$folder_name))
		throw new Exception('New folder name must be between 4 and 30 '.
				'characters long and can only contain letters, numbers, and _.');

	// Don't create subdirectories called 'files', that will cause issues
	if(file_exists(ROOT.'files/'.$folder_name) || $folder_name == 'files')
		throw new Exception('A file or folder with that name already exists.');

	mkdir(ROOT.'files/'.$folder_name);
	Log::addMessage('Created new directory \'files/'.$folder_name.'\'');
}

/**
 * Delete an uploaded file
 * @global acl $acl
 * @global db $db
 * @param type $filename
 * @throws Exception 
 */
function file_delete($filename) {
	global $acl;
	global $db;

	if (!$acl->check_permission('file_delete'))
		throw new Exception('You are not allowed to delete files.');
	if (!file_exists($filename))
		throw new Exception('The file you are trying to delete does not exist.');
	if (preg_match('/^\.\.|\.\./',$filename) || !preg_match('#^\./files/#i',$filename))
		throw new Exception('The file you are trying to delete is invalid.');

	// Attempt to delete file from disk
	$del = unlink($filename);
	if(!$del)
		throw new Exception('The file "'.$filename.'" could not be deleted.');

	// Attempt to delete database record associated with file
	$delete_info_query = 'DELETE FROM `'.FILE_TABLE.'`
		WHERE `path` = \''.$db->sql_escape_string($filename).'\'';
	$delete_info_handle = $db->sql_query($delete_info_query);
	if($db->error[$delete_info_handle] === 1)
		throw new Exception('The database record for file "'.$filename.'" could not be deleted.');

	Log::addMessage('Deleted file \''.$filename.'\'');
}

/**
 * Generate a list of files
 * @param string $directory
 * @param integer $type
 * @param string $selected
 * @return string
 */
function file_list($directory = "", $type = 0, $selected = "") {
	$return = NULL;
	$folder_root = ROOT.'files/';
	if (!preg_match('/[.]/',$directory) == 0) {
		return 'Error retrieving file list.<br />'."\n";
	}
	$folder_open = $folder_root.$directory;
	$folder_open_short = './files/'.$directory;
	$files = scandir($folder_open);
	$num_files = count($files);
	$j = 0;
	if ($type == 1) {
		$return .= '<select name="file_list">';
	} elseif ($type == 2) {
		// If type = 2, display icons for images, and display radio buttons
		// next to each icon. If it is not an image, do not display it. Add
		// a 'No image' link as well.
		$return .= '<input type="radio" name="image" value="" checked>No Image<br />';
		$j++; // Make sure this is displayed even if there's no files.
	}
	for ($i = 1; $i < $num_files; $i++) {
		if (!is_dir($folder_open.'/'.$files[$i])) {
			if ($type == 1) {
				$return .= '<option value="'.$folder_open_short.'/'.$files[$i].'" />'.$files[$i].'</option>';
				$j++;
			} elseif ($type == 2) {
				if (preg_match('#\.png|\.jpg$#i',$files[$i]) == 1) {
					$return .= '<div class="admin_image_list_item">';
					$file_info = get_file_info($folder_open_short.'/'.$files[$i]);
					if ($folder_open.'/'.$files[$i] == $selected) {
						$return .= '<input type="radio" name="image" value="'.$folder_open_short.'/'.$files[$i].'" checked /><br /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$file_info['label'].'" />';
					} else {
						$return .= '<input type="radio" name="image" value="'.$folder_open_short.'/'.$files[$i].'" /><br /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$file_info['label'].'" />';
					}
					$return .= '</div>';
					$j++;
				}
			} else {
				$return .= '<a href="'.$folder_open_short.'/'.$files[$i].'">'.$files[$i].'</a><br />';
				$j++; // Count files that were displayed.
			}
		}
	}
	if ($type == 1) {
		$return .= '</select>';
	}
	// Check if any files were displayed
	if ($j == 0) {
		$return = 'There are no files to display.';
	}
	return $return;
}

// ----------------------------------------------------------------------------

/**
 * Generate a directory and file list that updates through javascript
 * @param string $directory
 * @param string $root
 * @return string
 */
function dynamic_file_list($directory = '',$root = ROOT) {
	// Write folder list
	$current = $directory;
	$dropdown_box_options = '<option value="">Default</option>';
	$folder_root = $root.'files/';
	if (preg_match('#./#',$directory)) {
		return 'Error retrieving folder list.';
	}
	$folder_open = $folder_root;
	$files = scandir($folder_open);
	$num_files = count($files);
	$return = NULL;
	$dropdown_box_options = NULL;
	for ($i = 1; $i < $num_files; $i++) {
		if ($files[$i] == '..' || !is_dir($folder_open.'/'.$files[$i])) {
			continue;
		}
		if ($current == $files[$i]) {
			$dropdown_box_options .= '<option value="'.$files[$i].'" selected>'.$files[$i].'</option>';
		} else {
			$dropdown_box_options .= '<option value="'.$files[$i].'">'.$files[$i].'</option>';
		}
	}
	$return .= '<select name="folder_dropdown_box" id="dynamic_folder_dropdown_box" onChange="update_dynamic_file_list(\''.$root.'\')">
	<option value="">Default</option>'.$dropdown_box_options.'
	</select><br />';

	// Generate file list
	$return .= file_list($directory,1);
	return $return;
}

// ----------------------------------------------------------------------------

/**
 * get_file_info - Look up file information in the database
 * @global db $db Database connection object
 * @param string $file Path to file
 * @return array
 */
function get_file_info($file) {
	global $db;
	$file_info_query = 'SELECT * FROM ' . FILE_TABLE . '
		WHERE path = \''.addslashes($db->sql_escape_string($file)).'\' LIMIT 1';
	$file_info_handle = $db->sql_query($file_info_query);
	if ($db->error[$file_info_handle] === 1) {
		$file_info['label'] = 'Could not read information.';
		return $file_info;
	}
	if ($db->sql_num_rows($file_info_handle) != 1) {
		$file_info['label'] = NULL;
	} else {
		$file_info = $db->sql_fetch_assoc($file_info_handle);
	}
	return $file_info;
}

/**
 * Replace special or problematic characters in file name with underscores
 * @param string $filename Filename to escape
 * @return string New filename
 */
function replace_file_special_chars($filename) {
	// Validate parameters
	if (!is_string($filename)) {
		return false;
	}

	$filename = str_replace(array('\'','"','?','+','@','#','$','!','^',' '),'_',$filename);
	return $filename;
}

// ----------------------------------------------------------------------------

/**
 * Resize an image
 * @global Debug $debug Debug object
 * @param string $original Path to original file
 * @param string $thumb_path Path to new file; if NULL, put it in /thumbs/ subdir
 * @param integer $min_w Minimum thumbnail width; cannot be 0
 * @param integer $min_h Minimum thumbnail height; cannot be 0
 * @param integer $max_w Maximum thumbnail width; 0 is no limit
 * @param integer $max_h Maximum thumbnail height; 0 is no limit
 * @return boolean Success
 */
function generate_thumbnail($original,$thumb_path = NULL,$min_w = 1,$min_h = 1,$max_w = 0,$max_h = 0) {
	global $debug;

	if (!file_exists($original)) {
		$debug->addMessage('Failed to find original file',true);
		return false;
	}
	if ($min_w == 0 || $min_h == 0) {
		$debug->addMessage('Cannot have minimum dimension of 0px',true);
		return false;
	}
	if (preg_match('/\.png$/i',$original)) {
		$image = imageCreateFromPNG($original);
		$imagetype = 'png';
	} elseif (preg_match('/\.(jpg|jpeg)$/i',$original)) {
		$image = imageCreateFromJPEG($original);
		$imagetype = 'jpg';
	} else {
		$debug->addMessage('A thumbnail cannot be created from '.$original,true);
		return false;
	}

	if ($thumb_path == NULL) {
		// Add /thumbs/ to the path (using the reverse, and only replacing the first slash
		$reverse_path = strrev($original);
		$reverse_path = str_replace_count('/','/sbmuht/',$reverse_path,1);
		$thumb_path = strrev($reverse_path);
	}

	$image_x = imagesx($image);
	$image_y = imagesy($image);

	// If maximum dimensions are set
	if ($max_h != 0 || $max_w != 0) {
		if ($max_h == 0 && $max_w != 0 && $image_x > $max_w) {
			$new_x = $max_w;
			$new_y = $image_y * ($new_x / $image_x);
		} elseif ($max_h != 0 && $max_w == 0 && $image_y > $max_h) {
			$new_y = $max_h;
			$new_x = $image_x * ($new_y / $image_y);
		} else {
			$new_x = $max_w;
			$new_y = $image_y * ($new_x / $image_x);
			if ($new_y > $max_w) {
				$new_y = $max_h;
				$new_x = $image_x * ($new_y / $image_y);
			}
		}
		// Prevent stretching
		if ($image_y < $new_y || $image_x < $new_x) {
			$new_y = $image_y;
			$new_x = $image_x;
		}
		// Handle minimum values
		if ($new_x < $min_w) {
			$new_x = $min_w;
		}
		if ($new_y < $min_h) {
			$new_y = $min_h;
		}
	} else {
		// No max value - one dimension has no upper limit
		if ($image_y >= $image_x) {
			$new_x = $min_w;
			$new_y = $image_y * ($new_x / $image_x);
		} else {
			$new_y = $min_h;
			$new_x = $image_x * ($new_y / $image_y);
		}
	}

	$thumb_image = imageCreateTrueColor($new_x,$new_y);
	imagecopyresampled($thumb_image, $image, 0, 0, 0, 0, $new_x, $new_y, $image_x, $image_y);
	if ($imagetype == 'png') {
		imagepng($thumb_image,$thumb_path);
	} else {
		imagejpeg($thumb_image,$thumb_path);
	}
	$debug->addMessage('Generated thumbnail',false);
	return true;
}

?>