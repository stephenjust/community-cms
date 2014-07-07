<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalendarDayPage extends Page {
	private $year;
	private $month;
	private $day;
	private $events;

	public function __construct($page_id) {
		parent::__construct($page_id);
		
		$this->month = (isset($_GET['m']) && $_GET['m'] >= 0 && $_GET['m'] <= 13) ? (int)$_GET['m'] : date('n');
		$this->year = (isset($_GET['y']) && $_GET['y'] >= 2000 && $_GET['y'] <= 9999) ? (int)$_GET['y'] : date('Y');
		$this->day = (isset($_GET['d']) && $_GET['d'] > 0 && $_GET['d'] <= 31) ? (int)$_GET['d'] : date('j');
		
		$this->events = $this->getEvents();
	}
	
	public function getContent() {
		$tpl = new Smarty();
		$tpl->assign('page', $this);
		$tpl->assign('events', $this->events);
		$tpl->assign('time_format', get_config('time_format'));
		return $tpl->fetch('calendarDay.tpl');
	}
	
	public function getTitle() {
		return sprintf('%s - %s', $this->record['title'], date('M j, Y', mktime(0, 0, 0, $this->month, $this->day, $this->year)));
	}
	
	public function getMonth() {
		return $this->month;
	}
	
	public function getYear() {
		return $this->year;
	}
	
	private function getEvents() {
		$event_day_s = sprintf('%d-%d-%d 00:00:00', $this->year, $this->month, $this->day);
		$event_day_e = sprintf('%d-%d-%d 23:59:59', $this->year, $this->month, $this->day);
		$event_ids = DBConn::get()->query(sprintf('SELECT `id` FROM `%s` '
				. 'WHERE `start` >= :start '
				. 'AND `start` <= :end '
				. 'ORDER BY `start` ASC, `end` DESC', CALENDAR_TABLE),
				array(':start' => $event_day_s, ':end' => $event_day_e),
				DBConn::FETCH_ALL);
		$events = array();
		foreach ($event_ids AS $record) {
			$events[] = new CalEvent($record['id']);
		}
		return $events;
	}
}
