<?php
  	// Security Check
	if ($security != 1) {
		die ('You cannot access this page directly.');
		}
	echo "		<div class='content'>";
	echo get_page_content($connect,$page_info[1][id],$page_info[1][type],$_GET[view]);
	echo "		</div>";
?>