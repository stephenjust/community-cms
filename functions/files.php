<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}

	// Load template file
	function load_template_file($filename = 'index.html') {
		global $db; // Needed for db query
		global $CONFIG; // Needed for db query
		global $site_info; // Needed for db query
		$template_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'templates WHERE id = '.$site_info['template'];
		$template_handle = $db->query($template_query);
		if(!$template_handle) {
			echo mysql_error($db);
			} else {
			$template = $template_handle->fetch_assoc();
			}
		$template_path = $template['path'];
		$template_file = $template_path.$filename;
		$handle = fopen($template_file, "r");
		$tpl_file['contents'] = fread($handle, filesize($template_file));
		$tpl_file['template_path'] = $template_path;
		fclose($handle);
		return $tpl_file;
		}

	// File Upload Functions
	function file_upload_box($show_dirs = 0) {	// Displays HTML for a file upload box
		$return = '<form enctype="multipart/form-data" action="'.$_SERVER['SCRIPT_NAME'].'?upload=upload&'.$_SERVER['QUERY_STRING'].'" method="POST">
Please choose a file: <input name="upload" type="file" /><br />
';
		if($show_dirs == 1) {
			$return = $return.'Where would you like to save the file?<br />';
			$dir = ROOT.'files';
			$files = scandir($dir);
			$num_files = count($files);
			$i = 1;
			$return = $return.'<select name="path">
<option value="">Default</option>';
			while($i < $num_files) {
				if($files[$i] != '..' && is_dir(ROOT.'files/'.$files[$i])) {
					$return = $return.'<option value="'.$files[$i].'">'.$files[$i].'</option>';
					}
				$i++;
				}
			}
			$return = $return.'</select><br />';
			$return = $return.'<input type="submit" value="Upload" />
</form>'; // Don't forget to send same 'GET' vars to script!
		return $return;
		}
	function file_upload($path = "") {
		if($path != "") {
			$path = $path.'/';
			}
		$target = ROOT.'files/'.$path;
		$target = $target . basename( $_FILES['upload']['name']) ;
		$ok=1;
		if(move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
			$return = "The file ". basename( $_FILES['upload']['name']). " has been uploaded. ";
			$return .= log_action ('Uploaded file '.$_FILES['upload']['name']);
			} else {
			$return = "Sorry, there was a problem uploading your file.";
			}
		return $return;
		}
		
	// Create a folder list
	function folder_list($directory = "",$current = "",$type = 0) {
		$folder_root = './files/';
		if(!eregi('.',$directory)) {
			$folder_open = $folder_root.$directory;
			$files = scandir($folder_open);
			$num_files = count($files);
			$i = 1;
			$j = 1;
			$return = NULL;
			if($num_files == 0) {
				$return .= 'There are no files to display in this folder.';
				}
			if($type == 1) { // Start listbox if that is the view mode specified.
				$return .= '<select name="folder_list">
<option value="">Default</option>';
				}
			while($i < $num_files) {
				if($files[$i] != '..' && is_dir($folder_open.'/'.$files[$i])) {
					if($type == 0) {
						$return = $return.$files[$i].'<br />';
						} elseif($type == 1) {
						if($current == $files[$i]) {
							$return .= '<option value="'.$files[$i].'" selected>'.$files[$i].'</option>';
							} else {
							$return .= '<option value="'.$files[$i].'">'.$files[$i].'</option>';
							}
						}
					}
				$i++;
				}
			if($type == 1) { // End folder listbox if that was the view mode specified.
				$return = $return.'</select>';
				}
			} else {
			$return = 'Error retrieving folder list.';
			}
		return $return;
		}

	// Create a file list
	function file_list($directory = "", $type = 0, $selected = "") {
		$return = NULL;
		$folder_root = ROOT.'files/';
		if(eregi('[.]',$directory) == 0) {
			$folder_open = $folder_root.$directory;
			$folder_open_short = './files/'.$directory;
			$files = scandir($folder_open);
			$num_files = count($files);
			$i = 1;
			$j = 1;
			if($type == 1) {
				$return .= '<select name="file_list">';
				} elseif($type == 2) {	// If type = 2, display icons for images, and display radio buttons next to each icon. If it is not an image,
																// do not display it. Add a 'No image' link as well.
				$return .= '<input type="radio" name="image" value="" checked>No Image<br />';
				$j++; // Make sure this is displayed even if there's no files. 
				}
			while($i < $num_files) {
				if(!is_dir($folder_open.'/'.$files[$i])) {
					if($type == 1) {
						$return .= '<option value="'.$folder_open_short.'/'.$files[$i].'" />'.$files[$i].'</option>';
						$j++;
						} elseif($type == 2) {
						if(ereg('\.png|\.jpg$',$files[$i]) == 1) {
							$return .= '<div class="admin_image_list_item">';
							if($folder_open.'/'.$files[$i] == $selected) {
								$return .= '<input type="radio" name="image" value="'.$folder_open_short.'/'.$files[$i].'" checked /><br /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$files[$i].'" />';
								} else {
								$return .= '<input type="radio" name="image" value="'.$folder_open_short.'/'.$files[$i].'" /><br /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$files[$i].'" />';
								}
							$return .= '</div>';
							$j++;
							}
						} else {
						$return .= '<a href="'.$folder_open_short.'/'.$files[$i].'">'.$files[$i].'</a><br />';
						$j++; // Count files that were displayed.
						}
					}
				$i++;
				}
			if($j == 1) { // If no files were displayed, this will stay at 1.
				$return = 'There are no files to display.';
				}
			if($type == 1) {
				$return .= '</select>';
				}
			} else {
			$return = 'Error retrieving file list.';
			}
		return $return;
		}
	function dynamic_file_list($directory = '') {
		//
		// Folder list portion:
		//

		$current = $directory;
		$dropdown_box_options = '<option value="">Default</option>';
		$folder_root = ROOT.'files/';
		if(!eregi('./',$directory)) {
			$folder_open = $folder_root;
			$files = scandir($folder_open);
			$num_files = count($files);
			$i = 1;
			$j = 1;
			$return = NULL;
			$dropdown_box_options = NULL;
			while($i < $num_files) {
				if($files[$i] != '..' && is_dir($folder_open.'/'.$files[$i])) {
					if($current == $files[$i]) {
						$dropdown_box_options .= '<option value="'.$files[$i].'" selected>'.$files[$i].'</option>';
						} else {
						$dropdown_box_options .= '<option value="'.$files[$i].'">'.$files[$i].'</option>';
						}
					}
				$i++;
				}
			} else {
			$return .= 'Error retrieving folder list.';
			}
		$return .= '<select name="folder_dropdown_box" id="dynamic_folder_dropdown_box" onChange="update_dynamic_file_list()">
		<option value="">Default</option>'.$dropdown_box_options.'
		</select><br />';
		
		//
		// File list portion:
		//

		$return .= file_list($directory,1);
		return $return;
		}
	?>