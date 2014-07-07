<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalendarEventPage extends Page {
	private $notification = null;
	private $event;
	
	public function __construct($page_id) {
		parent::__construct($page_id);
		
		$event_id = (empty($_GET['a'])) ? 0 : (int)$_GET['a'];
		try {
			$this->event = new CalEvent($event_id);
		} catch (CalEventException $ex) {
			$this->event = null;
			header('HTTP/1.1 404 Not Found');
			$this->notification = 'The event could not be found.';
			Debug::get()->addMessage($ex->getMessage(), true);
		}
	}
	
	public function getContent() {
		if (!$this->event) {
			return;
		}
		$eventTpl = new Smarty();
			
		if (acl::get()->check_permission('adm_calendar_edit_date')) {
			$editbar = new EditBar();
			$editbar->set_label('Event');
			$editbar->add_control('admin.php?module=calendar_edit_date&id='.$this->event->getId(),
					'edit.png', 'Edit', array('adm_calendar_edit_date','admin_access'));
			$eventTpl->assign('editbar', $editbar);
		}
		$eventTpl->assign('event', $this->event);
		$eventTpl->assign('time_format', get_config('time_format'));
		$eventTpl->assign('show_author', get_config('calendar_show_author'));
		$eventTpl->assign('page', $this);

		return $eventTpl->fetch('calendarEvent.tpl');
	}
	
	public function getNotifications() {
		return $this->notification;
	}
	
	public function getTitle() {
		if ($this->event) {
			return sprintf('%s - %s - %s', $this->record['title'], $this->event->getTitle(), date('M d, Y', $this->event->getStart()));
		} else {
			return $this->record['title'];
		}
	}
}
