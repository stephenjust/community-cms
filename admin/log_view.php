<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	if($_GET['action'] == 'delete') {
		$delete_query = 'TRUNCATE TABLE '.$CONFIG['db_prefix'].'logs';
		$delete_handle = $db->query($delete_query);
		if(!$delete_handle) {
			$content .= 'Failed to clear log messages.<br />';
			} else {
			$content .= 'Cleared log messages.';
			}
		log_action('Cleared log messages.');
		}

// ----------------------------------------------------------------------------

	$log_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'logs log, '.$CONFIG['db_prefix'].'users user WHERE log.user_id = user.id ORDER BY log.date DESC';
	$log_message_handle = $db->query($log_message_query);
	if(!$log_message_handle) {
		$content .= 'Failed to read log messages. '.mysqli_error($db).'<br />';
		}
	$i = 1;
	$num_messages = $log_message_handle->num_rows;
	$content .= '<table class="log_messages">
<tr>
<th>Date</th><th>Action</th><th>User</th><th>IP</th>
</tr>';
	if($num_messages == 0) {
		$content .= '<tr class="row1">
<td colspan="4">No log messages.</td>
</tr>';
		}
$rowtype = 1;
	while($i <= $num_messages) {
		$log_message = $log_message_handle->fetch_assoc();
		$content .= '<tr class="row'.$rowtype.'">
<td>'.$log_message['date'].'</td>
<td>'.$log_message['action'].'</td>
<td>'.$log_message['realname'].'</td>
<td>'.long2ip($log_message['ip_addr']).'</td>
</tr>';
		if($rowtype == 1) {
			$rowtype = 2;
			} else {
			$rowtype = 1;
			}
		$i++;
		}
	$content .= '</table>
<form method="post" action="admin.php?module=log_view&action=delete">
<input type="submit" value="Clear Log Messages" />
</form>';
?>