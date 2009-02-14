<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = '<h1>Help</h1>';
	$content .= 'Help is on the way!';
?>