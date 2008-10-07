<?php
	// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}

	function get_page_content($id,$type = 1,$view = "") {
		global $CONFIG;
		global $db;
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes WHERE id = '.$type.' LIMIT 1';
		$page_type_handle = $db->query($page_type_query);
		if($page_type_handle->num_rows == 1) {
			$page_type = $page_type_handle->fetch_assoc();
			$page = include(ROOT.'pagetypes/'.$page_type['filename']);
			} else {
			$page = 'Failed to load page because the type of page that you are trying to view does not exist in the database.';
			}
//		switch ($type) {
//			case 1:
//				$page = display_news_content($id);
//				break;
//			case 2:
//				$page = display_newsletters($id);
//				break;
//			case 3:
//				include('calendar.php');
//				break;
//			case 4:
//				$page = include(ROOT.'pagetypes/contacts.php');
//				break;
//			}
		return $page;
		}
?>