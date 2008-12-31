<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
  switch($_GET['view']) {
    default:
  if(!isset($_GET['m'])) { $month = date('n'); } else { $month = $_GET['m']; }
  if(!isset($_GET['y'])) { $year = date('Y'); } else { $year = $_GET['y']; }
  if ($year < 2000) { $year = 2000; }
  if ($month < 1) { $month = 12; $year--; }
  if ($month > 12) { $month = 1; $year++; }
  $day1['timestamp'] = mktime(0,0,0,$month,1,$year);
  $day1['day_of_week'] = date('w',$day1['timestamp']);
  $calendar_days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
  $prev_year = $year - 1;
  $prev_month = $month - 1;
  $next_year = $year + 1;
  $next_month = $month + 1;
  $page = NULL;
  $calendar_month = load_template_file('calendar_month.html');
  global $special_title;
  $special_title = date('F Y',$day1['timestamp']).' - ';
	$calendar_month['contents'] = str_replace('<!-- $CURRENT_MONTH_NAME$ -->',date('F Y',$day1['timestamp']),$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $CURRENT_MONTH$ -->',$month,$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $CURRENT_YEAR$ -->',$year,$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $PREV_MONTH$ -->',$prev_month,$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $PREV_YEAR$ -->',$prev_year,$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $NEXT_MONTH$ -->',$next_month,$calendar_month['contents']);
	$calendar_month['contents'] = str_replace('<!-- $NEXT_YEAR$ -->',$next_year,$calendar_month['contents']);
	$calendar_month_temp = $calendar_month['contents'];
	unset($calendar_month);
	$calendar_month_temp = explode('<!-- $WEEK_START$ -->',$calendar_month_temp);
	$calendar_month_header = $calendar_month_temp[0];
	$page .= $calendar_month_header;
	unset($calendar_month_header);
	$calendar_month_temp = $calendar_month_temp[1];
	$calendar_month_temp = explode('<!-- $WEEK_END$ -->',$calendar_month_temp);
	$calendar_week = $calendar_month_temp[0];
	$calendar_footer = $calendar_month_temp[1];
	unset($calendar_month_temp);
	$counter_day = 1;
  $counter_dow = 0;
	while($counter_day <= $calendar_days) {
		if($counter_day == 1) {
			$current_week_temp = $calendar_week;
			$current_week_temp = explode('<!-- $EMPTY_DAY_START$ -->',$current_week_temp);
			$week_start = $current_week_temp[0];
			$current_week_temp = explode('<!-- $EMPTY_DAY_END$ -->',$current_week_temp[1]);
			$empty_day = $current_week_temp[0];
			$current_week_temp = explode('<!-- $DAY_START$ -->',$current_week_temp[1]);
			$current_week_temp = explode('<!-- $DAY_END$ -->',$current_week_temp[1]);
			$regular_day = $current_week_temp[0];
			$current_week_temp = explode('<!-- $TODAY_START$ -->',$current_week_temp[1]);
			$current_week_temp = explode('<!-- $TODAY_END$ -->',$current_week_temp[1]);
			$today_cell = $current_week_temp[0];
			$week_end = $current_week_temp[1];
			}
		if($counter_dow == 0) { // If it's the first day of the week,
			$current_week = $week_start; // Start the week template.
			}
		while($counter_dow < $day1['day_of_week'] && $counter_day == 1) {
			$current_week .= $empty_day; // If it's the first day of the month,
			$counter_dow++;              // insert some empty cells into the calendar.
			}
		if($counter_day == date('j') && $month == date('n') && $year == date('Y')) {
			$current_day = $today_cell;
			} else {
			$current_day = $regular_day;
			}
			unset($day_info);
			$day_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar date, '.$CONFIG['db_prefix'].'calendar_categories cat WHERE date.month = \''.$month.'\' AND date.year = \''.$year.'\' AND date.day = \''.$counter_day.'\' AND date.category = cat.cat_id LIMIT 0,2';
			$day_info_handle = $db->query($day_info_query);
			if ($day_info_handle->num_rows > 0) {
				$day_number = '<a href="?id='.$_GET['id'].'&view=day&m='.$month.'&y='.$year.'&d='.$counter_day.'" class="day_number">'.$counter_day.'</a>';
				} else {
				$day_number = $counter_day;
				}
		$current_day = str_replace('<!-- $DAY_NUMBER$ -->',$day_number,$current_day);
    $i = 1;
    $dates = NULL;
    while ($i <= $day_info_handle->num_rows) {
    	$day_info = $day_info_handle->fetch_assoc();
    	if($day_info['colour'] == '') {
    		$day_info['colour'] = 'red';
    		}
      $dates .= "<a href='?id=".$_GET['id']."&view=event&a=".$day_info['id'].'\' class="calendar_event"><img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png" width="16px" height="16px" alt="'.stripslashes($day_info['label']).'" border="0px" />'.stripslashes($day_info['header'])."</a><br />";
      $i++;
    }
    $current_day = str_replace('<!-- $DAY_EVENTS$ -->',$dates,$current_day);
		$current_week .= $current_day;
		$counter_dow++;
		while ($counter_dow < 7 && $counter_day == $calendar_days) { // At the end of the month,
			$current_week .= $empty_day;                              // fill any empty calendar cells with empty cells
			$counter_dow++;
			}			
		$counter_day++;
		if($counter_dow == 7) { // When you reach the end of the week...
			$current_week .= $week_end; // End the template for the current week,
			$counter_dow = 0; // Set the day back to Sunday,
			$page .= $current_week; // Add the full week to the page.
			}
		}
	$page .= $calendar_footer;
		break;
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
				$event_template = load_template_file('calendar_event.html');
				$event_template['contents'] = str_replace('<!-- $EVENT_HEADING$ -->',stripslashes($event['header']),$event_template['contents']);
				$event_template['contents'] = str_replace('<!-- $EVENT_AUTHOR$ -->',stripslashes($event['author']),$event_template['contents']);
				$event_template['contents'] = str_replace('<!-- $EVENT_TIME$ -->',$event_time,$event_template['contents']);
				$event_template['contents'] = str_replace('<!-- $EVENT_CATEGORY$ -->',stripslashes($event['label']),$event_template['contents']);			
				$event_template['contents'] = str_replace('<!-- $EVENT_DESCRIPTION$ -->',stripslashes($event['description']),$event_template['contents']);
				$event_template['contents'] = str_replace('<!-- $EVENT_LOCATION$ -->',stripslashes($event['location']),$event_template['contents']);
				$page .= "<a href='?id=".$_GET['id']."&m=".$event['month']."&y=".$event['year']."'>Back to month view</a><br />";
				$page .= "<a href='?id=".$_GET['id']."&view=day&d=".$event['day']."&m=".$event['month']."&y=".$event['year']."'>Back to day view</a><br />";
				$page .= $event_template['contents'];
				unset($event_template);
				global $special_title;
				$special_title = stripslashes($event['header']).' - ';
				unset($event);
				}
    	break;
    case "day":
      $day = get_row_from_db("calendar","WHERE year = $_GET[y] AND month = $_GET[m] AND day = $_GET[d] ORDER BY starttime ASC");
      $page = "<a href='?id=".$_GET['id']."&m=".$_GET['m']."&y=".$_GET['y']."'>Back to month view</a><br />\n";
      $page = $page."<table class='calendar_day'><tr><th>Time</th><th>Header</th><th>Description</th></tr>";
      $i = 1;
      while ($i <= $day['num_rows']) {
        $event_stime = explode(':',$day[$i]['starttime']);
        $event_etime = explode(':',$day[$i]['endtime']);
        $event_start = mktime($event_stime[0],$event_stime[1],0,$_GET['m'],$_GET['d'],$_GET['y']);
        $event_end = mktime($event_etime[0],$event_etime[1],0,$_GET['m'],$_GET['d'],$_GET['y']);
        if ($event_start == $event_end) {
        $page = $page."<tr><td class='time'><div class='time'>All day</div></td>";
        } else {
        $page = $page."<tr><td class='time'><div class='time'>".date('g:ia',$event_start)." - ".date('g:ia',$event_end)."</div></td>";
        }
        $page = $page."<td class='head'><div class='head'><a href='?id=".$_GET['id']."&view=event&a=".$day[$i]['id']."'>".stripslashes($day[$i]['header'])."</a></div></td>
<td class='description'><div class='description'>".stripslashes($day[$i]['description'])."</div></td></tr>\n";
        $i++;
      }
			$page .= "</table>";
		break;
	}
	return $page;
?>