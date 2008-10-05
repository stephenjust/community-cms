<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}

	// File Upload Functions
	function file_upload_box($show_dirs = 0) {	// Displays HTML for a file upload box
		$return = '<form enctype="multipart/form-data" action="'.$_SERVER['SCRIPT_NAME'].'?upload=upload&'.$_SERVER['QUERY_STRING'].'" method="POST">
Please choose a file: <input name="upload" type="file" /><br />
';
		if($show_dirs == 1) {
			$return = $return.'Where would you like to save the file?<br />';
			$dir = './files';
			$files = scandir($dir);
			$num_files = count($files);
			$i = 1;
			$return = $return.'<select name="path">
<option value="">Default</option>';
			while($i < $num_files) {
				if($files[$i] != '..' && is_dir('./files/'.$files[$i])) {
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
		$target = 'files/'.$path;
		$target = $target . basename( $_FILES['upload']['name']) ;
		$ok=1;
		if(move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
			$return = "The file ". basename( $_FILES['upload']['name']). " has been uploaded";
			} else {
			$return = "Sorry, there was a problem uploading your file.";
			}
		return $return;
		}
		
	// Create a folder list
	function folder_list($directory = "", $type = 0) {
		$folder_root = './files/';
		if(!eregi('[.]',$directory)) {
			$folder_open = $folder_root.$directory;
			$files = scandir($folder_open);
			$num_files = count($files);
			$i = 1;
			$j = 1;
			if($num_files == 0) {
				$return = 'There are no files to display in this folder.';
				}
			if($type == 1) { // Start listbox if that is the view mode specified.
				$return = $return.'<select name="folder_list">
<option value="">Default</option>';
				}
			while($i < $num_files) {
				if($files[$i] != '..' && is_dir($folder_open.'/'.$files[$i])) {
					if($type == 0) {
						$return = $return.$files[$i].'<br />';
						} elseif($type == 1) {
						$return = $return.'<option value="'.$files[$i].'">'.$files[$i].'</option>';
						}
					}
				$i++;
				}
			if($type == 1) { // End folder listbox if that was the view mode specified.
				$return = $return.'</select>';
				}
			} else {
			$return = 'Error retrieving file list.';
			}
		return $return;
		}

	// Create a file list
	function file_list($directory = "", $type = 0, $selected = "") {
		$return = NULL;
		$folder_root = './files/';
		if(eregi('[.]',$directory) == 0) {
			$folder_open = $folder_root.$directory;
			$files = scandir($folder_open);
			$num_files = count($files);
			$i = 1;
			$j = 1;
			if($type == 1) {
				$return = $return.'<select name="file_list">';
				} elseif($type == 2) {	// If type = 2, display icons for images, and display radio buttons next to each icon. If it is not an image,
																// do not display it. Add a 'No image' link as well.
				$return = $return.'<input type="radio" name="image" value="" checked>No Image<br />';
				$j++; // Make sure this is displayed even if there's no files. 
				}
			while($i < $num_files) {
				if(!is_dir($folder_open.'/'.$files[$i])) {
					if($type == 1) {
						$return = $return.'<option value="'.$folder_open.'/'.$files[$i].'" />'.$files[$i].'</option>';
						$j++;
						} elseif($type == 2) {
						if(ereg('\.png|\.jpg$',$files[$i]) == 1) {
							if($folder_open.'/'.$files[$i] == $selected) {
								$return = $return.'<input type="radio" name="image" value="'.$folder_open.'/'.$files[$i].'" checked /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$files[$i].'" /><br />';
								} else {
								$return = $return.'<input type="radio" name="image" value="'.$folder_open.'/'.$files[$i].'" /><img src="'.$folder_open.'/'.$files[$i].'" alt="'.$files[$i].'" /><br />';
								}
							$j++;
							}
						} else {
						$return = $return.'<a href="'.$folder_open.'/'.$files[$i].'">'.$files[$i].'</a><br />';
						$j++; // Count files that were displayed.
						}
					}
				$i++;
				}
			if($j == 1) { // If no files were displayed, this will stay at 1.
				$return = 'There are no files to display.';
				}
			if($type == 1) {
				$return = $return.'</select>';
				}
			} else {
			$return = 'Error retrieving file list.';
			}
		return $return;
		}
	?>