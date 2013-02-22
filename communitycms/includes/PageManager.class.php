<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class PageManager {
	private $mId;
	private $mTitle;
	
	public function __construct($id) {
		global $db;
		
		$id = $db->sql_escape_string($id);
		
		$query = 'SELECT `title`
			FROM `'.PAGE_TABLE.'`
			WHERE `id` = '.$id;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new SQLException('Error loading page.');
		if ($db->sql_num_rows($handle) == 0)
			throw new PageException('Page not found.');
		
		$result = $db->sql_fetch_assoc($handle);
		
		$this->mId = $id;
		$this->mTitle = $result['title'];
	}
	
	/**
	 * Delete a page
	 * @global acl $acl
	 * @global db $db
	 * @throws PageException
	 */
	public function delete() {
		global $acl;
		global $db;
		
		if (!$this->mId)
			throw new PageException('Invalid page.');
		if (!$acl->check_permission('page_delete'))
			throw new PageException('You are not allowed to delete pages.');

		// FIXME: Check for content on page before deleting

		// Delete page entry
		$query = 'DELETE FROM `'.PAGE_TABLE.'`
			WHERE `id` = '.$this->mId;
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new PageException('Error deleting page.');
		if ($db->sql_affected_rows($handle) < 1)
			throw new PageException('No pages deleted.');
		
		Log::addMessage('Deleted page \''.$this->mTitle.'\'');
		$this->mId = false;
	}
	
	/**
	 * Set the default page
	 * @global acl $acl
	 * @throws PageException
	 */
	function setHomepage() {
		global $acl;

		if (!$acl->check_permission('page_set_home'))
			throw new PageException('You are not allowed to change the default page.');

		if (!set_config('home', $this->mId))
			throw new PageException('Error setting defualt page.');
		
		Log::addMessage('Set home page to \''.$this->mTitle.'\'');
	}
	
}

?>
