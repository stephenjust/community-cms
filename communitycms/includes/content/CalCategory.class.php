<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalCategory {
	private $mId;
	private $mName;
	private $mIcon;
	
	/**
	 * Create a new CalCategory instance
	 * @global db $db
	 * @param int $id
	 * @throws CalCategoryException
	 */
	public function __construct($id) {
		global $db;

		$query = 'SELECT `label`, `colour`
			FROM `'.CALENDAR_CATEGORY_TABLE.'`
			WHERE `cat_id` = '.(int)$id;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new CalCategoryException('Error loading category.');
		if ($db->sql_num_rows($handle) == 0)
			throw new CalCategoryException('Category does not exist.');
		$result = $db->sql_fetch_assoc($handle);
		
		$this->mId = (int)$id;
		$this->mName = $result['label'];
		$this->mIcon = $result['colour'];
	}
	
	/**
	 * Create a calendar event category
	 * @global db $db
	 * @param string $label Name of category
	 * @param string $icon Name of PNG icon file (icon-________.png)
	 * @throws CalCategoryException
	 */
	public static function create($label, $icon) {
		global $db;

		$label = $db->sql_escape_string($label);
		$icon = $db->sql_escape_string($icon);
		if (strlen($label) == 0)
			throw new CalCategoryException('Category name is too short.');
		
		if (strlen($icon) == 0)
			throw new CalCategoryException('Icon selection is invalid.');
		
		$query = 'INSERT INTO `'.CALENDAR_CATEGORY_TABLE."`
			(`label`,`colour`)
			VALUES
			('$label', '$icon')";
		$handle = $db->sql_query($query);
		if($db->error[$handle] === 1)
			throw new CalCategoryException('Failed to create category.');
		
		Log::addMessage('Created event category \''.stripslashes($label).'\'');
	}
	
	/**
	 * Delete a calendar category entry
	 * @global db $db
	 * @throws CalCategoryException
	 */
	function delete() {
		global $db;

		if (CalCategory::count() == 1)
			throw new CalCategoryException('Cannot delete last category.');
		
		$delete_category_query = 'DELETE FROM `'.CALENDAR_CATEGORY_TABLE.'`
		   WHERE `cat_id` = '.$this->mId;
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category])
			throw new CalCategoryException('Failed to delete category.');

		Log::addMessage('Deleted category \''.$this->mName.'\'');
		$this->mId = NULL;
	}
	
	/**
	 * Get the number of calendar categories
	 * @global db $db
	 * @return int
	 * @throws CalCategoryException
	 */
	public static function count() {
		global $db;

		$query = 'SELECT COUNT(*)
			FROM `'.CALENDAR_CATEGORY_TABLE.'`';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new CalCategoryException('Error counting records.');
		$result = $db->sql_fetch_row($handle);
		return $result[0];
	}
	
	/**
	 * Get category icon file name
	 * @return string
	 */
	public function getIcon() {
		return HTML::schars($this->mIcon);
	}
	
	/**
	 * Get name of category
	 * @return string
	 */
	public function getName() {
		return HTML::schars($this->mName);
	}
}

class CalCategoryException extends Exception {}
?>
