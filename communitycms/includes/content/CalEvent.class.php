<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalEvent {
	private $mId;
	private $mExists = false;
	
	private $mTitle;
	private $mDescription;
	private $mStart;
	private $mEnd;
	private $mLocation;
	
	public function __construct($id) {
		global $db;

		if (!is_numeric($id))
			throw new CalEventException('Invalid event ID');
		
		$this->mId = $id;

		// Get event info
		$info_query = 'SELECT `start`, `end`, `header`, `description`
			FROM `'.CALENDAR_TABLE.'`
			WHERE `id` = '.$id.'
			LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1)
			throw new CalEventException('Failed to access event database.');
		if ($db->sql_num_rows($info_handle) != 0) {
			$this->mExists = true;
			$info = $db->sql_fetch_assoc($info_handle);
			$this->mTitle = $info['header'];
			$this->mDescription = $info['description'];
			$this->mStart = $info['start'];
			$this->mEnd = $info['end'];
		}
	}

	/**
	 * Create calendar event entry
	 * @global type $acl
	 * @global db $db
	 * @param string $title
	 * @param string $description
	 * @param string $author
	 * @param string $start_time
	 * @param string $end_time
	 * @param string $date
	 * @param int $category
	 * @param boolean $category_hide
	 * @param string $location
	 * @param boolean $location_hide
	 * @param int $image
	 * @param boolean $hide
	 * @return \CalEvent
	 * @throws CalEventException
	 */
	public static function create($title, $description, $author, $start_time, $end_time, $date, $category, $category_hide, $location, $location_hide, $image, $hide) {
		global $acl;
		global $db;

		if (!$acl->check_permission('date_create'))
			throw new CalEventException('You are not allowed to create calendar events.');

		// Add location to list of saved locations
		try {
			CalLocation::save($location);
		} catch (CalLocationException $e) {
			
		}

		// Sanitize inputs
		$location = $db->sql_escape_string(strip_tags($location));
		$title = $db->sql_escape_string(strip_tags($title));
		$description = $db->sql_escape_string(remove_comments($description));
		$author = $db->sql_escape_string(strip_tags($author));

		// Determine date
		if ($date == '') {
			$date = date('d/m/Y');
		}
		if (!preg_match('#^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$#', $date))
			throw new CalEventException('Your event\'s date was formatted invalidly. It should be in the format dd/mm/yyyy.');
		$event_date_parts = explode('/', $date);
		$year = $event_date_parts[2];
		$month = $event_date_parts[0];
		$day = $event_date_parts[1];

		if ($start_time == "" || $end_time == "" || $year == "" || $title == "")
			throw new CalEventException('One or more required fields was left blank.');
		$start_time = parse_time($start_time);
		$end_time = parse_time($end_time);
		if (!$start_time || !$end_time || $start_time > $end_time)
			throw new CalEventException('Invalid start or end time. Your event cannot end before it begins.');

		// Generate start/end dates for new system
		$start = $year . '-' . $month . '-' . $day . ' ' . $start_time;
		$end = $year . '-' . $month . '-' . $day . ' ' . $end_time;

		// Create event entry
		$create_date_query = 'INSERT INTO `'.CALENDAR_TABLE.'`
		(`category`,`category_hide`,`start`,`end`,`header`,
		`description`,`location`,`location_hide`,`author`,`image`,`hidden`)
		VALUES ("' . $category . '","' . (int) $category_hide . '","' . $start . '","' . $end . '","' . $title . '","' . $description . '",
		"' . $location . '","' . (int) $location_hide . '","' . $author . '","' . $image . '",' . $hide . ')';
		$create_date = $db->sql_query($create_date_query);
		if ($db->error[$create_date] === 1)
			throw new CalEventException('An error occurred while creating the calendar event.');
		$insert_id = $db->sql_insert_id(CALENDAR_TABLE, 'id');

		Log::addMessage('New date entry on ' . $day . '/' . $month . '/'
				. $year . ' \'' . stripslashes($title) . '\'');
		
		return new CalEvent($insert_id);
	}
	
	/**
	 * Delete calendar event
	 * @global db $db
	 * @throws CalEventException
	 */
	function delete() {
		global $db;
		
		if (!$this->mExists)
			throw new CalEventException('Event does not exist.');

		$query = 'DELETE FROM `'.CALENDAR_TABLE.'`
		   WHERE `id` = '.$this->mId;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new CalEventException('Failed to delete event.');

		Log::addMessage('Deleted calendar date \''.$this->mTitle.'\'');
		$this->mExists = false;
	}

}

class CalEventException extends Exception {}
?>
