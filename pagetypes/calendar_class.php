<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Description of calendar_class
 *
 * @author stephen
 */
class calendar {
	public $year = 2000;
	public $month = 1;
	public $prev_year;
	public $next_year;
	public $prev_month;
	public $next_month;
	public $first_day_ts;
	public $first_day_dow;
	public $month_days;

	function __construct($month,$year) {
		// Make sure dates are in range.
		if ((int)$year < 2000 || (int)$year > 9999) {
			$this->year = 2000;
		} else {
			$this->year = (int)$year;
		}
		if ((int)$month < 1) {
			$this->month = 12;
			$this->year--;
		} else if ((int)$month > 12) {
			$this->month = 1;
			$this->year++;
		} else {
			$this->month = (int)$month;
		}

		$this->prev_year = $this->year-1;
		$this->next_year = $this->year+1;
		$this->prev_month = $this->month-1;
		$this->next_month = $this->month+1;
		$this->first_day_ts = mktime(0,0,0,$this->month,1,$this->year);
		$this->first_day_dow = date('w',$this->first_day_ts);
		$this->month_days = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
	}
}


class calendar_event {
    public $event_id;
    public $event_text;
    private $event_query;
    function __construct() {
        $this->event_id = NULL;
        $this->event_text = '';
        $this->event_query = NULL;
    }
    function __destruct() {

    }
    public function __toString() {
        return $this->event_text;
    }
    function __get($name) {
        return $this->$name;
    }
    function __set($name,$value) {
        $this->$name = $value;
        return;
    }
    function get_event($id) {
        global $db;
        global $page;
        $this->event_query = 'SELECT cal.*, cat.label 
			FROM ' . CALENDAR_TABLE . ' cal, ' . CALENDAR_CATEGORY_TABLE . ' cat
            WHERE cal.id = '.(int)$id.' AND cal.category = cat.cat_id LIMIT 1';
        $event_handle = $db->sql_query($this->event_query);
        if($db->error[$event_handle] === 1) {
            header('HTTP/1.1 404 Not Found');
            $this->event_text = '<div class="notification">
                Failed to retrieve event from the database.</div>';
            return;
        }
        if($db->sql_num_rows($event_handle) != 1) {
            header('HTTP/1.1 404 Not Found');
            $this->event_text = '<div class="notification">
                The event could not be found.</div>';
            return;
        }
        $event_info = $db->sql_fetch_assoc($event_handle);
        if($event_info['starttime'] == $event_info['endtime']) {
            $event_start = mktime(0,0,0,$event_info['month'],$event_info['day'],$event_info['year']);
            $event_time = 'All day, '.date('l, F j Y',$event_start);
            unset($event_start);
        } else {
            $event_stime = explode(':',$event_info['starttime']);
            $event_etime = explode(':',$event_info['endtime']);
            $event_start = mktime($event_stime[0],$event_stime[1],0,$event_info['month'],$event_info['day'],$event_info['year']);
            $event_end = mktime($event_etime[0],$event_etime[1],0,$event_info['month'],$event_info['day'],$event_info['year']);
            $event_time = date(get_config('time_format').' -',$event_start).
					date(' '.get_config('time_format'),$event_end)."<br />".date(' l, F j Y',$event_start);
            unset($event_stime);
            unset($event_etime);
            unset($event_start);
            unset($event_end);
        }
        $template_event = new template;
        $template_event->load_file('calendar_event');
        $template_event->event_heading = stripslashes($event_info['header']);
        $template_event->event_author = stripslashes($event_info['author']);
        $template_event->event_time = $event_time;
		if (strlen($event_info['image']) > 0) {
			$im_info = get_file_info($event_info['image']);
			$template_event->event_image = '<img src="'.stripslashes($event_info['image']).'" class="calendar_event_image" alt="'.$im_info['label'].'" />';
		} else {
			$template_event->event_image = NULL;
		}
        $template_event->event_category = stripslashes($event_info['label']);
        $template_event->event_description = stripslashes($event_info['description']);
        $template_event->event_location = stripslashes($event_info['location']);
        $this->event_text .= "<a href='?".$page->url_reference."&amp;view=month&amp;m=".
            $event_info['month']."&amp;y=".$event_info['year']."'>Back to month
            view</a><br />";
        $this->event_text .= "<a href='?".$page->url_reference."&amp;view=day&amp;d=".
            $event_info['day']."&amp;m=".$event_info['month']."&amp;y=".$event_info['year'].
            "'>Back to day view</a><br />";
        $this->event_text .= $template_event;
        unset($template_event);
        global $special_title;
        $special_title = stripslashes($event_info['header']).' - ';
        unset($event);
        return;
    }
}
?>
