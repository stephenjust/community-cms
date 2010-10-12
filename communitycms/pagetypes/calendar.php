<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
global $page;
global $page_content_info;
global $view;

$day = (isset($_GET['d']) && $_GET['d'] >= 1 && $_GET['d'] <= 31) ? (int)$_GET['d'] : date('d');
$month = (isset($_GET['m']) && $_GET['m'] >= 0 && $_GET['m'] <= 13) ? (int)$_GET['m'] : date('n');
$year = (isset($_GET['y']) && $_GET['y'] >= 2000 && $_GET['y'] <= 9999) ? (int)$_GET['y'] : date('Y');
/**#@+
 * Include necessary functions to complete tasks in this file
 */
include(ROOT . 'pagetypes/calendar_class.php');
include(ROOT . 'functions/calendar.php');
/**#@-*/
if ($view == NULL) {
	$view = get_config('calendar_defualt_view');
}
if ($view != 'month' && $view != 'day' && $view != 'event') {
	$view = get_config('calendar_default_view');
}
switch ($view) {
	// MONTH VIEW
	case "month":
		$month_cal = new calendar($month,$year);
		unset($month);
		unset($year);
		$page_content = NULL;
		$template_month = new template;
		$template_month->load_file('calendar_month');

		// Add month and year to page title
		$page->title .= ' - '.date('F Y',$month_cal->first_day_ts);

		// Replace template placeholders that should not be altered
		// beyond this point
		$template_month->current_month_name = date('F Y',$month_cal->first_day_ts);
		$template_month->current_month = $month_cal->month;
		$template_month->current_year = $month_cal->year;
		$template_month->prev_month = $month_cal->prev_month;
		$template_month->prev_year = $month_cal->prev_year;
		$template_month->next_month = $month_cal->next_month;
		$template_month->next_year = $month_cal->next_year;

		// Replace day of week placeholders
		monthcal_day_strings($template_month);

		// Week template
		$template_week = new template;
		$template_week->path = $template_month->path;
		$template_week->template = $template_month->get_range('week');

		// Extract templates for each type of day
		$template_empty_day = $template_month->get_range('empty_day');
		$template_day = $template_month->get_range('day');
		$template_today = $template_month->get_range('today');

		// Remove day templates
		$template_week->replace_range('empty_day','');
		$template_week->replace_range('day','<!-- $DAY$ -->');
		$template_week->replace_range('today','');

// ----------------------------------------------------------------------------

		// Load all events for the current month
		$cal_info_query = 'SELECT * FROM ' . CALENDAR_TABLE . ' date
			LEFT JOIN '.CALENDAR_CATEGORY_TABLE.' cat
			ON date.category = cat.cat_id
			WHERE date.month = \''.$month_cal->month.'\'
			AND date.year = \''.$month_cal->year.'\'
			ORDER BY `date`.`day` ASC, `date`.`starttime` ASC';
		$cal_info_handle = $db->sql_query($cal_info_query);
		if ($db->error[$cal_info_handle] === 1) {
			$content .= 'Failed to read dates from database.<br />';
		}
		$num_events = $db->sql_num_rows($cal_info_handle);
		$current_event_count = 1;

		// Start drawing the calendar
		$counter_dow = 0; // Day of week in loop, 0=Sunday, 1=Monday...6=Saturday
		$all_weeks = NULL;
		for ($counter_day = 1; $counter_day <= $month_cal->month_days; $counter_day++) {
			if ($counter_dow == 0) { // If it's the first day of the week
				$current_week_days = NULL; // Clear the week.
			}
			while ($counter_dow < $month_cal->first_day_dow && $counter_day == 1) {
				$current_week_days .= $template_empty_day;
				// On the first day of the month, insert blank cells to
				// make sure that we start on the right day of the week
				$counter_dow++;
			}
			unset($current_day);
			$current_day = new template;
			$current_day->path = $template_week->path;
			if ($counter_day == date('j') && $month_cal->month == date('n') && $month_cal->year == date('Y')) {
				$current_day->template = $template_today;
			} else {
				$current_day->template = $template_day;
			}
			$dates = NULL;

			// If there's no events left to check, make sure the day numbers
			// are filled in
			if ($current_event_count > $num_events) {
				$current_day->day_number = $counter_day;
			}

			// Only look for new events while there are still unchecked rows
			while ($current_event_count <= $num_events) {
				// If $cal_info_result isn't set, then we've used the
				// last entry already
				if (!isset($cal_info_result)) {
					$cal_info_result = $db->sql_fetch_assoc($cal_info_handle);
				}
				if ($cal_info_result['day'] == $counter_day) {
					// There's at least one event on this day
					$current_day->day_number = '<a href="?'.$page->url_reference
						.'&amp;view=day&amp;m='.$month_cal->month.'&amp;y='.$month_cal->year.'&amp;d='
						.$counter_day.'" class="day_number">'.$counter_day.'</a>';
				} else {
					// Either no more dates on this day or none at all. Exit.
					$current_day->day_number = $counter_day;
					break;
				}

				// Give broken categories a default colour of red
				if ($cal_info_result['colour'] == NULL) {
					$cal_info_result['colour'] = 'unknown';
				}

				// Create the link to the event page
				$dates .= '<a href="?'.$page->url_reference.'&amp;view=event&amp;'
					.'a='.$cal_info_result['id'].'" class="calendar_event">';
				// Show icon if configured to do so
				if (get_config('calendar_month_show_cat_icons') == 1) {
					$dates .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$cal_info_result['colour'].'.png"'
					.' width="10px" height="10px" alt="'.stripslashes($cal_info_result['label']).'" border="0px" /> ';
				}
				// Show event start time if configured to do so
				if (get_config('calendar_month_show_stime') == 1
						&& $cal_info_result['starttime'] != $cal_info_result['endtime']) {
					$stime_tmp = explode(':',$cal_info_result['starttime']);
					$stime_tmp = mktime($stime_tmp[0],$stime_tmp[1]);
					$dates .= '<span class="calendar_event_starttime">'.date('g:ia',$stime_tmp).'</span>'.get_config('calendar_month_time_sep');
				}
				$dates .= stripslashes($cal_info_result['header']).'</a><br />'."\n";
				$current_event_count++;
				unset($cal_info_result);
			}

			$current_day->day_events = $dates;
			$current_week_days .= $current_day->template;
			$counter_dow++;
			while ($counter_dow < 7 && $counter_day == $month_cal->month_days) { // At the end of the month,
				$current_week_days .= $template_empty_day;                 // fill any empty calendar cells with empty cells
				$counter_dow++;
			}
			if ($counter_dow == 7) { // When you reach the end of the week...
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
		$page_content .= $template_month;
		break;

// ----------------------------------------------------------------------------

// EVENT VIEW
	case "event":
		$page_content = NULL;
		if (!isset($_GET['a'])) {
			$_GET['a'] = NULL;
		}
		$event_id = (int)$_GET['a'];
		$event = new calendar_event;
		$event->get_event($event_id);
		$page_content .= $event;
		unset($event);
		break;

// ----------------------------------------------------------------------------

// DAY VIEW
	case "day":
		if ($year < 2000 || $year > 9999) { $year = 2000; } // Validate month and year values
		if ($month < 1 || $month > 12) { $month = 1; }
		if ($day < 1 || $day > 31) { $day = 1; }
		$page_content = NULL;
		// Get events for current day from database
		$day_events_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE year = '.$year.' AND month = '.$month.' AND day = '.$day.'
			ORDER BY starttime ASC';
		$day_events_handle = $db->sql_query($day_events_query);
		$page_content .= '<a href="?'.$page->url_reference.'&amp;view=month&amp;m='.$month.
			'&amp;y='.$year.'">Back to month view</a><br />'."\n";
		if ($db->error[$day_events_handle] === 1) {
			$page_content .= 'Failed to read list of events from the database.';
			break;
		}
		if ($db->sql_num_rows($day_events_handle) == 0) {
			header('HTTP/1.1 404 Not Found');
			$page_content .= 'There are no events to display.';
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
		$page_content .= $day_template_head;
		unset($day_template_head);
		for ($i = 1; $db->sql_num_rows($day_events_handle) >= $i; $i++) {
			$day_events = $db->sql_fetch_assoc($day_events_handle);
			$event_stime = explode(':',$day_events['starttime']);
			$event_etime = explode(':',$day_events['endtime']);
			$event_start = mktime($event_stime[0],$event_stime[1],0,$month,$day,$year);
			$event_end = mktime($event_etime[0],$event_etime[1],0,$month,$day,$year);
			if ($event_start == $event_end) {
				$event_time = 'All day';
			} else {
				$event_time = date(get_config('time_format'),$event_start).' - '.
						date(get_config('time_format'),$event_end);
			}
			$current_event = $event_template;
			$current_event = str_replace('<!-- $EVENT_ID$ -->',$day_events['id'],$current_event);
			$current_event = str_replace('<!-- $EVENT_TIME$ -->',$event_time,$current_event);
			$current_event = str_replace('<!-- $EVENT_HEADING$ -->',stripslashes($day_events['header']),$current_event);
			$current_event = str_replace('<!-- $EVENT_DESCRIPTION$ -->',stripslashes(truncate(strip_tags($day_events['description']),100)),$current_event);
			$page_content .= $current_event;
		}
		$month_temp = $event_start;
		$page_content .= $day_template_foot;
		unset($day_template_foot);
		unset($event_template);
		unset($day_events_query);
		unset($day_events_handle);
		unset($day_events);
		$month_text = date('F',$event_start);
		$page->title .= ' - '.$month_text.' '.$day.', '.$year;
		break;
}
return $page_content;
?>