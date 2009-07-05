<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * @ignore
 */
if (isset($_GET['mode']) && (!defined('SECURITY') || !defined('ROOT') || !defined('ADMIN'))) {
	define('SECURITY',1);
	define('ADMIN',1);
	define('ROOT','../');
	require(ROOT.'config.php');
	require(ROOT.'include.php');
	require(ROOT.'functions/admin.php');
	initialize('ajax');
	$ajax = 1;
} else {
	$current_mode = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
	unset($_GET['mode']);
	if (!defined('SECURITY') || !defined('ADMIN')) {
		exit;
	}
}
// Check permission
if (!$acl->check_permission('adm_log_view')) {
	echo 'You do not have permission to access this module.<br />'."\n";
	exit;
}

// Validate $mode
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
if ($mode != ('view' || 'delete')) {
	$mode = NULL;
}

switch ($mode) {
	default:
		// Give list of modes
		$menu_items = array('view' => 'View Logs',
			'delete' => 'Delete Logs');
		break;

// ----------------------------------------------------------------------------

	case 'view':
		echo '<h1>View Logs</h1>'."\n";
		$log_message_query = 'SELECT * FROM `' . LOG_TABLE . '` `l`, `' . USER_TABLE . '` `u`
			WHERE `l`.`user_id` = `u`.`id` ORDER BY `l`.`date` DESC LIMIT 50';
		$log_message_handle = $db->sql_query($log_message_query);
		if ($db->error[$log_message_handle] === 1) {
			echo 'Failed to read log messages.<br />'."\n";
		}
		$num_messages = $db->sql_num_rows($log_message_handle);
		echo '<table class="admintable">'."\n";
		echo "\t".'<tr>'."\n";
		echo "\t\t".'<th style="width: 12em;">Date</th>'."\n";
		echo "\t\t".'<th>Action</th><th>User</th>'."\n";
		echo "\t\t".'<th style="width: 8em;">IP</th>'."\n";
		echo "\t".'</tr>'."\n";
		if ($num_messages == 0) {
			echo "\t".'<tr class="row1">'."\n";
			echo "\t\t".'<td colspan="4">No log messages.</td>'."\n";
			echo "\t".'</tr>'."\n";
		}
		$rowtype = 1;
		for ($i = 1; $i <= $num_messages; $i++) {
			$log_message = $db->sql_fetch_assoc($log_message_handle);
			echo "\t".'<tr class="row'.$rowtype.'">'."\n";
			echo "\t\t".'<td>'.$log_message['date'].'</td>'."\n";
			echo "\t\t".'<td>'.stripslashes($log_message['action']).'</td>'."\n";
			echo "\t\t".'<td>'.stripslashes($log_message['realname']).'</td>'."\n";
			echo "\t\t".'<td>'.long2ip($log_message['ip_addr']).'</td>'."\n";
			echo "\t".'</tr>'."\n";
			if ($rowtype == 1) {
				$rowtype = 2;
			} else {
				$rowtype = 1;
			}
		}
		echo '</table>';
		unset($rowtype);
		unset($log_message);
		unset($num_messages);
		unset($log_message_query);
		unset($log_message_handle);
		unset($i);
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		echo '<h1>Delete Logs</h1>'."\n";
		if ($acl->check_permission('log_clear')) {
			echo '<form method="post" action="admin.php?ui=1&amp;module=log_view&amp;mode=delete&amp;action=delete">'."\n";
			echo "\t".'<input type="submit" value="Clear Log Messages" />'."\n";
			echo '</form>'."\n";
		} else {
			echo 'You are not authorized to delete log messages.<br />'."\n";
		}

		if ($_GET['action'] == 'delete') {
			if (!$acl->check_permission('log_clear')) {
				echo 'You are not authorized to delete log messages.<br />'."\n";
			} else {
				$delete_query = 'TRUNCATE TABLE `' . LOG_TABLE . '`';
				$delete_handle = $db->sql_query($delete_query);
				if ($db->error[$delete_handle] === 1) {
					echo 'Failed to clear log messages.<br />'."\n";
				} else {
					echo 'Cleared log messages.<br />'.log_action('Cleared log messages.')."\n";
				}
			}
		}

		break;
}

if (isset($ajax)) {
	clean_up();
}

?>