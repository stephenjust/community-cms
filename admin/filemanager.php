<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	if($_GET['action'] == 'new_folder') {
		$new_folder_name = addslashes($_POST['new_folder_name']);
		// Validate folder name
		if(strlen($new_folder_name) > 30) {
			$content .= 'New folder name too long.<br />';
			$error = 1;
			}
		if(strlen($new_folder_name) < 4) {
			$content .= 'New folder name too short.<br />';
			$error = 1;
			}
		if(!eregi('^[[:alnum:]\_]+$',$new_folder_name) && $error != 1) {
			$content .= 'New folder name contains an invalid character.<br />';
			$error = 1;
			}
		if($error != 1) {
			if(!file_exists(ROOT.'files/'.$new_folder_name)) {
				mkdir(ROOT.'files/'.$new_folder_name);
				log_action('Created new directory \'files/'.$new_folder_name.'\'');
				} else {
				$content .= 'A file or folder with that name already exists.';
				}
			} // IF error
		} // IF 'new_folder'

// ----------------------------------------------------------------------------

	if(!isset($_POST['folder_list'])) {
		$_POST['folder_list'] = NULL;
		}
	$content .= '<form method="POST" action="admin.php?module=filemanager">
'.folder_list('',$_POST['folder_list'],1); // Create listbox with folder names and a form to navigate folders.
	$content .= '<input type="submit" value="Change Directory" />
</form>
<br />';
	$content .= file_list($_POST['folder_list']); // Get a file list of the current directory.
	$content .= '<br />
<br />
<form method="post" action="?module=filemanager&action=new_folder">
New folder: <input type="text" name="new_folder_name" maxlength="30" />
<input type="submit" value="Create Folder" />
</form>';
?>