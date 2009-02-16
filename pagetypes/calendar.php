<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	switch($_GET['view']) {
	// MONTH VIEW
		default:
			if(!isset($_GET['m'])) { $month = date('n'); } else { $month = $_GET['m']; }
			if(!isset($_GET['y'])) { $year = date('Y'); } else { $year = $_GET['y']; }
			if ($year < 2000 || $year > 9999) { $year = 2000; } // Validate month and year values
			if ($month < 1) { $month = 12; $year--; }
			if ($month > 12) { $month = 1; $year++; }
			$day1['timestamp'] = mktime(0,0,0,$month,1,$year); // First day of month
			$day1['day_of_week'] = date('w',$day1['timestamp']);
			$calendar_days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
			$prev_year = $year - 1;
			$prev_month = $month - 1;
			$next_year = $year + 1;
			$next_month = $month + 1;
			$page = NULL;
			$template_month = new template;
			$template_month->load_file('calendar_month');
			global $special_title;
			$special_title = date('F Y',$day1['timestamp']).' - ';
			$template_month->current_month_name = date('F Y',$day1['timestamp']);
			$template_month->current_month = $month;
			$template_month->current_year = $year;
			$template_month->prev_month = $prev_month;
			$template_month->prev_year = $prev_year;
			$template_month->next_month = $next_month;
			$template_month->next_year = $next_year;	
			// Week template				
			$template_week = new template;
			$template_week->path = $template_month->path;
			$template_week->template = $template_month->get_range('week');
			$template_empty_day = $template_month->get_range('empty_day');
			$template_day = $template_month->get_range('day');
			$template_today = $template_month->get_range('today');
			// Replace day entries with placeholders
			$template_week->replace_range('empty_day','');
			$template_week->replace_range('day','<!-- $DAY$ -->');
			$template_week->replace_range('today','');
			$counter_day = 1;
			$counter_dow = 0;
			$all_weeks = NULL;
			while($counter_day <= $calendar_days) {
				if($counter_dow == 0) { // If it's the first day of the week
					$current_week_days = NULL; // Clear the week.
					}
				while($counter_dow < $day1['day_of_week'] && $counter_day == 1) {
					$current_week_days .= $template_empty_day;
					// If it's the first day of the month,
					// insert some empty cells into the calendar.
					$counter_dow++;
					}
					unset($current_day);
					$current_day = new template;
					$current_day->path = $template_week->path;
				if($counter_day == date('j') && $month == date('n') && $year == date('Y')) {
					$current_day->template = $template_today;
					} else {
					$current_day->template = $template_day;
					}
				$day_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar date, '.$CONFIG['db_prefix'].'calendar_categories cat WHERE date.month = \''.$month.'\' AND date.year = \''.$year.'\' AND date.day = \''.$counter_day.'\' AND date.category = cat.cat_id LIMIT 0,2';
				$day_info_handle = $db->query($day_info_query);
				if ($day_info_handle->num_rows > 0) {
					$current_day->day_number = '<a href="?id='.$_GET['id'].'&view=day&m='.$month.'&y='.$year.'&d='.$counter_day.'" class="day_number">'.$counter_day.'</a>';
					} else {
					$current_day->day_number = $counter_day;
					}
				$dates = NULL;
				for ($i = 1; $i <= $day_info_handle->num_rows; $i++) {
					$day_info = $day_info_handle->fetch_assoc();
					if($day_info['colour'] == '') {
						$day_info['colour'] = 'red';
						}
					$dates .= "<a href='?id=".$_GET['id']."&view=event&a=".$day_info['id'].'\' class="calendar_event">
<img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png" width="16px" height="16px" alt="'.stripslashes($day_info['label']).'" border="0px" />
'.stripslashes($day_info['header'])."</a><br />";
					}
				$current_day->day_events = $dates;
				$current_week_days .= $current_day->template;
				$counter_dow++;
				while ($counter_dow < 7 && $counter_day == $calendar_days) { // At the end of the month,
					$current_week_days .= $template_empty_day;                 // fill any empty calendar cells with empty cells
					$counter_dow++;
					}
				$counter_day++;
				if($counter_dow == 7) { // When you reach the end of the week...
					$current_week = new template;
					$current_week->template = $template_week->template;
					$current_week->path = $template_week->path;
					$current_week->day = $current_week_days;
					$counter_dow = 0; // Set the day back to Sunday,
					$all_weeks .= $current_week->template; // Add the full week to the page.
					unset($current_week);
					}
				unset($current_day);
				unset($day_info);
				}
			$template_month->replace_range('week',$all_weeks);
			$page .= $template_month;
			break;
	// EVENT VIEW
		case "event":
			$page = NULL;
			$event_id = stripslashes($_GET['a']);
			$event_query = 'SELECT cal.*, cat.label FROM '.$CONFIG['db_prefix'].'calendar cal, '.$CONFIG['db_prefix'].'calendar_categories cat WHERE cal.id = '.$event_id.' AND cal.category = cat.cat_id LIMIT 1';
			$event_handle = $db->query($event_query);
			if(!$event_handle || $event_handle->num_rows == 0) {
				header('HTTP/1.1 404 Not Found');
				$page .= 'The event you are trying to view could not be found.';
				} else {
				$event = $event_handle->fetch_assoc();
				if($event['starttime'] == $event['endtime']) {
					$event_start = mktime(0,0,0,$event['month'],$event['day'],$event['year']);
					$event_time = 'All day, '.date('l, F j Y',$event_start);
					unset($event_start);
					} else {
					$event_stime = explode(':',$event['starttime']);
					$event_etime = explode(':',$event['endtime']);
					$event_start = mktime($event_stime[0],$event_stime[1],0,$event['month'],$event['day'],$event['year']);
					$event_end = mktime($event_etime[0],$event_etime[1],0,$event['month'],$event['day'],$event['year']);
					$event_time = date('g:ia -',$event_start).date(' g:ia',$event_end)."<br />".date(' l, F j Y',$event_start);
					unset($event_stime);
					unset($event_etime);
					unset($event_start);
					unset($event_end);
					}
				$template_event = new template;
				$template_event->load_file('calendar_event');
				$template_event->event_heading = stripslashes($event['header']);
				$template_event->event_author = stripslashes($event['author']);
				$template_event->event_time = $event_time;
				$template_event->event_category = stripslashes($event['label']);			
				$template_event->event_description = stripslashes($event['description']);
				$template_event->event_location = stripslashes($event['location']);
				$page .= "<a href='?id=".$_GET['id']."&m=".$event['month']."&y=".$event['year']."'>Back to month view</a><br />";
				$page .= "<a href='?id=".$_GET['id']."&view=day&d=".$event['day']."&m=".$event['month']."&y=".$event['year']."'>Back to day view</a><br />";
				$page .= $template_event;
				unset($template_event);
				global $special_title;
				$special_title = stripslashes($event['header']).' - ';
				unset($event);
				}
			break;
	// DAY VIEW
		case "day":
			// Validate month, year and day values
			if(!isset($_GET['m'])) { $month = date('n'); } else { $month = $_GET['m']; }
			if(!isset($_GET['y'])) { $year = date('Y'); } else { $year = $_GET['y']; }
			if(!isset($_GET['d'])) { $day = date('d'); } else { $day = $_GET['d']; }
			if ($year < 2000 || $year > 9999) { $year = 2000; } // Validate month and year values
			if ($month < 1 || $month > 12) { $month = 1; }
			if ($day < 1 || $day > 31) { $day = 1; }
			$page = NULL;
			// Get events for current day from database
			$day_events_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar WHERE year = '.$year.' AND month = '.$month.' AND day = '.$day.' ORDER BY starttime ASC';
			$day_events_handle = $db->query($day_events_query);
      $page .= "<a href='?id=".$_GET['id']."&m=".$month."&y=".$year."'>Back to month view</a><br />\n";
			if(!$day_events_handle) {
				$page .= 'Failed to read list of events from the database.';
				break;
				}
			if($day_events_handle->num_rows == 0) {
				header('HTTP/1.1 404 Not Found');
				$page .= 'There are no events to display.';
				break;
				}
			$day_template = load_template_file('calendar_day.html');
			$day_template_temp = explode('<!-- $EVENT_START$ -->',$day_template['contents']);
			$day_template_head = $day_template_temp[0];
			$day_template_temp = explode('<!-- $EVENT_END$ -->',$day_template_temp[1]);
			$event_template = $day_template_temp[0];
			$day_template_foot = $day_template_temp[1];
			unset($day_template);
			unset($day_template_temp);
			$page .= $day_template_head;
			unset($day_template_head);
			for($i = 1; $day_events_handle->num_rows >= $i; $i++) {
				$day_events = $day_events_handle->fetch_assoc();
				$event_stime = explode(':',$day_events['starttime']);
				$event_etime = explode(':',$day_events['endtime']);
				$event_start = mktime($event_stime[0],$event_stime[1],0,$month,$day,$year);
				$event_end = mktime($event_etime[0],$event_etime[1],0,$month,$day,$year);
				if ($event_start == $event_end) {
					$event_time = 'All day';
					} else {
					$event_time = date('g:ia',$event_start).' - '.date('g:ia',$event_end);
					}
				$current_event = $event_template;
				$current_event = str_replace('<!-- $EVENT_ID$ -->',$day_events['id'],$current_event);
				$current_event = str_replace('<!-- $EVENT_TIME$ -->',$event_time,$current_event);
				$current_event = str_replace('<!-- $EVENT_HEADING$ -->',stripslashes($day_events['header']),$current_event);
				$current_event = str_replace('<!-- $EVENT_DESCRIPTION$ -->',stripslashes($day_events['description']),$current_event);
				$page .= $current_event;
				}
			$month_temp = $event_start;
			$page .= $day_template_foot;
			unset($day_template_foot);
			unset($event_template);
			unset($day_events_query);
			unset($day_events_handle);
			unset($day_events);
			global $special_title;
			$month_text = date('F',$event_start);
			$special_title = $month_text.' '.$day.', '.$year.' - ';
			break;
		}
	return $page;
?>