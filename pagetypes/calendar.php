<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

namespace CommunityCMS;

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
require_once ROOT.'pagetypes/calendar_class.php';
/**#@-*/
if ($view == null) {
    $view = SysConfig::get()->getValue('calendar_defualt_view');
}
if ($view != 'month' && $view != 'day' && $view != 'event') {
    $view = SysConfig::get()->getValue('calendar_default_view');
}

switch ($view) {
    // MONTH VIEW
case "month":
    $month_cal = new calendar_month($month, $year);
    $month_cal->setup();
    $page_content = (string)$month_cal;

    // Add month and year to page title
    Page::$title .= ' - '.date('F Y', $month_cal->first_day_ts);
    break;

// ----------------------------------------------------------------------------

// EVENT VIEW
case "event":
    $page_content = null;
    if (!isset($_GET['a'])) {
        $_GET['a'] = null;
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
    if ($year < 2000 || $year > 9999) { $year = 2000; 
    } // Validate month and year values
    if ($month < 1 || $month > 12) { $month = 1; 
    }
    if ($day < 1 || $day > 31) { $day = 1; 
    }
    $page_content = null;
    // Get events for current day from database
    $event_day_s = gmmktime(0, 0, 0, $month, $day, $year);
    $event_day_e = gmmktime(23, 59, 59, $month, $day, $year);
    $events = CalEvent::getRange($event_day_s, $event_day_e);
    $page_content .= HTML::link(
        sprintf(
            '?%s&view=month&m=%u&y=%u',
            Page::$url_reference, $month, $year
        ), 'Back to month view'
    ).'<br />';
    if (count($events) == 0) {
        header('HTTP/1.1 404 Not Found');
        $page_content .= 'There are no events to display.';
        break;
    }
    $day_template = new Template;
    $day_template->loadFile('calendar_day');
    $day_template->day_heading = gmdate('l, F j', $event_day_s);
    $event_template = new Template;
    $event_template->path = $day_template->path;
    $event_template->template = $day_template->getRange('event');
    $day_template->replaceRange('event', '<!-- $EVENT$ -->');

    $event_rows = null;
    foreach ($events as $event) {
        $event_start = $event->getStart();
        $event_end = $event->getEnd();
        if ($event_start == $event_end) {
            $event_time = 'All day';
        } else {
            $event_time = date(SysConfig::get()->getValue('time_format'), $event_start).' - '.
            date(SysConfig::get()->getValue('time_format'), $event_end);
        }
        $current_event = clone $event_template;
        $current_event->event_id = $event->getId();
        $current_event->event_time = $event_time;
        $current_event->event_start_date = date('Y-m-d', $event_start);
        $current_event->event_heading = $event->getTitle();
        $current_event->event_description = StringUtils::ellipsize(strip_tags($event->getDescription()), 100);
        $event_rows .= (string)$current_event;
    }
    $day_template->event = $event_rows;
    $page_content .= $day_template;
    $month_text = date('F', $event_start);
    Page::$title .= ' - '.$month_text.' '.$day.', '.$year;
    break;
}
return $page_content;
