<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class Newsletter {
	private $mId;
	private $mExists = false;
	
	private $mPage;
	private $mYear;
	private $mMonth;
	private $mLabel;
	private $mPath;
	private $mHidden;
	
	public function __construct($id) {
		global $db;

		if (!is_numeric($id))
			throw new NewsletterException('Invalid newsletter ID');
		
		$this->mId = $id;

		// Get newsletter info
		$info_query = 'SELECT `page`, `year`, `month`, `label`, `path`, `hidden`
			FROM `'.NEWSLETTER_TABLE.'` WHERE
			`id` = '.$id.'
			LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1)
			throw new NewsletterException('Failed to access newsletter database.');
		if ($db->sql_num_rows($info_handle) != 0) {
			$this->mExists = true;
			$info = $db->sql_fetch_assoc($info_handle);
			$this->mPage = $info['page'];
			$this->mYear = $info['year'];
			$this->mMonth = $info['month'];
			$this->mLabel = $info['label'];
			$this->mPath = $info['path'];
			$this->mHidden = $info['hidden'];
		}
	}

	/**
	 * Delete newsletter entry from the database
	 * @global acl $acl Permission object
	 * @global db $db Database connection object
	 * @param integer $id Newsletter ID
	 * @throws NewsletterException
	 */
	public function delete() {
		global $acl;
		global $db;

		// Make sure entry exists
		if (!$this->mExists)
			throw new NewsletterException('Newsletter does not exist.');
		
		// Check permission
		if (!$acl->check_permission('newsletter_delete'))
			throw new NewsletterException('You are not allowed to delete newsletters.');

		// Delete newsletter entry
		$delete_query = 'DELETE FROM `'.NEWSLETTER_TABLE.'`
			WHERE `id` = '.$this->mId;
		$delete = $db->sql_query($delete_query);
		if($db->error[$delete])
			throw new NewsletterException('An error occurred when deleting the newsletter entry.');

		Log::addMessage('Deleted newsletter \''.$this->mLabel.'\'');
		$this->mExists = false;
	}

	/**
	 * Create a newsletter record
	 * @global acl $acl
	 * @global db $db
	 * @param string $entry_name
	 * @param string $entry_file
	 * @param integer $page Numeric Page ID
	 * @param integer $year
	 * @param integer $month
	 * @throws NewsletterException 
	 * @return Newsletter Newsletter instance for created item
	 */
	public static function create($entry_name,$entry_file,$page,$year,$month) {
		global $acl;
		global $db;

		// Check permissions
		if (!$acl->check_permission('newsletter_create'))
			throw new NewsletterException('You are not allowed to create newsletters.');

		// Sanitize inputs
		$entry_name = $db->sql_escape_string($entry_name);
		$entry_file = $db->sql_escape_string($entry_file);
		$page = (int)$page;
		$year = (int)$year;
		$month = (int)$month;
		if (strlen($entry_name) == 0)
			throw new NewsletterException('No label was given for the newsletter.');
		if (strlen($entry_file) <= 3)
			throw new NewsletterException('No file was selected for the newsletter.');
		if ($month > 12 || $month < 1)
			throw new NewsletterException('An invalid month was selected for the newsletter.');
		if ($year > 3000 || $year < 1000)
			throw new NewsletterException('An invalid year was selected for the newsletter.');

		// Validate the newsletter page
		// FIXME: This should be done with page class
		$page_query = 'SELECT `title` FROM `'.PAGE_TABLE.'`
			WHERE `id` = '.$page.' LIMIT 1';
		$page_handle = $db->sql_query($page_query);
		if ($db->error[$page_handle] === 1) 
			throw new NewsletterException('An error occurred when validating the given page information.');
		if ($db->sql_num_rows($page_handle) === 0)
			throw new NewsletterException('The page given for the newsletter does not exist.');
		$page_title = $db->sql_fetch_assoc($page_handle);

		// Create the new newsletter record
		$new_query = 'INSERT INTO `'.NEWSLETTER_TABLE."`
			(`label`,`page`,`year`,`month`,`path`) VALUES
			('$entry_name',".$page.",".$year.",".$month.",'".$entry_file."')";
		$new = $db->sql_query($new_query);
		if ($db->error[$new] === 1)
			throw new NewsletterException('An error occurred when creating the newsletter.');
		$insert_id = $db->sql_insert_id(NEWSLETTER_TABLE, 'id');

		// Create the log entry
		Log::addMessage('Newsletter \''.$entry_name.'\' added to page '.$page_title);
		
		return new Newsletter($insert_id);
	}
}

class NewsletterException extends Exception {}
?>
