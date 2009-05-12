<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
if ($_GET['action'] == 'delete') {
	$delete_query = 'TRUNCATE TABLE ' . LOG_TABLE;
	$delete_handle = $db->sql_query($delete_query);
	if ($db->error[$delete_handle] === 1) {
		$content .= 'Failed to clear log messages.<br />';
	} else {
		$content .= 'Cleared log messages.<br />'.log_action('Cleared log messages.');
	}
}

// ----------------------------------------------------------------------------

$log_message_query = 'SELECT * FROM ' . LOG_TABLE . ' l, ' . USER_TABLE . ' u
	WHERE l.user_id = u.id ORDER BY l.date DESC';
$log_message_handle = $db->sql_query($log_message_query);
if ($db->error[$log_message_handle] === 1) {
	$content .= 'Failed to read log messages.<br />';
}
$i = 1;
$num_messages = $db->sql_num_rows($log_message_handle);
$tab_content['view'] = '<table class="admintable">
	<tr><th>Date</th><th>Action</th><th>User</th><th>IP</th></tr>';
if ($num_messages == 0) {
	$tab_content['view'] .= '<tr class="row1">
		<td colspan="4">No log messages.</td>
		</tr>';
}
$rowtype = 1;
while ($i <= $num_messages) {
	$log_message = $db->sql_fetch_assoc($log_message_handle);
	$tab_content['view'] .= '<tr class="row'.$rowtype.'">
		<td>'.$log_message['date'].'</td>
		<td>'.$log_message['action'].'</td>
		<td>'.$log_message['realname'].'</td>
		<td>'.long2ip($log_message['ip_addr']).'</td>
		</tr>';
	if ($rowtype == 1) {
		$rowtype = 2;
	} else {
		$rowtype = 1;
	}
	$i++;
}
$tab_content['view'] .= '</table>';
$tab_content['delete'] = '<form method="post" action="admin.php?module=log_view&action=delete">
<input type="submit" value="Clear Log Messages" />
</form>';
$tab_layout = new tabs;
$tab_layout->add_tab('View Log Messages',$tab_content['view']);
$tab_layout->add_tab('Delete Log Messages',$tab_content['delete']);
$content .= $tab_layout;
?>