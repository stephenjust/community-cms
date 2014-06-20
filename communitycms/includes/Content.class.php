<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/DBConn.class.php');

class Content {
	private $id = 0;
	private $page_id;
	private $title;
	private $content;
	private $author;
	private $date;
	private $date_edited;
	private $image;
	private $publish;
	private $priority;
	private $show_date;
	private $delete_date;
	
	/**
	 * Get all of the content items on a page
	 * @param int $page_id
	 * @param int $start
	 * @param int $num
	 * @return \Content
	 */
	public static function getByPage($page_id, $start = 0, $num = 0, $only_published = false) {
		if ($start < 0) { $start = 0; }
		$query = sprintf('SELECT `id` FROM `%s` '
				. 'WHERE `page` = :page %s ORDER BY `priority` DESC, `date` DESC, `id` DESC %s OFFSET %d',
				NEWS_TABLE, ($only_published) ? 'AND `publish` = 1' : null,
				($num) ? 'LIMIT '.$num : null, $start);
		$results = DBConn::get()->query($query,
				array(':page' => $page_id), DBConn::FETCH_ALL);
		$items = array();
		foreach ($results AS $result) {
			$items[] = new Content($result['id']);
		}
		return $items;
	}
	
	public static function getPublishedByPage($page_id, $start = 0, $num = 0) {
		return Content::getByPage($page_id, $start, $num, true);
	}
	
	public static function getContentIDsByPage($page_id, $only_published = false) {
		$query = sprintf('SELECT `id` FROM `%s` '
				. 'WHERE `page` = :page %s ORDER BY `priority` DESC, `date` DESC, `id` DESC',
				NEWS_TABLE, ($only_published) ? 'AND `publish` = 1' : null);
		$results = DBConn::get()->query($query,
				array(':page' => $page_id), DBConn::FETCH_ALL);
		$items = array();
		foreach ($results AS $result) {
			$items[] = $result['id'];
		}
		return $items;
	}
	
	public function __construct($id) {
		$result = DBConn::get()->query(sprintf('SELECT * FROM `%s` WHERE `id` = :id', NEWS_TABLE),
				array(':id' => $id), DBConn::FETCH);
		if (!$result) {
			throw new ContentNotFoundException('Content not found');
		}
		$this->id = $id;
		$this->page_id = $result['page'];
		$this->title = $result['name'];
		$this->content = $result['description'];
		$this->author = $result['author'];
		$this->date = $result['date'];
		$this->date_edited = $result['date_edited'];
		$this->image = $result['image'];
		$this->publish = $result['publish'];
		$this->priority = $result['priority'];
		$this->show_date = $result['showdate'];
		$this->delete_date = $result['delete_date'];
	}
	
	/**
	 * Get content ID
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Get page ID
	 * @return int
	 */
	public function getPage() {
		return $this->page_id;
	}
	
	/**
	 * True if the item is published
	 * @return boolean
	 */
	public function published() {
		return (boolean) $this->publish;
	}
}

class ContentNotFoundException extends Exception {}
