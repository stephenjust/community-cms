<?php
/**
 * Community CMS Installer - "What's New"
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

ob_start();
?>
<h1>What's New?</h1>
<ul><li><a href="index.php?page=license">Install</a></li></ul>
<h2>New in Version 0.6</h2>
<h3>Features</h3>
<ul>
	<li>Admin</li>
	<ul>
		<li>Slick new administrative interface design</li>
		<li>Simplified, reorganized, and unified administration modules</li>
	</ul>
	<li>Pages</li>
	<ul>
		<li>The new Text-ID feature allows you to keep web addresses readable</li>
		<li>You can keep pages from appearing on the menu by hiding them.</li>
	</ul>
	<li>News</li>
	<ul>
		<li>Module settings allow you to alter the default settings for your new news articles, and to change other news-related settings</li>
		<li>Multiple news items can be moved, copied, or deleted at once</li>
	</ul>
	<li>Calendar</li>
	<ul>
		<li>A more colourful design for your visitors</li>
		<li>A date selector allows you to easily set the date for your event entries</li>
		<li>The new time parser allows you to enter times of day in almost any common format</li>
	</ul>
	<li>Newsletters</li>
	<ul>
		<li>Your current entries can now be edited</li>
	</ul>
	<li>Contacts</li>
	<ul>
		<li>Contact entries can now be created and edited independant of users</li>
	</ul>
	<li>Users</li>
	<ul>
		<li>The new User Groups feature allows you to categorize all of your users into groupings of different privelege levels</li>
	</ul>
	<li>Files</li>
	<ul>
		<li>It is now really easy to link to a file using TinyMCE</li>
		<li>Using the file manager's edit button, you can assign a label to your files</li>
		<li>Using the file manager's delete button, you can now delete files</li>
	</ul>
	<li>Feedback can now be provided to the developers through a built-in form</li>
	<li>Community CMS can now install on a PostgreSQL database</li>
	<li>Installer can now change default admin username and password</li>
</ul>
<h3>Under the Hood Changes</h3>
<ul>
	<li>Added a debug mode</li>
	<li>Added ability to use custom database tables by editing includes/constants.php</li>
	<li>Moved a great amount of functionality into classes and functions</li>
	<li>Separated the database processing layer from the rest of the code</li>
</ul>
<h3>Bug Fixes</h3>
<ul>
	<li>Calendar entries are now listed on the calendar in order of their start time</li>
	<li>Fixed bug which made it impossible to change a user's password</li>
	<li>Fixed pagination bug</li>
	<li>Fixed several SQL Injection vulnerabilities</li>
</ul>
<form method="post" action="index.php?page=license">
	<input type="submit" value="Install" />
</form>
<?php
$content = ob_get_clean();
?>
