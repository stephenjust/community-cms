<?php
/**
 * Community CMS Installer - "What's New"
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

ob_start();
?>
<h1>What's New?</h1>
<ul><li><a href="index.php?page=license">Install</a></li></ul>
<h2>New in Version 0.7</h2>
<h3>Features</h3>
<ul>
	<li>Admin</li>
	<ul>
		<li>Most modules can now switch datasets without reloading the page</li>
		<li>Calendar module displays start time and end time on summary page</li>
		<li>Added module to view all configuration values throughout the CMS</li>
		<li>Automatically resize images for newsicons or galleries</li>
		<li>Updated TinyMCE version</li>
	</ul>
	<li>Image Galleries</li>
	<ul>
		<li>Added support for the SimpleViewer image gallery software</li>
		<li>Added a built-in javascript-based gallery (unstable)</li>
	</ul>
	<li>News</li>
	<ul>
		<li>Added ability to post news articles that are not visible to users (unpublished)</li>
		<li>Added quick links from the front-end to edit or (un)publish a news article</li>
	</ul>
	<li>Contacts</li>
	<ul>
		<li>Added multiple styles of contact lists</li>
		<li>Added configuration value to keep phone number display consistent</li>
	</ul>
	<li>Permissions</li>
	<ul>
		<li>Added a more complete set of permissions</li>
	</ul>
	<li>Installer</li>
	<ul>
		<li>Added license(s) to installer for the user to read</li>
		<li>Code vastly improved and separated into functions to keep results from install and upgrade consistent</li>
	</ul>
	<li>Added password expiration feature</li>
	<li>Added ability for users to change their passwords without access to admin</li>
	<li>Added a hierarchal menu system</li>
	<li>Added CSS for print media</li>
</ul>
<h3>Under the Hood Changes</h3>
<ul>
	<li>Configuration is now stored in a table that is easier to expand</li>
	<li>Removed proprietary data files and replaced them with XML</li>
	<li>Many operations are now stored in functions</li>
	<li>Improved caching in several instances that greatly decreases the number of redundant SQL queries</li>
</ul>
<h3>Bug Fixes</h3>
<ul>
	<li>Escape problematic characters in file names when uploading files</li>
	<li>Regex functions should now be case-sensitive where necessary</li>
	<li>Removed remaining uses of ereg() and eregi()</li>
	<li>Fix some cases where permissions were not honored</li>
</ul>
<form method="post" action="index.php?page=license">
	<input type="submit" value="Install" />
</form>
<?php
$content = ob_get_clean();
?>
