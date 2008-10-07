<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	$i = 1;
		$newsletter = get_row_from_db("newsletters","WHERE page = ".$id." ORDER BY year desc, month desc LIMIT 0,30");
		if($newsletter['num_rows'] == 0) {
			$return = "No newsletters to display";
			} else {
			$currentyear = $newsletter[1]['year'];
			$return = "<div class='newsletter'><span class='newsletter_year'>".$currentyear."</span><br />\n";
			while ($newsletter['num_rows'] >= $i) {
				if ($currentyear != $newsletter[$i]['year']) {
					$currentyear = $newsletter[$i]['year'];
					$return = $return."<span class='newsletter_year'>".$currentyear."</span><br />\n";
					}
				if ($newsletter[$i]['hidden'] != 1) {
					$return = $return.'<a href="'.$newsletter[$i]['path'].'">'.$newsletter[$i]['label']."</a><br />\n";
					} else {
					$return = $return.$newsletter[$i]['label']."<br />\n";
					}
				$i++;
				}
			}
		return $return."</div>";
	?>