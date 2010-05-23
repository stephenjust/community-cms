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

if ($_GET['action'] == 'new_log') {
	if (!$acl->check_permission('log_post_custom_message')) {
		$content .= 'You are not authorized to post custom log messages.<br />';
	} else {
		$log_message = strip_tags($_POST['message']);
		if (strlen($log_message) > 5) {
			log_action($log_message);
		} else {
			$content .= 'The log message you entered was too short.<br />';
		}
	}
} // IF 'new_log'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
// Display log messages
$tab_content['activity'] = NULL;
$log_message_query = 'SELECT `log`.`date`,`log`.`action`,
	`u`.`realname`,`log`.`ip_addr`
	FROM ' . LOG_TABLE . ' log, ' . USER_TABLE . ' u
	WHERE log.user_id = u.id ORDER BY log.date DESC LIMIT 5';
$log_message_handle = $db->sql_query($log_message_query);
if ($db->error[$log_message_handle] === 1) {
	$tab_content['activity'] .= 'Failed to read log messages.<br />'."\n";
}
$table_values = array();
for ($i = 1; $i <= $db->sql_num_rows($log_message_handle); $i++) {
	$next_row = $db->sql_fetch_row($log_message_handle);
	// Convert IP address from long to proper IP address
	$next_row[3] = long2ip($next_row[3]);
	$table_values[] = $next_row;
}
$tab_content['activity'] .= create_table(array('Date','Action','User','IP'),
		$table_values);
if ($acl->check_permission('log_post_custom_message')) {
	$tab_content['activity'] .= '<form method="post" action="?action=new_log">
		<input type="text" name="message" /><input type="submit" value="Add Message" />
		</form>';
}
$tab['activity'] = $tab_layout->add_tab('Recent Activity',$tab_content['activity']);

// ----------------------------------------------------------------------------

$user_query = 'SELECT * FROM ' . USER_TABLE . ' ORDER BY id DESC';
$user_handle = $db->sql_query($user_query);
if ($user_handle) {
	$user = $db->sql_fetch_assoc($user_handle);
	$tab_content['user'] = 'Number of users: '.$db->sql_num_rows($user_handle).'<br />
		Newest user: '.$user['username'];
} else {
	$tab_content['user'] = 'Could not find user information.';
}
$tab['users'] = $tab_layout->add_tab('User Summary',$tab_content['user']);

// ----------------------------------------------------------------------------

$tab_content['database'] = 'Database Content Version: '.get_config('db_version').'<br />
	Database Software Version: '.$db->sql_server_info();
$tab['database'] = $tab_layout->add_tab('Database Summary',$tab_content['database']);
$content .= $tab_layout;
?>