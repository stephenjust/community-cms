<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
$view_all = false;

if (!$acl->check_permission('adm_log_view')) {
	$content .= 'You do not have the necessary permissions to access this module.';
	return true;
}

switch ($_GET['action']) {
	case 'delete':
		if (!$acl->check_permission('log_clear')) {
			$content .= 'You are not authorized to clear the log.<br />';
			break;
		}
		$delete_query = 'TRUNCATE TABLE `' . LOG_TABLE . '`';
		$delete_handle = $db->sql_query($delete_query);
		if ($db->error[$delete_handle] === 1) {
			$content .= 'Failed to clear log messages.<br />';
			break;
		}
		$content .= 'Cleared log messages.<br />'."\n";
		log_action('Cleared log messages.');
		break;
	case 'viewall':
		$view_all = true;
		break;
	default:
		break;
}

// ----------------------------------------------------------------------------

$tab_content['view'] = NULL;
if ($view_all == false) {
	$log_message_query = 'SELECT `log`.`date`,`log`.`action`,
		`u`.`realname`,`log`.`ip_addr`
		FROM ' . LOG_TABLE . ' log, ' . USER_TABLE . ' u
		WHERE log.user_id = u.id ORDER BY log.date DESC LIMIT 50';
} else {
	$log_message_query = 'SELECT `log`.`date`,`log`.`action`,
		`u`.`realname`,`log`.`ip_addr`
		FROM ' . LOG_TABLE . ' log, ' . USER_TABLE . ' u
		WHERE log.user_id = u.id ORDER BY log.date DESC';
}
$log_message_handle = $db->sql_query($log_message_query);
if ($db->error[$log_message_handle] === 1) {
	$tab_content['view'] .= 'Failed to read log messages.<br />'."\n";
}
$table_values = array();
for ($i = 1; $i <= $db->sql_num_rows($log_message_handle); $i++) {
	$next_row = $db->sql_fetch_row($log_message_handle);
	// Convert IP address from long to proper IP address
	$next_row[3] = long2ip($next_row[3]);
	$table_values[] = $next_row;
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

$content .= $tab_layout;
?>