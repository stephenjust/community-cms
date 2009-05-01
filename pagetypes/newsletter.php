<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	$return = NULL;
    global $CONFIG;
    global $db;
    global $page;
	$i = 1;
		$newsletter_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'newsletters WHERE page = '.$page->id.' ORDER BY year desc, month desc LIMIT 0,30';
		$newsletter_handle = $db->query($newsletter_query);
		if($newsletter_handle->num_rows == 0) {
			$return .= "No newsletters to display";
			} else {
			$newsletter = $newsletter_handle->fetch_assoc();
			$currentyear = $newsletter['year'];
			$return .= "<div class='newsletter'><span class='newsletter_year'>".$currentyear."</span><br />\n";
			while ($newsletter_handle->num_rows >= $i) {
				if ($currentyear != $newsletter['year']) {
					$currentyear = $newsletter['year'];
					$return .= "<span class='newsletter_year'>".$currentyear."</span><br />\n";
					}
				if ($newsletter['hidden'] != 1) {
					$return .= '<a href="'.$newsletter['path'].'">'.$newsletter['label']."</a><br />\n";
					} else {
					$return .= $newsletter['label']."<br />\n";
					}
				$i++;
				if($i <= $newsletter_handle->num_rows) {
					$newsletter = $newsletter_handle->fetch_assoc();
					}
				}
			}
		return $return."</div>";
	?>