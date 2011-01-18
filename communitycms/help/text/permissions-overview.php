<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.help
 */
if (!defined('IN_HELP')) {
	header('HTTP/1.1 403 Forbidden');
	die('You must access this file using the help browser');
}
?>
<h1>Permissions: Overview</h1>
<p>In Community CMS, determining what information a user can access and what
functions they can perform are determined by permissions. Permissions are
assigned on a per-group basis. For a user to be affected by a set of
permissions, they must be a member of the group with those permissions assigned.
The CMS can support a virtually unlimited number of user groups and permission
settings, however certain settings may conflict or depend on others, so only
experimentation can help you to achieve the desired level of access for a group
of users.</p>