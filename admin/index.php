<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if($_GET['action'] == 'new_log') {
		$log_message = strip_tags($_POST['message']);
		if(strlen($log_message) > 5) {
			log_action($log_message);
			}
		} // IF 'new_log'

// ----------------------------------------------------------------------------

	$tab_layout = new tabs;
	// Display log messages
    $tab_content['activity'] = NULL;
	$log_message_query = 'SELECT * FROM ' . LOG_TABLE . ' log, ' . USER_TABLE . ' u
		WHERE log.user_id = u.id ORDER BY log.date DESC LIMIT 5';
	$log_message_handle = $db->sql_query($log_message_query);
	if(!$log_message_handle) {
		$tab_content['activity'] .= 'Failed to read log messages.<br />';
		}
	$num_messages = $db->sql_num_rows($log_message_handle);
	$tab_content['activity'] .= '<table class="ui-corner-all admintable">
<tr>
<th>Date</th><th>Action</th><th>User</th><th>IP</th>
</tr>';
	$rowtype = 1;
	if($num_messages == 0) {
		$tab_content['activity'] .= '<tr class="row1">
<td colspan="4">No log messages.</td>
</tr>';
		}
	for ($i = 1; $i <= $num_messages; $i++) {
		$log_message = $db->sql_fetch_assoc($log_message_handle);
		$tab_content['activity'] .= '<tr class="row'.$rowtype.'">
<td>'.$log_message['date'].'</td><td>'.$log_message['action'].'</td><td>'.$log_message['realname'].'</td><td>'.long2ip($log_message['ip_addr']).'</td>
</tr>';
		if($rowtype == 1) {
			$rowtype = 2;
			} else {
			$rowtype = 1;
			}
		} // FOR $i
	$tab_content['activity'] .= '</table>';
	$tab_content['activity'] .= '<form method="post" action="?action=new_log"><input type="text" name="message" /><input type="submit" value="Add Message" />
</form>';
	$tab['activity'] = $tab_layout->add_tab('Recent Activity',$tab_content['activity']);

// ----------------------------------------------------------------------------

	$user_query = 'SELECT * FROM ' . USER_TABLE . ' ORDER BY id DESC';
	$user_handle = $db->sql_query($user_query);
	if($user_handle) {
		$user = $db->sql_fetch_assoc($user_handle);
		$tab_content['user'] = 'Number of users: '.$db->sql_num_rows($user_handle).'<br />
			Newest user: '.$user['username'];
		} else {
		$tab_content['user'] = 'Could not find user information.';
		}
	$tab['users'] = $tab_layout->add_tab('User Summary',$tab_content['user']);

// ----------------------------------------------------------------------------

	$tab_content['database'] = 'Database Content Version: '.$site_info['db_version'].'<br />
		Database Software Version: '.$db->sql_server_info();
	$tab['database'] = $tab_layout->add_tab('Database Summary',$tab_content['database']);
	$content = $tab_layout;
?>