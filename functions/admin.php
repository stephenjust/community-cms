<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function admin_nav() {
		global $CONFIG;
		global $db;
		$result = NULL;
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'admin_pages WHERE on_menu = 1 AND category = "Main" ORDER BY category,label ASC';
		$page_handle = $db->query($page_query);
		$i = 1;
		$header = NULL;
		while($page_handle->num_rows >= $i) {
			$page_list = $page_handle->fetch_assoc();
			$result .= '<a href="admin.php?module='.$page_list['file'].'">'.$page_list['label'].'</a><br />';
			$i++;
			}
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'admin_pages WHERE on_menu = 1 AND category != "Main" ORDER BY category,label ASC';
		$page_handle = $db->query($page_query);
		$i = 1;
		while($page_handle->num_rows >= $i) {
			$page_list = $page_handle->fetch_assoc();
			$last_header = $header;
			$header = $page_list['category'];
			if($header != $last_header) {
				$result .= '<span class="nav_header">'.stripslashes($page_list['category']).'</span><br />';
				}
			$result .= '<a href="admin.php?module='.$page_list['file'].'">'.stripslashes($page_list['label']).'</a><br />';
			$i++;
			}
		return $result;
		}
	$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'admin_pages WHERE file = "'.$_GET['module'].'" LIMIT 1';
	$page_handle = $db->query($page_query);
	if($page_handle->num_rows != 1) {
		include('./admin/index.php');
		} else {
		$page = $page_handle->fetch_assoc();
		$loaded = include('./admin/'.$page['file'].'.php');
		if(!$loaded) {
			$content = 'Failed to load '.$page['file'].'.php';
			}
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