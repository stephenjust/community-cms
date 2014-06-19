<?php

/**
 * Community CMS
 *
 * @copyright Copyright (C) 2012-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/acl/acl.php');
require_once(ROOT.'includes/DBConn.class.php');
require_once(ROOT.'includes/Log.class.php');
require_once(ROOT.'includes/PageUtil.class.php');
require_once(ROOT.'includes/Validate.class.php');

class PageMessage {
	
	private $id = 0;
	private $content;
	private $page_id;
	
	/**
	 * Create a page message record
	 * @param int $page
	 * @param string $content
	 * @param string $start
	 * @param string $end
	 * @param boolean $expire
	 * @return /PageMessage
	 * @throws Exception
	 */
	public static function create($page, $content, $start, $end, $expire) {
		acl::get()->require_permission('page_message_new');
		assert(PageUtil::exists($page), 'Page does not exist.');
		assert(Validate::date($start), 'Invalid start date.');
		assert(Validate::date($end), 'Invalid end date.');
		try {
			DBConn::get()->query(
					sprintf('INSERT INTO `%s`'
							. 'SET `start_date`=:start, `end_date`=:end,'
							. '`end`=:expire, `text`=:content, `page_id`=:page,'
							. '`order`=:order', PAGE_MESSAGE_TABLE),
					array(':start' => $start,
						':end' => $end,
						':expire' => ($expire === true) ? 1 : 0,
						':content' => $content,
						':page' => $page,
						':order' => 0));
			Log::addMessage(sprintf("Created page message for page '%s'", PageUtil::getTitle($page)));
			return new PageMessage(DBConn::get()->lastInsertId());
		} catch (DBException $ex) {
			throw new Exception('An error occurred when creating the page message record.');
		}
	}
	
	/**
	 * Get items attached to the given page
	 * @param int $page_id
	 * @return \PageMessage
	 */
	public static function getByPage($page_id) {
		$results = DBConn::get()->query(sprintf('SELECT `message_id` from `%s`'
				. 'WHERE `page_id` = :page', PAGE_MESSAGE_TABLE),
				array(':page' => $page_id), DBConn::FETCH_ALL);
		$messages = [];
		foreach ($results AS $result) {
			$messages[] = new PageMessage($result['message_id']);
		}
		return $messages;
	}
	
	public function __construct($id) {
		$result = DBConn::get()->query(
				sprintf('SELECT * FROM `%s` WHERE `message_id` = :id', PAGE_MESSAGE_TABLE),
				array(':id' => $id), DBConn::FETCH);
		$this->id = $id;
		$this->content = $result['text'];
		$this->page_id = $result['page_id'];
	}
	
	/**
	 * Delete page message
	 * @throws Exception
	 */
	public function delete() {
		assert($this->id);
		acl::get()->require_permission('page_message_delete');
		try {
			DBConn::get()->query(
					sprintf('DELETE FROM `%s` WHERE `message_id` = :id', PAGE_MESSAGE_TABLE),
					array(':id' => $this->id));
			Log::addMessage(sprintf("Deleted page message on page '%s'", PageUtil::getTitle($this->page_id)));
			$this->id = 0;
		} catch (DBException $ex) {
			throw new Exception('An error occurred while deleting the page message.');
		}
	}
	
	/**
	 * Edit the page message
	 * @param int $page
	 * @param string $content
	 * @param string $start
	 * @param string $end
	 * @param boolean $expire
	 * @throws Exception
	 */
	public function edit($page,$content,$start,$end,$expire) {
		acl::get()->require_permission('page_message_edit');
		assert(PageUtil::exists($page), 'Page does not exist.');
		assert(Validate::date($start), 'Invalid start date.');
		assert(Validate::date($end), 'Invalid end date.');
		
		try {
			DBConn::get()->query(sprintf('UPDATE `%s`'
					. 'SET `start_date`=:start, `end_date`=:end, `end`=:expire,'
					. '`text`=:content WHERE `message_id` = :id', PAGE_MESSAGE_TABLE),
					array(':start' => $start, ':end' => $end,
						':expire' => ($expire === true) ? 1 : 0,
						':content' => $content, ':id' => $this->id));
			Log::addMessage(sprintf("Edited page message for page '%s'", PageUtil::getTitle($page)));
			$this->content = $content;
			$this->page_id = $page;
		} catch (DBException $ex) {
			throw new Exception('An error occurred when updating the page message record.');
		}
	}
	
	/**
	 * Get message ID
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Get content, abbrediated to a number of characters
	 * @param int $len Number of characters to return, default 75.
	 * @return string
	 */
	public function getAbbreviatedContent($len = 75) {
		return truncate(strip_tags($this->content,'<br>'), $len);
	}
	
}
