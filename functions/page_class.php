<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * generate a page
 *
 * TODO: This class does ...
 *
 * @author stephen
 */
class page {
	/**
	 * Unique identifier for page
	 * @var int Page ID
	 */
	public $id = 0;
	/**
	 * Marker that records whether the page exists or not
	 * @var bool True if page exists, false if it does not.
	 */
	public $exists = 0;
	/**
	 * Unique string identifier for page
	 * @var string Page Text ID
	 */
	public $text_id = NULL;
	/**
	 * Notification to display in the notification area of the page
	 * @var string
	 */
	public $notification = '';
	/**
	 * How scripts should reference the page
	 * @var string Either Text ID or ID
	 */
	public $url_reference = NULL;
	/**
	 * Text to display at the top of a page.
	 * @var string Text
	 */
	public $title = NULL;
	/**
	 * True if title is to be displayed on page.
	 * @var boolean
	 */
	public $showtitle = true;
	/**
	 * Page type
	 * @var string Page type
	 */
	public $type = 'news';
	/**
	 * Stores the full content of the page before it is parsed.
	 * @var string Content of page
	 */
	private $fullcontent;
	/**
	 * Stores the content of the body
	 * @var string
	 */
	public $content;
	public $blocksleft = NULL;
	public $blocksright = NULL;
	/**
	 * Stores the page header
	 * @var string
	 */
	private $header;
	/**
	 * Stores the main page body
	 * @var string
	 */
	private $body;
	/**
	 * Stores the page footer
	 * @var string
	 */
	private $footer;
	/**
	 * Page meta-description for search engines
	 * @var string
	 */
	public $meta_description;
	function __construct() {

	}
	function __destruct() {

	}

	/**
	 * Set type of page to load for pages without ID
	 * @param string $type Name of page type to load
	 * @return void
	 */
	public function set_type($type) {
		switch ($type) {
			default:
				return;
				break;
		}
	}

	/**
	 * set_page - Set the current page by whatever identifier is provided
	 * @param mixed $reference Numeric ID or String
	 * @param boolean $is_id If $reference is a numeric ID, true; else a text ID
	 * @return boolean Success
	 */
	public function set_page($reference, $is_id = true) {
		if ($is_id == true) {
			if (!is_numeric($reference)) {
				return false;
			}
			$this->id = (int)$reference;
		} else {
			if (strlen($reference) == 0) {
				return false;
			}
			$this->text_id = (string)$reference;
			$this->url_reference = 'page='.$this->text_id;
		}
		$this->get_page_information();
		return true;
	}

	/**
	 * If a page exists, collect all information about it from the database.
	 * @global object $db Database connection object
	 * @global object $debug Debug object
	 * @return void
	 */
	public function get_page_information() {
		global $db;
		global $debug;

		// Article Page
		if (isset($_GET['showarticle'])) {
			$debug->add_trace('Loading single article only',false,'get_page_information()');
			$this->id = 0;
			$this->text_id = NULL;
			$this->showtitle = false;
			require(ROOT . 'pagetypes/news_class.php');
			$article = new news_item;
			$article->set_article_id((int)$_GET['showarticle']);
			if (!$article->get_article()) {
				$this->exists = 0;
				return;
			}
			$this->exists = 1;
			$this->content = $article->article;
			return;
		}

		if ($this->id > 0 && strlen($this->text_id) == 0) {
			$debug->add_trace('Using numeric ID to get page information',false,'get_page_information()');
			$page_query_id = '`page`.`id` = '.$this->id;
		} elseif (strlen($this->text_id) > 0) {
			$debug->add_trace('Using text ID to get page information',false,'get_page_information()');
			$page_query_id = '`page`.`text_id` = \''.$this->text_id.'\'';
		} else {
			return;
		}
		$page_query = 'SELECT `page`.*, `pt`.`filename`
			FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
			WHERE '.$page_query_id.'
			AND `page`.`type` = `pt`.`id`
			LIMIT 1';
		$page_handle = $db->sql_query($page_query);
		if ($db->error[$page_handle] == 1) {
			$debug->add_trace('Error looking up page information',true,'get_page_information()');
			return;
		}
		if ($db->sql_num_rows($page_handle) != 1) {
			$debug->add_trace('Page is not listed in database',true,'get_page_information()');
			return;
		}
		$page = $db->sql_fetch_assoc($page_handle);
		$this->id = $page['id'];
		$this->text_id = $page['text_id'];
		$this->showtitle = ($page['show_title'] == 1) ? true : false;
		$this->blocksleft = $page['blocks_left'];
		$this->blocksright = $page['blocks_right'];
		$this->exists = 1;
		$this->meta_description = $page['meta_desc'];
		$this->type = $page['filename'];
		if (strlen($this->text_id) == 0) {
			$this->url_reference = 'id='.$this->id;
		} else {
			if(isset($_GET['id'])) {
				header("HTTP/1.1 301 Moved Permanently");
				$matches = NULL;
				$old_page_address = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
				eregi('id=[0-9]+',$old_page_address,$matches);
				$new_page_address = str_replace($matches,'page='.$this->text_id,$old_page_address);
				header('Location: '.$new_page_address);
			}
			$this->url_reference = 'page='.$this->text_id;
		}
		$this->title = stripslashes($page['title']);
		if(!isset($this->content)) {
			$this->content = include(ROOT.'pagetypes/'.$this->type);
			if(!$this->content) {
				$this->exists = 0;
				$this->notification = '<strong>Error: </strong>System file not found.<br />';
				$debug->add_trace('Including '.$this->type.' returned false',true,'get_page_information()');
			}
		}
		return;
	}

	public function get_page_content() {
		global $db;
		if ($this->exists == 0) {
			header("HTTP/1.0 404 Not Found");
			$this->title .= 'Page Not Found';
			$this->notification .= '<strong>Error: </strong>The requested page
				could not be found.<br />';
			return;
		} else {
			if (!isset($_GET['view'])) {
				$_GET['view'] = NULL;
			}
			return $this->content;
		}
	}
	public function display_header() {
		// FIXME: Stub
	}
	public function display_content() {
		// FIXME: Stub
	}
	public function display_footer() {
		// FIXME: Stub
	}
}
?>
