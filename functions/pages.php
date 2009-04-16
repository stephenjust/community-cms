<?php
	// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}
    include (ROOT.'functions/page_class.php');
	function get_page_content($id,$type = 1,$view = "") {
		if($type == "") {
			$type = 0;
			}
		$id = (int)$id;
		global $CONFIG;
        global $page;
		global $db;
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes WHERE id = '.$type.' LIMIT 1';
		$page_type_handle = $db->query($page_type_query);
		try {
			if($page_type_handle->num_rows == 1) {
				$page_type = $page_type_handle->fetch_assoc();
				$pg = include(ROOT.'pagetypes/'.$page_type['filename']);
				} else {
				header("HTTP/1.0 404 Not Found");
				global $page_not_found;
				$page_not_found = 1;
				throw new Exception('Page not found.');
				}
			}
		catch(Exception $e) {
			$page->notification .= '<b>Error:</b> '.$e->getMessage();
			$pg = NULL;
			}
		return $pg;
		}
?>