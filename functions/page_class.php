<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * generate a page
 *
 * This class does ...
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
			case 'settings_main':
				$this->type = 'settings_main.php';
				if(!checkuser(1)) {
					return;
				}
				$this->id = 0;
				$this->exists = 1;
				$this->title = 'Settings - Main';
				break;
			case 'settings_profile':
				$this->type = 'settings_profile.php';
				if(!checkuser(1)) {
					return;
				}
				$this->id = 0;
				$this->exists = 1;
				$this->title = 'Settings - Profile';
				break;
		}
	}
	/**
	 * Set the page's ID
	 * @param int $id Page id
	 * @return void
	 */
	public function set_id($id) {
		if($this->id == $id) {
			return;
		}
		$this->id = (int)$id;
		$this->get_page_information();
		return;
	}
	/**
	 * Set the page's Text ID
	 * @param string $id Text ID
	 * @return void
	 */
	public function set_text_id($id) {
		if($this->text_id == $id) {
			$this->url_reference = 'page='.$this->text_id;
			return;
		}
		if(strlen($id) > 1) {
			$this->text_id = (string)$id;
			$this->get_page_information();
		}
		return;
	}
	/**
	 * If a page exists, collect all information about it from the database.
	 * @global object $db Database connection object
	 * @return void
	 */
	public function get_page_information() {
		global $db;
		if ($this->id != 0 && strlen($this->text_id) == 0) {
			$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' WHERE
				id = '.$this->id.' LIMIT 1';
			$page_handle = $db->sql_query($page_query);
			if ($db->error[$page_handle] == 1) {
				return;
			}
			if ($db->sql_num_rows($page_handle) != 1) {
				return;
			}
			$page = $db->sql_fetch_assoc($page_handle);
			$this->text_id = $page['text_id'];
			$this->exists = 1;
		} elseif (strlen($this->text_id) > 0) {
			$page_query = 'SELECT * FROM ' . PAGE_TABLE . '
				WHERE text_id = \''.$this->text_id.'\' LIMIT 1';
			$page_handle = $db->sql_query($page_query);
			if ($db->error[$page_handle] === 1) {
				return;
			}
			if ($db->sql_num_rows($page_handle) != 1) {
				return;
			}
			$page = $db->sql_fetch_assoc($page_handle);
			$this->id = $page['id'];
			$this->exists = 1;
		} else {
			return;
		}
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
		$this->title = $page['title'];
		$page_type_query = 'SELECT * FROM ' . PAGE_TYPE_TABLE . '
			WHERE id = '.$page['type'].' LIMIT 1';
		$page_type_handle = $db->sql_query($page_type_query);
		if(!$page_type_handle) {
			return;
		}
		$page_type = $db->sql_fetch_assoc($page_type_handle);
		if($db->sql_num_rows($page_type_handle) == 0) {
			$this->exists = 0;
			return;
		}
		$this->type = $page_type['filename'];
		if(!isset($this->content)) {
			$this->content = include(ROOT.'pagetypes/'.$this->type);
			if(!$this->content) {
				$this->exists = 0;
				$this->notification = '<strong>Error: </strong>System file not found.<br />';
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
