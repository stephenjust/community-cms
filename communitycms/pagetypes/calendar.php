<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
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
		$month_cal = new calendar_month($month,$year);
		$month_cal->setup();
		$page_content = (string)$month_cal;

		// Add month and year to page title
		Page::$title .= ' - '.date('F Y',$month_cal->first_day_ts);
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
		$event_day_s = $year.'-'.$month.'-'.$day.' 00:00:00';
		$event_day_e = $year.'-'.$month.'-'.$day.' 23:59:59';
		$day_events_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE `start` >= \''.$event_day_s.'\'
			AND `start` <= \''.$event_day_e.'\'
			ORDER BY `start` ASC, `end` DESC';
		$day_events_handle = $db->sql_query($day_events_query);
		$page_content .= '<a href="?'.Page::$url_reference.'&amp;view=month&amp;m='.$month.
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
			$event_start = strtotime($day_events['start']);
			$event_end = strtotime($day_events['end']);
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
		Page::$title .= ' - '.$month_text.' '.$day.', '.$year;
		break;
}
return $page_content;
?>