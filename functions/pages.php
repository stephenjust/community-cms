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
		if(isset($_POST['vote']) && isset($_POST['vote_poll'])) {
			$question_id = $_POST['vote_poll'];
			$answer_id = $_POST['vote'];
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$query = 'INSERT INTO '.$CONFIG['db_prefix'].'poll_responses (question_id ,answer_id ,value ,ip_addr) VALUES ('.$question_id.', '.$answer_id.', NULL, \''.ip2long($user_ip).'\');';
			$handle = $db->query($query);
			if(!$handle) {
				echo('Failed to submit your vote.');
				} else {
				echo('Thank you for voting.');
				}
			}
		$page_type_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pagetypes WHERE id = '.$type.' LIMIT 1';
		$page_type_handle = $db->query($page_type_query);
		try {
			if($page_type_handle->num_rows == 1) {
				$page_type = $page_type_handle->fetch_assoc();
				$page = $NOTIFICATION.include(ROOT.'pagetypes/'.$page_type['filename']);
				} else {
				header("HTTP/1.0 404 Not Found");
				global $page_not_found;
				$page_not_found = 1;
				throw new Exception('Page not found.');
				}
			}
		catch(Exception $e) {
			$page = '<b>Error:</b> '.$e->getMessage();
			}
		return $page;
		}
?>