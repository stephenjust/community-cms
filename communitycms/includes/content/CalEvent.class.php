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
