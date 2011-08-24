<?php
/**
 * Community CMS
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
			Log::new_message($log_message);
		} else {
			$content .= 'The log message you entered was too short.<br />';
		}
	}
} // IF 'new_log'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$tab_content['activity'] = NULL;
// Display log messages
$messages = Log::get_last_message(5);
if (!$messages) {
	$tab_content['activity'] = '<span class="errormessage">Failed to read log messages.</span><br />'."\n";
}
$table_values = array();
for ($i = 0; $i < count($messages); $i++) {
	$table_values[] = array($messages[$i]['date'],$messages[$i]['action'],$messages[$i]['user_name'],$messages[$i]['ip_addr']);
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