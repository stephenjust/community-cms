<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class CalCategory {
	
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
	 * @param integer $id
	 * @throws CalCategoryException
	 */
	function delete($id) {
		global $db;
		global $debug;
		// Validate parameters
		if (!is_numeric($id))
			throw new CalCategoryException('Invalid category ID.');

		$check_if_last_query = 'SELECT * FROM `' . CALENDAR_CATEGORY_TABLE . '` LIMIT 2';
		$check_if_last_handle = $db->sql_query($check_if_last_query);
		if ($db->error[$check_if_last_handle])
			throw new CalCategoryException('Failed to check the number of remaining categories.');
		
		if ($db->sql_num_rows($check_if_last_handle) == 1)
			throw new CalCategoryException('Cannot delete last category.');

		$check_category_query = 'SELECT * FROM `' . CALENDAR_CATEGORY_TABLE . '`
		   WHERE `cat_id` = ' . $id . ' LIMIT 1';
		$check_category_handle = $db->sql_query($check_category_query);
		if ($db->error[$check_category_handle])
			throw new CalCategoryException('Failed to read category information');

		if ($db->sql_num_rows($check_category_handle) != 1)
			throw new CalCategoryException('The category you want to delete does not exist.');
		
		$delete_category_query = 'DELETE FROM `' . CALENDAR_CATEGORY_TABLE . '`
		   WHERE `cat_id` = ' . $id;
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category])
			throw new CalCategoryException('Failed to delete category.');

		$check_category = $db->sql_fetch_assoc($check_category_handle);
		Log::addMessage('Deleted category \''.$check_category['label'].'\'');
	}
	
}

class CalCategoryException extends Exception {}
?>
