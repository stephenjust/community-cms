<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
  $content = '<h1>Upload a File</h1>';
  // Check if the form has been submitted.
  if(isset($_GET['upload'])) {
    // TODO: Add automatic thumbnail generation for large images.
  	$content .= file_upload($_POST['path']);
  	}
  // Display upload form and upload location selector.
  $content .= file_upload_box(1);
?>