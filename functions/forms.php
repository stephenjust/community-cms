<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * checkbox - Convert between the checkbox response, and boolean, or from boolean to HTML
 * @param mixed $var current state
 * @param int $reverse Switch from form response -> boolean to boolean -> HTML code
 * @return mixed
 */
function checkbox($var,$reverse = 0) {
	if ($reverse == 0) {
		if ($var == "on") {
			return 1;
		} else {
			return 0;
		}
	} else {
		if ($var == 1) {
			return 'checked';
		} else {
			return NULL;
		}
	}
}


function parse_time($time) {
	$new_time = 0;
	if (eregi('^[0-1]?[0-9]:[0-9][0-9] ?[ap]m?$',$time)) {
		$time = str_replace(array(' ','m','M'),NULL,$time);
		$time = explode(':',$time);
		$hour = $time[0];
		if (eregi('a$',$time[1])) {
			$minute = str_replace(array('a','A'),NULL,$time[1]);
			if ($hour == '12') {
				$hour = 0;
			}
		} else {
			$minute = str_replace(array('p','P'),NULL,$time[1]);
			if ($hour != 12) {
				$hour = $hour + 12;
			}
		}
		$new_time = $hour.':'.$minute;
		if (strlen($new_time) === 4) {
			$new_time = '0'.$new_time;
		}
	} elseif (eregi('^[0-2]?[0-9]:[0-5][0-9]$',$time)) {
		$new_time = $time;
		if (strlen($new_time) === 4) {
			$new_time = '0'.$new_time;
		}
	} elseif (eregi('^[0-1]?[0-9] ?[ap]m?$',$time)) {
		$time = str_replace(array(' ','m','M'),NULL,$time);
		$minute = '00';
		$hour = $time;
		if (eregi('a$',$time)) {
			$hour = str_replace(array('a','A'),NULL,$time);
			if ($hour == '12') {
				$hour = 0;
			}
		} else {
			$hour = str_replace(array('p','P'),NULL,$time);
			if ($hour != 12) {
				$hour = $hour + 12;
			}
		}
		$new_time = $hour.':'.$minute;
		if (strlen($new_time) === 4) {
			$new_time = '0'.$new_time;
		}
	}
	return $new_time;
}

function replace_char_codes($input) {
	$output = $input;
	$output = str_replace('–','&ndash;',$output);
	$output = str_replace('’','&rsquo;',$output);
	return $output;
}
?>
