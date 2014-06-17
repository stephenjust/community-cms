<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}
global $acl;
$view_all = false;

if (!$acl->check_permission('adm_log_view'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

switch ($_GET['action']) {
	case 'delete':
		if (!$acl->check_permission('log_clear')) {
			echo '<span class="errormessage">You are not authorized to clear the log.</span><br />'."\n";
			break;
		}
		try {
			Log::clear();
			echo 'Cleared log messages.<br />'."\n";
		} catch (Exception $ex) {
			echo '<span class="errormessage">Failed to clear log messages.</span><br />'."\n";
		}
		break;
	case 'viewall':
		$view_all = true;
		break;
	default:
		break;
}

// ----------------------------------------------------------------------------

$tab_content['view'] = NULL;
if ($view_all == true) {
	$limit = 1000;
} else {
	$limit = 50;
}
$messages = Log::getLastMessages($limit);
if (!$messages) {
	$tab_content['view'] = '<span class="errormessage">Failed to read log messages.</span><br />'."\n";
}
$table_values = array();
for ($i = 0; $i < count($messages); $i++) {
	$table_values[] = array($messages[$i]['date'],$messages[$i]['action'],$messages[$i]['user_name'],$messages[$i]['ip_addr']);
}
$tab_content['view'] .= create_table(array('Date','Action','User','IP'),
		$table_values);
if ($view_all == false) {
	$tab_content['view'] .= '<form method="POST" action="?module=log_view&amp;action=viewall">'."\n".
		'<input type="submit" value="View All Logs" /></form>'."\n";
}
$tab_layout = new tabs;
$tab_layout->add_tab('View Log Messages',$tab_content['view']);

if ($acl->check_permission('log_clear')) {
	$tab_content['delete'] = '<form method="post" action="admin.php?module=log_view&action=delete">
	<input type="submit" value="Clear Log Messages" />
	</form>';
	$tab_layout->add_tab('Delete Log Messages',$tab_content['delete']);
}

echo $tab_layout;
?>