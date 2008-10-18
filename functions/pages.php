<?php
	// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}

	function get_page_content($id,$type = 1,$view = "") {
		if($type == "") {
			$type = 0;
			}
		global $CONFIG;
		global $db;
		global $NOTIFICATION;
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes WHERE id = '.$type.' LIMIT 1';
		$page_type_handle = $db->query($page_type_query);
		if($page_type_handle->num_rows == 1) {
			$page_type = $page_type_handle->fetch_assoc();
			$page = $NOTIFICATION.include(ROOT.'pagetypes/'.$page_type['filename']);
			} else {
			$page = 'Failed to load page because the type of page that you are trying to view does not exist in the database.';
			}
		return $page;
		}
?>