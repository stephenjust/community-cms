<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if(!isset($_POST['folder_list'])) {
		$_GET['folder_list'] == "";
		}
	$content = '<form method="POST" action="admin.php?module=filemanager">'.folder_list('',1); // Create listbox with folder names and a form to navigate folders.
	$content = $content.'<input type="submit" value="Change Directory" /></form><br />';
	$content = $content.file_list($_POST['folder_list']); // Get a file list of the current directory.
?>