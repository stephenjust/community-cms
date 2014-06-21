<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/content/CalLocation.class.php');

class CalEvent {
	private $mId;
	private $mExists = false;
	
	private $mTitle;
	private $mDescription;
	private $mStart;
	private $mEnd;
	private $mLocation;
	private $mLocationHide;
	private $mCategory;
	private $mCategoryHide;
	private $mImage;
	private $mHide;
	
	public function __construct($id) {
		global $db;

		if (!is_numeric($id))
			throw new CalEventException('Invalid event ID');
		
		$this->mId = $id;

		// Get event info
		$info_query = 'SELECT `start`, `end`, `header`, `description`, `hidden`,
				`category`, `category_hide`, `image`, `location`, `location_hide`
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
			$this->mCategory = $info['category'];
			$this->mCategoryHide = $info['category_hide'];
			$this->mImage = $info['image'];
			$this->mLocation = $info['location'];
			$this->mLocationHide = $info['location_hide'];
			$this->mHide = $info['hidden'];
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
	
	/**
	 * Edit an event entry
	 * @global acl $acl
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
		global $acl;
		global $db;

		if (!$acl->check_permission('acl_calendar_edit_date'))
			throw new CalEventException('You are not allowed to edit calendar events.');

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
		author='$author',image='$image',hidden='$hide' WHERE id = {$this->mId} LIMIT 1";
		$edit_date = $db->sql_query($edit_date_query);
		if ($db->error[$edit_date] === 1)
			throw new CalEventException('An error occurred while updating the event record.');

		Log::addMessage('Edited date entry on ' . date('Y-m-d', $start) . ' \'' . stripslashes($title) . '\'');
	}

	public function getCategoryHide() {
		return $this->mCategoryHide;
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
	 * @return int
	 * @throws CalEventException
	 */
	public function getCategory() {
		if (!$this->mExists)
			throw new CalEventException('Event does not exist!');
		
		return $this->mCategory;
	}
	
	public function getDescription() {
		return HTML::schars($this->mDescription);
	}
	
	/**
	 * Get event end time
	 * @return int
	 */
	public function getEnd() {
		return strtotime($this->mEnd);
	}
	
	public function getHidden() {
		return $this->mHide;
	}
	
	/**
	 * Get ID
	 * @return int
	 * @throws CalEventException
	 */
	public function getId() {
		if (!$this->mExists)
			throw new CalEventException('Event does not exist!');
		
		return $this->mId;
	}
	
	/**
	 * Get event image
	 * @return string
	 */
	public function getImage() {
		return $this->mImage;
	}
	
	/**
	 * Get event location
	 * @return string
	 */
	public function getLocation() {
		return HTML::schars($this->mLocation);
	}
	
	public function getLocationHide() {
		return $this->mLocationHide;
	}
	
	/**
	 * Get event start time
	 * @return integer
	 */
	public function getStart() {
		return strtotime($this->mStart);
	}
	
	/**
	 * Get title
	 * @return string
	 * @throws CalEventException
	 */
	public function getTitle() {
		if (!$this->mExists)
			throw new CalEventException('Event does not exist!');
		
		return HTML::schars($this->mTitle);
	}
}

class CalEventException extends Exception {}
?>
