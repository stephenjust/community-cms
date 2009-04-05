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
	$log_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'logs log, '.$CONFIG['db_prefix'].'users user WHERE log.user_id = user.id ORDER BY log.date DESC LIMIT 5';
	$log_message_handle = $db->query($log_message_query);
	if(!$log_message_handle) {
		$tab_content['activity'] .= 'Failed to read log messages. '.mysqli_error($db).'<br />';
		}
	$num_messages = $log_message_handle->num_rows;
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
		$log_message = $log_message_handle->fetch_assoc();
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

	$user_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users ORDER BY id DESC';
	$user_handle = $db->query($user_query);
	if($user_handle) {
		$user = $user_handle->fetch_assoc();
		$tab_content['user'] = 'Number of users: '.$user_handle->num_rows.'<br />
			Newest user: '.$user['username'];
		} else {
		$tab_content['user'] = 'Could not find user information.';
		}
	$tab['users'] = $tab_layout->add_tab('User Summary',$tab_content['user']);

// ----------------------------------------------------------------------------

	$tab_content['database'] = 'Database Version: '.$site_info['db_version'].'<br />
		MySQL Version: '.$db->get_server_info();
	$tab['database'] = $tab_layout->add_tab('Database Summary',$tab_content['database']);
	$content = $tab_layout;
?>