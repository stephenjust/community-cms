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
		$query_string.'" method="post">
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
			<option value="">Default</option>';
		for ($i = 1; $i < $num_files; $i++) {
			if($files[$i] != '..' && is_dir(ROOT.'files/'.$files[$i])) {
				if ($files[$i] == $current_dir) {
					$return .= '<option value="'.$files[$i].'" selected>'.$files[$i].'</option>';
				} else {
					$return .= '<option value="'.$files[$i].'">'.$files[$i].'</option>';
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

/**
 * Get the subdirectories of the files tree
 * @return array
 */
function folder_get_list() {
	$directory = FILES_ROOT;
	$files = scandir($directory);
	$subdirs = array();
	for ($i = 0; $i < count($files); $i++) {
		if (!is_dir($directory.$files[$i]))
			continue;
		if ($files[$i] == '.' || $files[$i] == '..')
			continue;
		$subdirs[] = $files[$i];
	}
	return $subdirs;
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
					$f = new File($directory.'/'.$files[$i]);
					$file_info = $f->getInfo();
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

?>