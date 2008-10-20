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
  $page = $page."<th colspan='5'>".date('F Y',$day1['timestamp'])."</th>\n<th colspan='2'> <form method='post' action='?id=".$_GET['id']."&m=".$month."&y=".$prev_year."'><input type='submit' value='&lt;&lt;' width='40px' class='button_cal_back_year' /></form> 
  <form method='post' action='?id=".$_GET['id']."&m=".$prev_month."&y=".$year."'><input type='submit' value='&lt;' width='30px' class='button_cal_back_month' /></form> <form method='post' action='?id=".$_GET['id']."'><input type='submit' value='Today' width='55px' class='button_cal_today' /></form>
  <form method='post' action='?id=".$_GET['id']."&m=".$next_month."&y=".$year."'><input type='submit' value='&gt;' width='30px' class='button_cal_next_month' /></form> <form method='post' action='?id=".$_GET['id']."&m=".$month."&y=".$next_year."'><input type='submit' value='&gt;&gt;' width='40px' class='button_cal_next_year' /></form></th></tr>\n<tr>";
  $page = $page."<th>Sunday</th>\n<th>Monday</th>\n<th>Tuesday</th>\n<th>Wednesday</th>\n<th>Thursday</th>\n<th>Friday</th>\n<th>Saturday</th><tr>\n";
  while ($counter_dow < $day1['day_of_week']) {
  $page = $page."<td></td>";
  $counter_dow++;
  }
  $counter_day = 1;
  while ($counter_day <= $calendar_days) {
    if ($counter_dow == 7) { $page = $page."</tr>\n<tr>"; $counter_dow = 0; }
    unset($dates);
		$dates_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar WHERE month = \''.$month.'\' AND year = \''.$year.'\' AND day = \''.$counter_day.'\' LIMIT 0,2';
		$dates_handle = $db->query($dates_query);
		$dates[1] = $dates_handle->fetch_assoc();
		$i = 1;
		while($i < $dates_handle->num_rows) {
			$dates[$i] = $dates_handle->fetch_assoc($dates_handle);
			$i++;
			}
		$dates['num_rows'] = $dates_handle->num_rows;
    $page = $page."<td";
    if ($counter_day == date('j') && $month == date('n') && $year == date('Y')) {
      $page = $page." class='calendar_today'";
    }
    $page = $page."><b>";
    if ($dates['num_rows'] > 0) {
      $page = $page."<a href='?id=".$_GET['id']."&view=day&m=".$month."&y=".$year."&d=".$counter_day."'>".$counter_day."</a>";
    } else {
      $page = $page.$counter_day;
    }
    $page = $page."</b><br />";
    $i = 1;
    $page = $page."<div class='calendar_content'>";
    while ($i <= $dates['num_rows']) {
      $page = $page."<a href='?id=".$_GET['id']."&view=event&a=".$dates[$i]['id']."'>".stripslashes($dates[$i]['header'])."</a><br />";
      $i++;
    }
    $page = $page."</div>";
    $page = $page."</td>\n";
    $counter_day++;
    $counter_dow++;
  }
  while ($counter_dow < 7) {
    $page = $page."<td></td>";
    $counter_dow++;
  }
  $page = $page."</tr><tr><td colspan='7' align='center' style='vertical-align: middle;'>Showing 2 events per day. Click on the day number to show more.</td></tr></table>";
    break;
    case "event":
    $event = get_row_from_db("calendar","WHERE id = $_GET[a]");
    $page = "<a href='?id=".$_GET['id']."&m=".$event[1]['month']."&y=".$event[1]['year']."'>Back to month view</a><br />";
    $page = $page."<a href='?id=".$_GET['id']."&view=day&d=".$event[1]['day']."&m=".$event[1]['month']."&y=".$event[1]['year']."'>Back to day view</a><br />";
    $page = $page."<h1>".$event[1]['header']."</h1>";
    $page = $page."Posted by ".$event[1]['author']."<br />";
    if ($event[1]['starttime'] == $event[1]['endtime']) {
      $event_start = mktime(0,0,0,$event[1]['month'],$event[1]['day'],$event[1]['year']);
      $page = $page."All day, ".date('l, F j Y',$event_start);
    } else {
      $event_stime = explode(':',$event[1]['starttime']);
      $event_etime = explode(':',$event[1]['endtime']);
      $event_start = mktime($event_stime[0],$event_stime[1],0,$event[1]['month'],$event[1]['day'],$event[1]['year']);
      $event_end = mktime($event_etime[0],$event_etime[1],0,$event[1]['month'],$event[1]['day'],$event[1]['year']);
      $page = $page.date('g:ia -',$event_start);
      $page = $page.date(' g:ia',$event_end)."<br />";
      $page = $page.date(' l, F j Y',$event_start);
      $page = $page."<br />\n";
    }
    $page = $page."<br /><br />\n";
    $page = $page.$event[1]['description'];
    $page = $page."<br />\n";
    $page = $page.$event[1]['location'];
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
        $page = $page."<td class='head'><div class='head'><a href='?id=".$_GET['id']."&view=event&a=".$day[$i]['id']."'>".$day[$i]['header']."</a></div></td><td class='description'><div class='description'>".$day[$i]['description']."</div></td></tr>\n";
        $i++;
      }
      $page = $page."</table>";
    break;
  }
  return $page;
?>