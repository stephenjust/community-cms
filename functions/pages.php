<?php
	// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}
    include (ROOT.'functions/page_class.php');
	function get_page_content($id,$type = 'news',$view = "") {
		if($type == "") {
			$type = 'news';
			}
		$id = (int)$id;
		global $CONFIG;
        global $page;
		global $db;
        $pg = include(ROOT.'pagetypes/'.$type);
        return $pg;
    }
?>