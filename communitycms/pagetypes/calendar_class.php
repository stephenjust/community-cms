<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Contains information about a month-long calendar
 *
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
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

/**
 * Handles the generation of an event-listing page
 *
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
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
		global $acl;
		global $db;
		global $debug;

		$this->event_query = 'SELECT `cal`.*, `cat`.`label`
			FROM `'.CALENDAR_TABLE.'` cal
			LEFT JOIN `'.CALENDAR_CATEGORY_TABLE.'` cat
			ON `cal`.`category` = `cat`.`cat_id`
			WHERE `cal`.`id` = '.(int)$id.'
			LIMIT 1';
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
		if($event_info['start'] == $event_info['end']) {
			$event_start = strtotime($event_info['start']);
			$event_time = 'All day, '.date('l, F j Y',$event_start);
		} else {
			$event_start = strtotime($event_info['start']);
			$event_end = strtotime($event_info['end']);
			$event_time = date(get_config('time_format').' -',$event_start).
					date(' '.get_config('time_format'),$event_end)."<br />".date(' l, F j Y',$event_start);
			unset($event_end);
		}
		$month_text = date('F',$event_start);
		$template_event = new template;
		$template_event->load_file('calendar_event');
		$template_event->event_heading = stripslashes($event_info['header']);
		
		// Insert edit bar
		$editbar = new editbar;
		$editbar->set_label('Event');
		if (!$acl->check_permission('adm_calendar_edit_date'))
			$editbar->visible = false;
		$editbar->add_control('admin.php?module=calendar_edit_date&id='.$event_info['id'],
				'edit.png',
				'Edit',
				array('adm_calendar_edit_date','admin_access'));
		$template_event->edit_bar = $editbar;
		
		// Insert event author
		if (get_config('calendar_show_author')) {
			$template_event->event_author_start = NULL;
			$template_event->event_author_end = NULL;
			$template_event->event_author = stripslashes($event_info['author']);
		} else
			$template_event->replace_range('event_author',NULL);

		$template_event->event_time = $event_time;
		$template_event->event_start_date = date('Y-m-d', $event_start);
		if (strlen($event_info['image']) > 0) {
			try {
				$im_file = new File(str_replace('./files/', NULL, $event_info['image']));
				$im_info = $im_file->getInfo();
				$template_event->event_image = '<img src="'.stripslashes($event_info['image']).'" class="calendar_event_image" alt="'.$im_info['label'].'" />';
			} catch (FileException $e) {
				$debug->add_trace('Image error: '.$e->getMessage(), true);
				$template_event->event_image = NULL;
			}
			$template_event->event_image_start = NULL;
			$template_event->event_image_end = NULL;
		} else
			$template_event->replace_range('event_image',NULL);
		if ($event_info['category_hide'] || $event_info['label'] == NULL) {
			$template_event->replace_range('event_category', NULL);
		} else {
			$template_event->event_category_start = NULL;
			$template_event->event_category_end = NULL;
			$template_event->event_category = $event_info['label'];
		}
		$template_event->event_description = stripslashes($event_info['description']);

		// Check if we need to fill the location field
		if (strlen($event_info['location']) < 1 || $event_info['location_hide'] == 1) {
			$template_event->replace_range('event_location',NULL);
		} else {
			$template_event->event_location = stripslashes($event_info['location']);
			$template_event->event_location_start = NULL;
			$template_event->event_location_end = NULL;
		}
		$this->event_text .= "<a href='?".Page::$url_reference."&amp;view=month&amp;m=".
			date('m',$event_start)."&amp;y=".date('Y',$event_start)."'>Back to month
			view</a><br />";
		$this->event_text .= "<a href='?".Page::$url_reference."&amp;view=day&amp;d=".
			date('d',$event_start)."&amp;m=".date('m',$event_start)."&amp;y=".date('Y',$event_start).
			"'>Back to day view</a><br />";
		$this->event_text .= $template_event;
		unset($template_event);
		Page::$title .= ' - '.stripslashes($event_info['header']).' - '.date('M d, Y',$event_start);
		return;
	}
}

class calendar_month extends calendar {
	private $event_array = array();
	private $template;
	private $template_week;
	private $template_day;
	private $template_day_empty;
	private $template_day_today;
	private $day_grid;

	/**
	 * Generate an array to hold all of the events listed in the calendar 
	 */
	public function setup() {
		// Make sure the event array has the sane number of entries as the
		// number of days in the month
		for ($i = 1; $i <= $this->month_days; $i++) {
			$this->event_array[$i] = array();
		}
		
		$this->load_events();
		$this->load_template();
		$this->build_grid();
		$this->generate_html();
	}
	
	/**
	 * Add an event to the month's event list
	 * @param integer $id
	 * @param integer $day
	 * @param string $heading
	 * @param integer $start In unix time
	 * @param integer $end In unix time
	 * @param string $category
	 * @param string $cat_image 
	 */
	public function add_event($id,$day,$heading,$start,$end,$category,$cat_image) {
		$event = array('id' => $id,
			'heading' => $heading,
			'start' => $start,
			'end' => $end,
			'cat_label' => $category,
			'cat_image' => $cat_image);
		if ($event['cat_image'] == NULL)
			$event['cat_image'] = 'unknown.png';
		$this->event_array[$day][] = $event;
	}
	
	/**
	 * Pull all of the event records for the current month from the database
	 * @global db $db
	 * @throws Exception 
	 */
	private function load_events() {
		global $db;

		// Fetch records from the database
		$month_start = $this->year.'-'.$this->month.'-01 00:00:00';
		$month_end = $this->year.'-'.$this->month.'-'.$this->month_days.' 23:59:59';
		$query = 'SELECT `date`.*, `cat`.`label`,`cat`.`colour`
			FROM `'.CALENDAR_TABLE.'` `date`
			LEFT JOIN `'.CALENDAR_CATEGORY_TABLE.'` `cat`
			ON `date`.`category` = `cat`.`cat_id`
			WHERE `date`.`start` >= \''.$month_start.'\'
			AND `date`.`start` <= \''.$month_end.'\'
			ORDER BY `date`.`start` ASC, `date`.`end` DESC';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1) 
			throw new Exception('An error occurred while reading dates from the calendar.');
		$num_events = $db->sql_num_rows($handle);

		// Add each record to the event array
		for ($i = 1; $i <= $num_events; $i++) {
			$event = $db->sql_fetch_assoc($handle);
			$start = strtotime($event['start']);
			$end = strtotime($event['end']);
			
			$this->add_event($event['id'],
					date('j',$start), $event['header'],
					$start, $end,
					$event['label'],
					$event['colour'].'.png');
		}
	}
	
	/**
	 * Load calendar template file and do initial string replacements 
	 */
	private function load_template() {
		$this->template = new template;
		$this->template->load_file('calendar_month');

		// Replace template placeholders that should not be altered
		// beyond this point
		$this->template->current_month_name = date('F Y',$this->first_day_ts);
		$this->template->current_month = $this->month;
		$this->template->current_year = $this->year;
		$this->template->prev_month = $this->prev_month;
		$this->template->prev_year = $this->prev_year;
		$this->template->next_month = $this->next_month;
		$this->template->next_year = $this->next_year;

		// Replace day of week placeholders
		// Insert date labels
		// Settings:
		// calendar_month_day_format
		// 1 - Use full name
		// 0 - Use abbreviation
		if (get_config('calendar_month_day_format') == 1) {
			$this->template->cal_sunday = 'Sunday';
			$this->template->cal_monday = 'Monday';
			$this->template->cal_tuesday = 'Tuesday';
			$this->template->cal_wednesday = 'Wednesday';
			$this->template->cal_thursday = 'Thursday';
			$this->template->cal_friday = 'Friday';
			$this->template->cal_saturday = 'Saturday';
		} else {
			$this->template->cal_sunday = 'Sun';
			$this->template->cal_monday = 'Mon';
			$this->template->cal_tuesday = 'Tues';
			$this->template->cal_wednesday = 'Wed';
			$this->template->cal_thursday = 'Thurs';
			$this->template->cal_friday = 'Fri';
			$this->template->cal_saturday = 'Sat';
		}
		
		// Week template
		$template_week = new template;
		$template_week->path = $this->template->path;
		$template_week->template = $this->template->get_range('week');

		// Extract templates for each type of day
		$template_empty_day = $this->template->get_range('empty_day');
		$template_day = $this->template->get_range('day');
		$template_today = $this->template->get_range('today');

		// Remove day templates
		$template_week->replace_range('empty_day','');
		$template_week->replace_range('day','<!-- $DAY$ -->');
		$template_week->replace_range('today','');
		
		$this->template_week = $template_week;
		$this->template_day = $template_day;
		$this->template_day_empty = $template_empty_day;
		$this->template_day_today = $template_today;
	}
	
	/**
	 * Generate a 2-d array in the form of a standard calendar grid 
	 */
	private function build_grid() {
		// Day of week: 0 - 6 = Sunday - Saturday
		$week = 0;
		$grid = array();
		// Precede the first day with blank days
		for ($day_of_week = 0; $day_of_week < $this->first_day_dow; $day_of_week++) {
			$grid[$week][$day_of_week] = 0;
		}
		for ($day = 1; $day <= $this->month_days; $day++) {
			$grid[$week][$day_of_week++] = $day;
			
			if ($day_of_week == 7) {
				$day_of_week = 0;
				$week++;
			}
		}
		// Fill out the rest of the last week with blank days
		for ($day_of_week; $day_of_week <= 6; $day_of_week++) {
			if ($day_of_week == 0)
				break;
			$grid[$week][$day_of_week] = 0;
		}
		
		$this->day_grid = $grid;
	}
	
	private function generate_html() {
		// Iterate through each week and generate those html chunks
		$all_weeks = NULL;
		for ($i = 0; $i < count($this->day_grid); $i++) {
			$current_week = clone($this->template_week);
			// Load each of the weekdays
			$week_html = NULL;
			for ($day = 0; $day <= 6; $day++) {
				$day_number = $this->day_grid[$i][$day];
				// Insert blank days
				if ($day_number === 0) {
					$day_template = $this->template_day_empty;
					$week_html .= $day_template;
					continue;
				}

				// Choose which template to use for the day
				$day_template = new template;
				$day_template->path = $current_week->path;
				if ($day_number == date('j')
						&& $this->month == date('n')
						&& $this->year == date('Y'))
					$day_template->template = $this->template_day_today;
				else
					$day_template->template = $this->template_day;
				
				// Replace day number with either a static number or a link
				if (count($this->event_array[$day_number]) > 0) {
					// There's at least one event on this day
					$day_template->day_number =
							sprintf('<a href="?%s&amp;view=day&amp;m=%u&amp;y=%u&amp;d=%u" class="day_number">%u</a>',
									Page::$url_reference,
									$this->month,$this->year,$day_number,$day_number);
					
					// Loop through day's events and add them in to the template
					$event_html = NULL;
					for ($e = 0; $e < count($this->event_array[$day_number]); $e++) {
						// Create the link to the event page
						$event_html .=
								sprintf('<a href="?%s&amp;view=event&amp;a=%u" class="calendar_event">',
										Page::$url_reference,
										$this->event_array[$day_number][$e]['id']);
						// Show icon if configured to do so
						if (get_config('calendar_month_show_cat_icons') == 1) {
							$event_html .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$this->event_array[$day_number][$e]['cat_image'].'"'
							.' width="10px" height="10px" alt="'.$this->event_array[$day_number][$e]['cat_label'].'" border="0px" /> ';
						}
						// Show event start time if configured to do so
						if (get_config('calendar_month_show_stime') == 1
								&& $this->event_array[$day_number][$e]['start'] != $this->event_array[$day_number][$e]['end']) {
							$event_html .= '<span class="calendar_event_starttime">'.date('g:ia',$this->event_array[$day_number][$e]['start']).'</span>'.get_config('calendar_month_time_sep');
						}
						$event_html .= $this->event_array[$day_number][$e]['heading'].'</a><br />'."\n";
					}
					$day_template->day_events = $event_html;
				} else {
					// Either no more dates on this day or none at all. Exit.
					$day_template->day_number = $day_number;
				}
				$week_html .= (string)$day_template;
			}
			$current_week->day = $week_html;
			$all_weeks .= (string)$current_week;
		}
		
		$this->template->replace_range('week',$all_weeks);
	}
	
	public function __toString() {
		return (string)$this->template;
	}
}
?>
