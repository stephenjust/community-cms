<?php
	// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}

	function get_page_content($id,$type = 1,$view = "") {
		switch ($type) {
			case 1:
				$page = display_news_content($id);
				break;
			case 2:
				$page = display_newsletters($id);
				break;
			case 3:
				include('calendar.php');
				break;
			case 4:
				$page = include(ROOT.'pagetypes/contacts.php');
				break;
			}
		return $page;
		}
?>