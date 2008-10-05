<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
$content = '<h1>Administration</h1>
<h3>Most Recent Activity:</h3>
These headings were added to the administration panel. Note the highly modified Page Manager, as it now supports moving pages and deleting pages in a user friendly way. When creating a new page, the \'hidden\' checkbox now behaves as expected.
<h3>User Summary:</h3>
You have at least one admin user and possibly some other users.
<h3>Database Summary:</h3>
You are using <i>some</i> space for your database.'; 
?>