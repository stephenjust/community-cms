<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function checkbox($var) {
		if($var == "on") {
			return 1; 
			} else {
			return 0;
			}
		}


	function replace_char_codes($input) {
		$output = $input;
		$output = str_replace('–','&ndash;',$output);
		$output = str_replace('’','&rsquo;',$output);
		return $output;
		}
?>
