<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

// ----------------------------------------------------------------------------

/**
 * Create calendar event entry
 * @global acl $acl
 * @global db $db
 * @param string $title
 * @param string $description
 * @param string $author
 * @param string $start_time
 * @param string $end_time
 * @param string $date
 * @param integer $category
 * @param boolean $category_hide
 * @param string $location
 * @param boolean $location_hide
 * @param string $image
 * @param boolean $hide
 */
function event_create($title,$description,$author,$start_time,$end_time,
		$date,$category,$category_hide, $location,$location_hide,$image,$hide) {
	global $acl;
	global $db;

	if (!$acl->check_permission('date_create'))
		throw new Exception('You are not allowed to create calendar events.');

	// Add location to list of saved locations
	try {
		location_save($location);
	}
	catch (Exception $e) {
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
	if (!preg_match('#^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$#',$date))
		throw new Exception('Your event\'s date was formatted invalidly. It should be in the format dd/mm/yyyy.');
	$event_date_parts = explode('/',$date);
	$year = $event_date_parts[2];
	$month = $event_date_parts[0];
	$day = $event_date_parts[1];

	if ($start_time == "" || $end_time == "" || $year == "" || $title == "") 
		throw new Exception('One or more required fields was left blank.');
	$start_time = parse_time($start_time);
	$end_time = parse_time($end_time);
	if (!$start_time || !$end_time || $start_time > $end_time)
		throw new Exception('Invalid start or end time. Your event cannot end before it begins.');
	
	// Generate start/end dates for new system
	$start = $year.'-'.$month.'-'.$day.' '.$start_time;
	$end = $year.'-'.$month.'-'.$day.' '.$end_time;
	
	// Create event entry
	$create_date_query = 'INSERT INTO ' . CALENDAR_TABLE . '
		(`category`,`category_hide`,`start`,`end`,`header`,
		`description`,`location`,`location_hide`,`author`,`image`,`hidden`)
		VALUES ("'.$category.'","'.(int)$category_hide.'","'.$start.'","'.$end.'","'.$title.'","'.$description.'",
		"'.$location.'","'.(int)$location_hide.'","'.$author.'","'.$image.'",'.$hide.')';
	$create_date = $db->sql_query($create_date_query);
	if ($db->error[$create_date] === 1)
		throw new Exception('An error occurred while creating the calendar event.');

	Log::addMessage('New date entry on '.$day.'/'.$month.'/'
		.$year.' \''.stripslashes($title).'\'');
}

/**
 * Create a calendar event category
 * @global db $db
 * @global Debug $debug
 * @param string $label Name of category
 * @param string $icon Name of PNG icon file (icon-________.png)
 * @param string $description Unused currently
 * @return boolean 
 */
function event_cat_create($label,$icon,$description = NULL) {
	global $db;
	global $debug;

	$label = addslashes($label);
	if (strlen($label) < 1) {
		$debug->addMessage('Category name is too short',true);
		return false;
	}
	if (strlen($icon) < 1) {
		$debug->addMessage('Icon selection is invalid',true);
		return false;
	}
	$query = 'INSERT INTO `'.CALENDAR_CATEGORY_TABLE.'`
		(`label`,`colour`)
		VALUES
		(\''.$label.'\',\''.$icon.'\')';
	$handle = $db->sql_query($query);
	if($db->error[$handle] === 1) {
		$debug->addMessage('Failed to create category',true);
		return false;
	}
	Log::addMessage('Created event category \''.stripslashes($label).'\'');
	return true;
}

/**
 * Save a new location entry if it does not already exist
 * @global Debug $debug
 * @global db $db
 * @param string $location (unescaped)
 * @return void
 * @throws Exception 
 */
function location_save($location) {
	global $debug;
	global $db;

	if (get_config('calendar_save_locations') != 1)
		return;

	if (!isset($location) || strlen($location) < 2) {
		$debug->addMessage('No location given',false);
		return;
	}
	$location = $db->sql_escape_string($location);

	// Check if the given location is already in the database
	$check_dupe_query = 'SELECT `value` FROM `'.LOCATION_TABLE.'`
		WHERE `value` = \''.$location.'\'';
	$check_dupe_handle = $db->sql_query($check_dupe_query);
	if ($db->error[$check_dupe_handle] === 1)
		throw new Exception('An error occurred when reading the list of saved locations.');
	if ($db->sql_num_rows($check_dupe_handle) == 1)
		return;

	// Create new location entry
	$new_loc_query = 'INSERT INTO `'.LOCATION_TABLE.'`
		(`value`) VALUES (\''.$location.'\')';
	$new_loc_handle = $db->sql_query($new_loc_query);
	if ($db->error[$new_loc_handle] === 1)
		throw new Exception('An error occurred while attempting to save a new location entry.');

	Log::addMessage('Created new location \''.stripslashes($location).'\'.');
}

// ----------------------------------------------------------------------------

/**
 * Delete a calendar category entry
 * @global db $db
 * @global Debug $debug
 * @param integer $id
 * @return boolean
 */
function event_cat_delete($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->addMessage('Invalid ID given',true);
		return false;
	}

	$check_if_last_query = 'SELECT * FROM `'.CALENDAR_CATEGORY_TABLE.'` LIMIT 2';
	$check_if_last_handle = $db->sql_query($check_if_last_query);
	if ($db->error[$check_if_last_handle] === 1) {
		$debug->addMessage('Failed to check if you are trying to delete the last category',false);
		return false;
	}
	if ($db->sql_num_rows($check_if_last_handle) == 1) {
		$debug->addMessage('Cannot delete last entry',true);
		return false;
	}

	$check_category_query = 'SELECT * FROM `'. CALENDAR_CATEGORY_TABLE .'`
		WHERE `cat_id` = '.$id.' LIMIT 1';
	$check_category_handle = $db->sql_query($check_category_query);
	if ($db->error[$check_category_handle] === 1) {
		$debug->addMessage('Failed to read category information. Does it exist?',false);
		return false;
	}
	if ($db->sql_num_rows($check_category_handle) == 1) {
		$delete_category_query = 'DELETE FROM `'.CALENDAR_CATEGORY_TABLE.'`
			WHERE `cat_id` = '.$id;
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category] === 1) {
			$debug->addMessage('Failed to perform delete operation',true);
			return false;
		} else {
			$check_category = $db->sql_fetch_assoc($check_category_handle);
			Log::addMessage('Deleted category \''.$check_category['label'].'\'');
			return true;
		}
	} else {
		return false;
	}
}

// ----------------------------------------------------------------------------

/**
 * Edit an event entry
 * @global acl $acl
 * @global db $db
 * @param integer $id
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
 * @throws Exception 
 */
function event_edit($id,$title,$description,$author,$start,$end,$category,$category_hide,$location,$location_hide,$image,$hide) {
	global $acl;
	global $db;

	if (!$acl->check_permission('acl_calendar_edit_date'))
		throw new Exception('You are now allowed to edit calendar events.');

	// Sanitize Inputs
	$id = (int)$id;
	$title = $db->sql_escape_string(htmlspecialchars(strip_tags($title)));
	$description = $db->sql_escape_string(remove_comments($description));
	$author = $db->sql_escape_string(htmlspecialchars(strip_tags($author)));
	$category = (int)$category;
	$start = strtotime($start);
	$end = strtotime($end);
	$category_hide = ($category_hide == true) ? 1 : 0;
	$location = $db->sql_escape_string(htmlspecialchars(strip_tags($location)));
	$location_hide = ($location_hide == true) ? 1 : 0;
	$image = $db->sql_escape_string(htmlspecialchars(strip_tags($image)));
	$hide = ($hide === true) ? 1 : 0;
	
	location_save(stripslashes($location));

	// Legacy date calculations (to be removed)
	$year = date('Y',$start);
	$month = date('m',$start);
	$day = date('d',$start);

	// Generate new start/end string
	$start_t = date('Y-m-d H:i:s',$start);
	$end_t = date('Y-m-d H:i:s',$end);
	$start_time = date('H:i:s',$start);
	$end_time = date('H:i:s',$end);

	$edit_date_query = 'UPDATE ' . CALENDAR_TABLE . "
		SET `category`='$category', `category_hide`='$category_hide',
		`start`='$start_t', `end`='$end_t',
		`header`='$title', `description`='$description',
		`location`='$location', `location_hide`='$location_hide',
		author='$author',image='$image',hidden='$hide' WHERE id = $id LIMIT 1";
	$edit_date = $db->sql_query($edit_date_query);
	if ($db->error[$edit_date] === 1)
		throw new Exception('An error occurred while updating the event record.');

	Log::addMessage('Edited date entry on '.date('Y-m-d',$start).' \''.stripslashes($title).'\'');
}

/**
 * Get information of an event
 * @global db $db
 * @param integer $id
 * @return array
 * @throws Exception 
 */
function event_get($id) {
	global $db;

	// Sanitize inputs
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('An invalid event ID was given.');

	// Get event record
	$query = 'SELECT `id`,`category`,`category_hide`,`start`,`end`,`header`,
		`description`,`location`,`location_hide`,`author`,`image`,`hidden`
		FROM `'.CALENDAR_TABLE.'`
		WHERE `id` = '.$id.' LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1)
		throw new Exception('Failed to retrieve calendar event.');
	if ($db->sql_num_rows($handle) == 0)
		throw new Exception('The requested event does not exist.');

	$event = $db->sql_fetch_assoc($handle);
	return $event;
}

// ----------------------------------------------------------------------------

// FIXME: This doesn't work yet
function monthcal_get_date($day,$month,$year,$template) {
	global $calendar_settings;
	global $db;
	global $debug;

	$dates_query = 'SELECT * FROM `' . CALENDAR_TABLE . '` `date`,
		`' . CALENDAR_CATEGORY_TABLE . '` `cat`
		WHERE `date`.`month` = \''.$month.'\' AND `date`.`year` = \''.$year.'\'
		AND `date`.`day` = \''.$day.'\' AND `date`.`category` =
		`cat`.`cat_id` ORDER BY `starttime` ASC';
	$dates_handle = $db->sql_query($dates_query);
	unset($dates_query);

	if ($db->error[$dates_handle] === 1) {
		$debug->addMessage('Failed to read date information',true);
		return 'Error';
	}
	if ($db->sql_num_rows($dates_handle) > 0) {
		$template->day_number = HTML::link('index.php?'.Page::$url_reference.'&view=day&m='.$month.'&y='.$year.'&d='.$day, $day, 'day_number');
	} else {
		$template->day_number = $day;
	}
	$dates = NULL;
	for ($i = 1; $i <= $db->sql_num_rows($dates_handle); $i++) {
		$day_info = $db->sql_fetch_assoc($dates_handle);
		if($day_info['colour'] == '') {
			$day_info['colour'] = 'red';
		}
		$dates .= '<a href="?'.Page::$url_reference.'&amp;view=event&amp;'
			.'a='.$day_info['id'].'" class="calendar_event">';
		if ($calendar_settings['month_show_cat_icons'] == 1) {
			$dates .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png"'
			.' width="10px" height="10px" alt="'.$day_info['label'].'" border="0px" />';
		}
		if ($calendar_settings['month_show_stime'] == 1) {
			$stime_tmp = explode(':',$day_info['starttime']);
			$stime_tmp = mktime($stime_tmp[0],$stime_tmp[1]);
			$dates .= '<span class="calendar_event_starttime">'.date('g:ia',$stime_tmp).'</span> ';
		}
		$dates .= $day_info['header'].'</a><br />'."\n";
	}
	$template->day_events = $dates;
	return (string)$template;
}

function monthcal_day_strings($template) {
	if (!is_object($template)) {
		return $template;
	}

	// Insert date labels
	// Settings:
	// calendar_month_day_format
	// 1 - Use full name
	// 0 - Use abbreviation
	if (get_config('calendar_month_day_format') == 1) {
		$template->cal_sunday = 'Sunday';
		$template->cal_monday = 'Monday';
		$template->cal_tuesday = 'Tuesday';
		$template->cal_wednesday = 'Wednesday';
		$template->cal_thursday = 'Thursday';
		$template->cal_friday = 'Friday';
		$template->cal_saturday = 'Saturday';
	} else {
		$template->cal_sunday = 'Sun';
		$template->cal_monday = 'Mon';
		$template->cal_tuesday = 'Tues';
		$template->cal_wednesday = 'Wed';
		$template->cal_thursday = 'Thurs';
		$template->cal_friday = 'Fri';
		$template->cal_saturday = 'Sat';
	}
	return $template;
}
?>
