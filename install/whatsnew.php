<?php
/**
 * Community CMS Installer - "What's New"
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
?>
<html>
	<head>
		<title>What's New in Community CMS?</title>
		<link rel='StyleSheet' type='text/css' href='./files/style.css' />
	</head>
	<body>
		<div id="main">
			<h1>What's New?</h1>
			<ul><li><a href="index.php?page=1">Skip to Installer</a></li></ul>
			<h2>New in Version 0.6</h2>
			<h3>Features</h3>
			<ul>
				<li>Pages can now be assigned friendly human-readable names, rather than just numbers</li>
				<li>Contact entries are now created independant of users</li>
				<li>Uploaded files can now be assigned attributes</li>
				<li>It is now really easy to link to a file using TinyMCE</li>
				<li>News item display settings can be customized now</li>
				<li>Dates for calendar entries can now be easily selected</li>
				<li>Multiple news items can be moved, copied, or deleted at once</li>
				<li>Feedback can now be provided to the developers through a built-in form</li>
				<li>Users can now be assigned a group to be a member of</li>
				<li>The file manager can delete files now</li>
				<li>Community CMS can now run on a PostgreSQL database</li>
				<li>Installer can now change default admin username and password</li>
			</ul>
			<h3>Under the Hood Changes</h3>
			<ul>
				<li>Added a debug mode</li>
				<li>Added ability to use custom database tables by editing includes/constants.php</li>
				<li>Moved a great amount of functionality into classes</li>
				<li>Separated the database processing layer from the rest of the code</li>
			</ul>
			<h3>Bug Fixes</h3>
			<ul>
				<li>Calendar entries are now listed on the calendar in order of their start time</li>
				<li>Fixed bug which made it impossible to change a user's password</li>
				<li>Fixed pagination bug</li>
				<li>Fixed several SQL Injection vulnerabilities</li>
			</ul>
			<form method="post" action="index.php?page=1">
				<input type="submit" value="Install" />
			</form>
		</div>
		<div id="footer">
			Community CMS Copyright&copy; 2007 - 2009 Stephen Just
		</div>
	</body>
</html>
<?php
exit;
?>
