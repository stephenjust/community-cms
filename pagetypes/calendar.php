<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
  switch($_GET['view']) {
    default:
  if(!isset($_GET['m'])) { $month = date('m'); } else { $month = $_GET['m']; }
  if(!isset($_GET['y'])) { $year = date('Y'); } else { $year = $_GET['y']; }
  if ($year < 2000) { $year = 2000; }
  $day1['timestamp'] = mktime(0,0,0,$month,1,$year);
  $day1['day_of_week'] = date('w',$day1['timestamp']);
  if ($month == 0) { $month = 12; $year--; }
  if ($month == 13) { $month = 1; $year++; }
  $calendar_days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
  $counter_dow = 0;
  $prev_year = $year - 1;
  $prev_month = $month - 1;
  $next_year = $year + 1;
  $next_month = $month + 1;
  $page =  "<table class='calendar'><tr>\n";
  // List buttons from right to left
  $page = $page."<th colspan='4'>".date('F Y',$day1['timestamp'])."</th>\n<th colspan='3'>
	<form method='post' action='?id=".$_GET['id']."&m=".$month."&y=".$next_year."'><input type='submit' value='&gt;&gt;' width='40px' class='button_cal_next_year' /></form>
	<form method='post' action='?id=".$_GET['id']."&m=".$next_month."&y=".$year."'><input type='submit' value='&gt;' width='30px' class='button_cal_next_month' /></form>
	<form method='post' action='?id=".$_GET['id']."'><input type='submit' value='Today' width='55px' class='button_cal_today' /></form>
	<form method='post' action='?id=".$_GET['id']."&m=".$prev_month."&y=".$year."'><input type='submit' value='&lt;' width='30px' class='button_cal_back_month' /></form>
	<form method='post' action='?id=".$_GET['id']."&m=".$month."&y=".$prev_year."'><input type='submit' value='&lt;&lt;' width='40px' class='button_cal_back_year' /></form>
	</th></tr>\n<tr>";
  $page = $page."<th>Sunday</th>\n<th>Monday</th>\n<th>Tuesday</th>\n<th>Wednesday</th>\n<th>Thursday</th>\n<th>Friday</th>\n<th>Saturday</th><tr>\n";
  while ($counter_dow < $day1['day_of_week']) {
  $page = $page."<td></td>";
  $counter_dow++;
  }
  $counter_day = 1;
  while ($counter_day <= $calendar_days) {
    if ($counter_dow == 7) { $page = $page."</tr>\n<tr>"; $counter_dow = 0; }
    unset($dates);
		$dates_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar date, '.$CONFIG['db_prefix'].'calendar_categories cat WHERE date.month = \''.$month.'\' AND date.year = \''.$year.'\' AND date.day = \''.$counter_day.'\' AND date.category = cat.cat_id LIMIT 0,2';
		$dates_handle = $db->query($dates_query);
		$i = 1;
    $page = $page."<td";
    if ($counter_day == date('j') && $month == date('n') && $year == date('Y')) {
      $page = $page." class='calendar_today'";
    }
    $page = $page."><b>";
    if ($dates_handle->num_rows > 0) {
      $page = $page."<a href='?id=".$_GET['id']."&view=day&m=".$month."&y=".$year."&d=".$counter_day."'>".$counter_day."</a>";
    } else {
      $page = $page.$counter_day;
    }
    $page = $page."</b><br />";
    $i = 1;
    $page = $page."<div class='calendar_content'>";
    while ($i <= $dates_handle->num_rows) {
    	$dates = $dates_handle->fetch_assoc();
    	if($dates['colour'] == '') {
    		$dates['colour'] = 'red';
    		}
      $page = $page."<a href='?id=".$_GET['id']."&view=event&a=".$dates['id'].'\'><img src="<!-- $IMAGE_PATH$ -->icon_'.$dates['colour'].'.png" width="16px" height="16px" alt="'.$dates['label'].'" border="0px" />'.stripslashes($dates['header'])."</a><br />";
      $i++;
    }
    $page = $page."</div>";
    $page = $page."</td>\n";
    $counter_day++;
    $counter_dow++;
  }
  while ($counter_dow < 7) {
    $page .= '<td></td>';
    $counter_dow++;
  }
  $page .= "</tr><tr><td colspan='7' align='center' style='vertical-align: middle;'>Showing 2 events per day. Click on the day number to show more.</td></tr></table>";
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
      $page = $page."</table>";
    break;
  }
  return $page;
?>