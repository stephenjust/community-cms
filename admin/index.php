<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	define('ROOT','./');
	if($_GET['action'] == 'new_log') {
		$log_message = strip_tags($_POST['message']);
		if(strlen($log_message) > 5) {
			log_action($log_message);
			}
		}
	$log_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'logs log, '.$CONFIG['db_prefix'].'users user WHERE log.user_id = user.id ORDER BY log.date DESC LIMIT 5';
	$log_message_handle = $db->query($log_message_query);
	if(!$log_message_handle) {
		$content .= 'Failed to read log messages. '.mysqli_error($db).'<br />';
		}
	$i = 1;
	$num_messages = $log_message_handle->num_rows;
$content = '<h1>Administration</h1>';
$content .= '<h3>Most Recent Activity:</h3>
<table class="log_messages">
<tr>
<th>Date</th><th>Action</th><th>User</th><th>IP</th>
</tr>';
$rowtype = 1;
	while($i <= $num_messages) {
		$log_message = $log_message_handle->fetch_assoc();
		$content .= '<tr class="row'.$rowtype.'">
<td>'.$log_message['date'].'</td><td>'.$log_message['action'].'</td><td>'.$log_message['realname'].'</td><td>'.long2ip($log_message['ip_addr']).'</td>
</tr>';
		if($rowtype == 1) {
			$rowtype = 2;
			} else {
			$rowtype = 1;
			}
		$i++;
		}
	$content .= '</table>';
	$content .= '<form method="post" action="?action=new_log"><input type="text" name="message" /><input type="submit" value="Add Message" /></form>';
$content .= '<h3>User Summary:</h3>
You have at least one admin user and possibly some other users.
<h3>Database Summary:</h3>
You are using <i>some</i> space for your database.'; 
?>