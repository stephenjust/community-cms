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
		for($i = 0; $i < count($admin_pages); $i++) {
			if(strlen($admin_pages[$i]) > 3) { // 1
				$admin_menu_item[$i] = explode('#',$admin_pages[$i]);
				if(isset($admin_menu_item[$i][3])) { // 2
					if($admin_menu_item[$i][0] != $last_heading && $admin_menu_item[$i][1] == 1) { // 3
						$result .= '<span class="nav_header">'.stripslashes($admin_menu_item[$i][0]).'</span><br />';
						$last_heading = $admin_menu_item[$i][0];
						} // 3
					if($admin_menu_item[$i][1] == 1) { // 4
						$result .= '<a href="admin.php?module='.$admin_menu_item[$i][3].'">'.$admin_menu_item[$i][2].'</a><br />';
						} // 4
					} // 2
				} // 1
			} // FOR
		return $result;
		}

	function log_action($message) {
		global $db;
		global $CONFIG;
		$date = date('Y-m-d H:i:s');
		$user = $_SESSION['userid'];
		$ip_octet = $_SERVER['REMOTE_ADDR'];
		$ip_int = ip2long($ip_octet);
		$log_query = 'INSERT INTO '.$CONFIG['db_prefix'].'logs (user_id,action,date,ip_addr) VALUES ('.$user.',"'.$message.'","'.$date.'",'.$ip_int.')';
		if(!$db->query($log_query)) {
			$message_error = mysqli_error($db);
			}
		return $message_error;
		}
	?>