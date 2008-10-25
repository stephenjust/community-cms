<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	$log_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'logs log, '.$CONFIG['db_prefix'].'users user WHERE log.user_id = user.id ORDER BY log.date DESC';
	$log_message_handle = $db->query($log_message_query);
	if(!$log_message_handle) {
		$content .= 'Failed to read log messages. '.mysqli_error($db).'<br />';
		}
	$i = 1;
	$num_messages = $log_message_handle->num_rows;
	$content .= '<table class="log_messages">
<tr>
<th>Date</th><th>Action</th><th>User</th>
</tr>';
$rowtype = 1;
	while($i <= $num_messages) {
		$log_message = $log_message_handle->fetch_assoc();
		$content .= '<tr class="row'.$rowtype.'">
<td>'.$log_message['date'].'</td><td>'.$log_message['action'].'</td><td>'.$log_message['realname'].'</td>
</tr>';
		if($rowtype == 1) {
			$rowtype = 2;
			} else {
			$rowtype = 1;
			}
		$i++;
		}
	$content .= '</table>';
?>