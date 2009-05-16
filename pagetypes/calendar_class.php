<?php
/**
 * Description of calendar_class
 *
 * @author stephen
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
        global $db;
        global $page;
        global $page_info;
        global $site_info;
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
            $event_time = date('g:ia -',$event_start).date(' g:ia',$event_end)."<br />".date(' l, F j Y',$event_start);
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
        $template_event->event_category = stripslashes($event_info['label']);
        $template_event->event_description = stripslashes($event_info['description']);
        $template_event->event_location = stripslashes($event_info['location']);
        $this->event_text .= "<a href='?".$page->url_reference."&m=".
            $event_info['month']."&y=".$event_info['year']."'>Back to month
            view</a><br />";
        $this->event_text .= "<a href='?".$page->url_reference."&view=day&d=".
            $event_info['day']."&m=".$event_info['month']."&y=".$event_info['year'].
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
