<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

namespace CommunityCMS;

use CommunityCMS\Component\EditBarComponent;

/**
 * Contains information about a month-long calendar
 *
 * @author  stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
class calendar
{
    public $year = 2000;
    public $month = 1;
    public $prev_year;
    public $next_year;
    public $prev_month;
    public $next_month;
    public $first_day_ts;
    public $first_day_dow;
    public $month_days;

    function __construct($month,$year) 
    {
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
        $this->first_day_ts = mktime(0, 0, 0, $this->month, 1, $this->year);
        $this->first_day_dow = date('w', $this->first_day_ts);
        $this->month_days = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
    }
}

/**
 * Handles the generation of an event-listing page
 *
 * @author  stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
class calendar_event
{
    public $event_id;
    public $event_text;
    private $event_query;
    function __construct() 
    {
        $this->event_id = null;
        $this->event_text = '';
        $this->event_query = null;
    }
    function __destruct() 
    {

    }
    public function __toString() 
    {
        return $this->event_text;
    }
    function __get($name) 
    {
        return $this->$name;
    }
    function __set($name,$value) 
    {
        $this->$name = $value;
        return;
    }
    
    function get_event($id) 
    {
        try {
            $eventTpl = new Tpl();
            $event = new CalEvent($id);
        } catch (CalEventException $ex) {
            header('HTTP/1.1 404 Not Found');
            $this->event_text = '<div class="notification">The event could not be found.</div>';
            Debug::get()->addMessage($ex->getMessage(), true);
            return;
        }
            
        if (acl::get()->check_permission('adm_calendar_edit_date')) {
            $editbar = new EditBarComponent();
            $editbar->setLabel('Event');
            $editbar->addControl(
                'admin.php?module=calendar_edit_date&id='.$event->getId(),
                'edit.png', 'Edit', array('adm_calendar_edit_date','admin_access')
            );
            $eventTpl->assign('editbar', $editbar->render());
        }
        $eventTpl->assign('event', $event);
        $eventTpl->assign('time_format', SysConfig::get()->getValue('time_format'));
        $eventTpl->assign('show_author', SysConfig::get()->getValue('calendar_show_author'));
        $eventTpl->assign('page_url_ref', Page::$url_reference);

        $this->event_text = $eventTpl->fetch('calendarEvent.tpl');
        Page::$title .= ' - '.stripslashes($event->getTitle().' - '.date('M d, Y', $event->getStart()));
    }
}

class calendar_month extends calendar
{
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
    public function setup() 
    {
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
     * @param string  $heading
     * @param integer $start     In unix time
     * @param integer $end       In unix time
     * @param string  $category
     * @param string  $cat_image 
     */
    public function add_event($id,$day,$heading,$start,$end,$category,$cat_image) 
    {
        $event = array('id' => $id,
        'heading' => $heading,
        'start' => $start,
        'end' => $end,
        'cat_label' => $category,
        'cat_image' => $cat_image);
        if ($event['cat_image'] == null) {
            $event['cat_image'] = 'unknown.png'; 
        }
        $this->event_array[$day][] = $event;
    }
    
    /**
     * Pull all of the event records for the current month from the database
     * @throws \Exception 
     */
    private function load_events() 
    {
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
        try {
            $results = DBConn::get()->query($query,
                [":month_start" => $month_start, ":month_end" => $month_end], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new \Exception('An error occurred while reading dates from the calendar.');
        }

        // Add each record to the event array
        foreach ($results as $event) {
            $start = strtotime($event['start']);
            $end = strtotime($event['end']);
            
            $this->add_event(
                $event['id'],
                date('j', $start), $event['header'],
                $start, $end,
                $event['label'],
                $event['colour'].'.png'
            );
        }
    }
    
    /**
     * Load calendar template file and do initial string replacements 
     */
    private function load_template() 
    {
        $this->template = new Template;
        $this->template->loadFile('calendar_month');

        // Replace template placeholders that should not be altered
        // beyond this point
        $this->template->current_month_name = date('F Y', $this->first_day_ts);
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
        if (SysConfig::get()->getValue('calendar_month_day_format') == 1) {
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
        $template_week = new Template;
        $template_week->path = $this->template->path;
        $template_week->template = $this->template->getRange('week');

        // Extract templates for each type of day
        $template_empty_day = $this->template->getRange('empty_day');
        $template_day = $this->template->getRange('day');
        $template_today = $this->template->getRange('today');

        // Remove day templates
        $template_week->replaceRange('empty_day', '');
        $template_week->replaceRange('day', '<!-- $DAY$ -->');
        $template_week->replaceRange('today', '');
        
        $this->template_week = $template_week;
        $this->template_day = $template_day;
        $this->template_day_empty = $template_empty_day;
        $this->template_day_today = $template_today;
    }
    
    /**
     * Generate a 2-d array in the form of a standard calendar grid 
     */
    private function build_grid() 
    {
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
            if ($day_of_week == 0) {
                break; 
            }
            $grid[$week][$day_of_week] = 0;
        }
        
        $this->day_grid = $grid;
    }
    
    private function generate_html() 
    {
        // Iterate through each week and generate those html chunks
        $all_weeks = null;
        for ($i = 0; $i < count($this->day_grid); $i++) {
            $current_week = clone($this->template_week);
            // Load each of the weekdays
            $week_html = null;
            for ($day = 0; $day <= 6; $day++) {
                $day_number = $this->day_grid[$i][$day];
                // Insert blank days
                if ($day_number === 0) {
                    $day_template = $this->template_day_empty;
                    $week_html .= $day_template;
                    continue;
                }

                // Choose which template to use for the day
                $day_template = new Template;
                $day_template->path = $current_week->path;
                if ($day_number == date('j')
                    && $this->month == date('n')
                    && $this->year == date('Y')
                ) {
                    $day_template->template = $this->template_day_today; 
                }
                else {
                    $day_template->template = $this->template_day; 
                }
                
                // Replace day number with either a static number or a link
                if (count($this->event_array[$day_number]) > 0) {
                    // There's at least one event on this day
                    $day_template->day_number =
                    sprintf(
                        '<a href="?%s&amp;view=day&amp;m=%u&amp;y=%u&amp;d=%u" class="day_number">%u</a>',
                        Page::$url_reference,
                        $this->month, $this->year, $day_number, $day_number
                    );
                    
                    // Loop through day's events and add them in to the template
                    $event_html = null;
                    for ($e = 0; $e < count($this->event_array[$day_number]); $e++) {
                        // Create the link to the event page
                        $event_html .=
                        sprintf(
                            '<a href="?%s&amp;view=event&amp;a=%u" class="calendar_event">',
                            Page::$url_reference,
                            $this->event_array[$day_number][$e]['id']
                        );
                        // Show icon if configured to do so
                        if (SysConfig::get()->getValue('calendar_month_show_cat_icons') == 1) {
                            $event_html .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$this->event_array[$day_number][$e]['cat_image'].'"'
                            .' width="10px" height="10px" alt="'.HTML::schars($this->event_array[$day_number][$e]['cat_label']).'" border="0px" /> ';
                        }
                        // Show event start time if configured to do so
                        if (SysConfig::get()->getValue('calendar_month_show_stime') == 1
                            && $this->event_array[$day_number][$e]['start'] != $this->event_array[$day_number][$e]['end']
                        ) {
                            $event_html .= '<span class="calendar_event_starttime">'.date('g:ia', $this->event_array[$day_number][$e]['start']).'</span>'.SysConfig::get()->getValue('calendar_month_time_sep');
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
        
        $this->template->replaceRange('week', $all_weeks);
    }
    
    public function __toString() 
    {
        return (string)$this->template;
    }
}
