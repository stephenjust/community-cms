<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function admin_nav() {
		global $CONFIG;
		global $db;
		$pl_file = ROOT.'admin/menu.info';
		$pl_handle = fopen($pl_file,'r');
		$page_list = fread($pl_handle,filesize($pl_file));
		fclose($pl_handle);
		$admin_pages = explode("\n",$page_list);
		unset($page_list);
		$last_heading = 'Main';
		$result = NULL;
		$list_index = 0;
		$page_index = 0;
		for($i = 0; $i < count($admin_pages); $i++) {
			if(strlen($admin_pages[$i]) > 3) { // 1
				$admin_menu_item[$i] = explode('#',$admin_pages[$i]);
				if(isset($admin_menu_item[$i][3])) { // 2
					if($admin_menu_item[$i][0] != $last_heading && $admin_menu_item[$i][1] == 1) { // 3
						$result .= '</div></div>
<div><h3><a href="#">'.stripslashes($admin_menu_item[$i][0]).'</a></h3>
<div>';
						$last_heading = $admin_menu_item[$i][0];
						$list_index++;
						} // 3
					if($admin_menu_item[$i][1] == 1) { // 4
						if($_GET['module'] == $admin_menu_item[$i][3]) {
							$page_index = $list_index;
							}
						$result .= '<a href="admin.php?module='.$admin_menu_item[$i][3].'">'.$admin_menu_item[$i][2].'</a><br />';
						} // 4
					} // 2
				} // 1
			} // FOR
		$result .= '</div></div></div>';
		$result .= '<script type="text/javascript">
$(function() {
$("#menu").accordion({ header: "h3" }).accordion( "activate" , '.$page_index.' );
});
</script>';
		return $result;
		}

	function log_action($message) {
		global $db;
		global $CONFIG;
		$message_error = NULL;
		$date = date('Y-m-d H:i:s');
		$user = $_SESSION['userid'];
		$ip_octet = $_SERVER['REMOTE_ADDR'];
		$ip_int = ip2long($ip_octet);
		$log_query = 'INSERT INTO '.$CONFIG['db_prefix'].'logs
			(user_id,action,date,ip_addr)
			VALUES ('.$user.',\''.addslashes($message).'\',\''.$date.'\','.$ip_int.')';
		$log_handle = $db->sql_query($log_query);
		if ($db->error[$log_handle] === 1) {
			$message_error = $db->_print_error_query($log_handle);
			}
		return $message_error;
		}
	?>