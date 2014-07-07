<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalEvent {
	private $id = 0;
	private $title;
	private $description;
	private $start_time;
	private $end_time;
	private $location;
	private $category;
	private $author;
	private $image;
	private $publish;
	private $location_hidden;
	private $category_hidden;

	/**
	 * Create calendar event entry
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
	public static function create($title, $description, $author, $start_time,
			$end_time, $date, $category, $category_hide, $location, $location_hide, $image, $hide) {
		acl::get()->require_permission('date_create');
		if (!$title) {
			throw new CalEventException('Event heading must not be blank.');
		}
		$start = CalEvent::convertInputToDatetime($date, $start_time);
		$end = CalEvent::convertInputToDatetime($date, $end_time);
		if (strtotime($start) > strtotime($end)) {
			throw new CalEventException('Invalid start or end time. Your event cannot end before it begins.');
		}
		
		try {
			DBConn::get()->query(sprintf('INSERT INTO `%s` '
					. '(`category`, `category_hide`, `start`, `end`, `header`, '
					. '`description`, `location`, `location_hide`, `author`, `image`, `hidden`) VALUES '
					. '(:category, :category_hide, :start, :end, :header, '
					. ':description, :location, :location_hide, :author, :image, :hide)', CALENDAR_TABLE),
					array(':category' => $category,
						':category_hide' => (int) $category_hide,
						':start' => $start, ':end' => $end,
						':header' => $title,
						':description' => remove_comments($description),
						':location' => $location,
						':location_hide' => (int) $location_hide,
						':author' => $author, ':image' => $image,
						':hide' => (int) $hide));
		
			try {
				CalLocation::save($location);
			} catch (CalLocationException $e) {
				Debug::get()->addMessage('Failed to save event location.');
			}
			$insert_id = DBConn::get()->lastInsertId();
			Log::addMessage(sprintf("New date entry on %s, '%s'", date('d/m/Y', strtotime($start)), $title));
			return new CalEvent($insert_id);
		} catch (DBException $ex) {
			echo $ex;
			throw new CalEventException('Failed to create event.');
		}
	}
	
	/**
	 * Create CalEvent instance
	 * @param int $id
	 * @throws CalEventException
	 */
	public function __construct($id) {
		assert(is_numeric($id));
		
		$result = DBConn::get()->query(sprintf('SELECT * FROM `%s` WHERE `id` = :id', CALENDAR_TABLE),
				array(':id' => $id), DBConn::FETCH);
		if (!$result) {
			throw new CalEventException('Event not found.');
		}

		$this->id = $id;
		$this->title = $result['header'];
		$this->description = $result['description'];
		$this->start_time = $result['start'];
		$this->end_time = $result['end'];
		$this->category = $result['category'];
		$this->category_hidden = $result['category_hide'];
		$this->image = $result['image'];
		$this->location = $result['location'];
		$this->location_hidden = $result['location_hide'];
		$this->publish = !$result['hidden'];
		$this->author = $result['author'];
	}

	/**
	 * Delete calendar event
	 * @throws CalEventException
	 */
	function delete() {
		assert($this->id);
		try {
			DBConn::get()->query(sprintf('DELETE FROM `%s` WHERE `id` = :id',
					CALENDAR_TABLE),
					array(':id' => $this->id));
			Log::addMessage(sprintf("Deleted calendar date '%s'.", $this->title));
			$this->id = 0;
		} catch (DBException $ex) {
			throw new CalEventException('Failed to delete event.');
		}
	}
	
	/**
	 * Edit an event entry
	 * @global db $db
	 * @param string $title
	 * @param string $description
	 * @param string $author
	 * @param date $start
	 * @param date $end
	 * @param integer $category
	 * @param boolean $category_hide
	 * @param string $location
	 * @param boolean $location_hide
	 * @param string $image
	 * @param boolean $hide
	 * @throws CalEventException 
	 */
	public function edit($title, $description, $author, $start, $end, $category, $category_hide, $location, $location_hide, $image, $hide) {
		global $db;
		acl::get()->require_permission('acl_calendar_edit_date');

		// Sanitize Inputs
		$title = $db->sql_escape_string(htmlspecialchars(strip_tags($title)));
		$description = $db->sql_escape_string(remove_comments($description));
		$author = $db->sql_escape_string(htmlspecialchars(strip_tags($author)));
		$category = (int) $category;
		$start = strtotime($start);
		$end = strtotime($end);
		$category_hide = ($category_hide == true) ? 1 : 0;
		$location = $db->sql_escape_string(htmlspecialchars(strip_tags($location)));
		$location_hide = ($location_hide == true) ? 1 : 0;
		$image = $db->sql_escape_string(htmlspecialchars(strip_tags($image)));
		$hide = ($hide === true) ? 1 : 0;

		CalLocation::save(stripslashes($location));

		// Generate new start/end string
		$start_t = date('Y-m-d H:i:s', $start);
		$end_t = date('Y-m-d H:i:s', $end);

		$edit_date_query = 'UPDATE ' . CALENDAR_TABLE . "
		SET `category`='$category', `category_hide`='$category_hide',
		`start`='$start_t', `end`='$end_t',
		`header`='$title', `description`='$description',
		`location`='$location', `location_hide`='$location_hide',
		author='$author',image='$image',hidden='$hide' WHERE id = {$this->id} LIMIT 1";
		$edit_date = $db->sql_query($edit_date_query);
		if ($db->error[$edit_date] === 1)
			throw new CalEventException('An error occurred while updating the event record.');

		Log::addMessage('Edited date entry on ' . date('Y-m-d', $start) . ' \'' . stripslashes($title) . '\'');
	}

	public function getCategoryHide() {
		return $this->category_hidden;
	}
	
	/**
	 * Get list of event categories
	 * @global db $db
	 * @return array
	 * @throws CalEventException
	 */
	public static function getCategoryList() {
		global $db;

		$query = 'SELECT `cat_id` AS `id`,`label`
			FROM `'.CALENDAR_CATEGORY_TABLE.'`
			ORDER BY `cat_id` ASC';
		$handle = $db->sql_query($query);
		if ($db->error[$handle])
			throw new CalEventException('Error reading category list.');
		
		$categories = array();
		for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
			$result = $db->sql_fetch_assoc($handle);
			$categories[] = $result;
		}
		
		return $categories;
	}
	
	/**
	 * Get category
	 * @return string
	 */
	public function getCategory() {
		$cat = new CalCategory($this->category);
		return $cat->getName();
	}
	
	public function getCategoryID() {
		return $this->category;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getTruncatedDescription($length = 100) {
		return truncate(strip_tags($this->description), $length);
	}
	
	/**
	 * Get event end time
	 * @return int
	 */
	public function getEnd() {
		return strtotime($this->end_time);
	}
	
	public function getHidden() {
		return !$this->publish;
	}
	
	public function getAuthor() {
		return $this->author;
	}
	
	public function isAllDay() {
		return ($this->start_time == $this->end_time);
	}
	
	/**
	 * Get ID
	 * @return int
	 * @throws CalEventException
	 */
	public function getId() {		
		return $this->id;
	}
	
	/**
	 * Get event image
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}
	
	/**
	 * Get event location
	 * @return string
	 */
	public function getLocation() {
		return HTML::schars($this->location);
	}
	
	public function getLocationHide() {
		return $this->location_hidden;
	}
	
	/**
	 * Get event start time
	 * @return integer
	 */
	public function getStart() {
		return strtotime($this->start_time);
	}
	
	/**
	 * Get title
	 * @return string
	 * @throws CalEventException
	 */
	public function getTitle() {
		return HTML::schars($this->title);
	}

	/**
	 * Convert user input to a MySQL compatible datetime string.
	 * @param string $date_string
	 * @param string $time_string
	 * @return string
	 * @throws CalEventException
	 */
	private static function convertInputToDatetime($date_string, $time_string) {
		if (!$date_string) {
			$date_string = date('d/m/Y');
		}
		if (!preg_match('#^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$#', $date_string)) {
			throw new CalEventException('Your event\'s date was formatted invalidly. It should be in the format dd/mm/yyyy.');
		}
		$event_date_parts = explode('/', $date_string);
		$year = $event_date_parts[2];
		$month = $event_date_parts[0];
		$day = $event_date_parts[1];

		$time = parse_time($time_string);

		return sprintf('%d-%d-%d %s', $year, $month, $day, $time);
	}
}

class CalEventException extends Exception {}
