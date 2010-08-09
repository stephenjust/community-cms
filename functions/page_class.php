<?php
/**
 * Community CMS
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
	 * Text to display in the page's title bar
	 * @var string text
	 */
	public $title = NULL;
	/**
	 * Page title in database
	 * @var string text
	 */
	public $page_title = NULL;
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
	 * Only print content when displaying page
	 * @var boolean 
	 */
	public $content_only = false;
	/**
	 * Stores the content of the body
	 * @var string
	 */
	public $content;
	public $blocksleft = NULL;
	public $blocksright = NULL;
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
			$this->title .= $article->article_title;
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
		$this->page_title = $this->title;
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
			return $this->content;
		}
	}

	public function display_page() {
		// Read template.xml for current template to figure out which order
		// to spit out content

		// If $this->content_only === true, only print the part of the template
		// with type="content"

		// FIXME: Stub
	}

	/**
	 * display_header - Print the page header
	 */
	public function display_header() {
		$template = new template;
		$template->load_file('header');

		// Include javascript
		$js_include = '<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/ajax.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/cms_fe.js"></script>';
		if ($this->type == 'tabs.php') {
			$js_include .= '<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery-ui.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery-fe.js"></script>';
		}
		$template->js_include = $js_include;
		unset($js_include);

		// Include StyleSheets
		$css_include =
			'<link rel="StyleSheet" type="text/css" href="'.$template->path.'style.css" />'."\n".
			'<link rel="StyleSheet" type="text/css" href="'.$template->path.'print.css" media="print" />'."\n";
		if (DEBUG === 1) {
			$css_include .= '<link rel="StyleSheet" type="text/css" href="'.$template->path.'debug.css" />'."\n";
		}
		$template->css_include = $css_include;
		unset($css_include);

		$template->admin_include = NULL;
		$template->print_header = get_config('site_name');

		// Print Meta Description if available
		$meta_desc = $this->meta_description;
		$meta_wrapper[1] = '<meta name="description" content="';
		$meta_wrapper[2] = '" />';
		if (strlen($meta_desc) > 1) {
			$template->meta_desc = $meta_wrapper[1].$meta_desc.$meta_wrapper[2];
		} else {
			$template->meta_desc = NULL;
		}

		if ($this->exists == 0) {
			$this->title .= 'Page not found';
		}
		$this->title .= ' - '.get_config('site_name');
		$template->page_title = $this->title;
		echo $template;
		unset($template);
	}

	/**
	 * nav_menu - Returns HTML for navigation menu
	 * @global object $db Database object
	 * @global object $debug Debugging object
	 * @return string HTML for menu
	 */
	private function nav_menu() {
		global $db;
		global $debug;

		// Prepare menu and submenu templates
		$template = new template;
		if (!$template->load_file('nav_bar')) {
			return false;
		}
		$menu_template = $template->split_range('nav_menu');
		$submenu_template = $template->split_range('nav_submenu');
		unset($template);

		// Handle main menu
		// Split template into components
		$menu_template->nav_menu_id = 'nav-menu';
		$menu_item_template = $menu_template->split_range('menu_item');
		$cmenu_item_template = $menu_template->split_range('current_menu_item');
		$menus_item_template = $menu_template->split_range('menu_item_with_child');
		$cmenus_item_template = $menu_template->split_range('current_menu_item_with_child');

		$nav_menu = page_list(0,true);

		$menu = NULL;
		foreach ($nav_menu AS $nav_menu_item) {
			$haschild = 0;
			if ($nav_menu_item['has_children'] == true && $this->id == $nav_menu_item['id']) {
				$item_template = clone $cmenus_item_template;
				$haschild = 1;
			} elseif ($nav_menu_item['has_children'] == true) {
				$item_template = clone $menus_item_template;
				$haschild = 1;
			} elseif ($this->id == $nav_menu_item['id']) {
				$item_template = clone $cmenu_item_template;
			} else {
				$item_template = clone $menu_item_template;
			}
			if ($nav_menu_item['type'] == 0) {
				$link = explode('<LINK>',$nav_menu_item['title']); // Check if menu entry is a link
				$link_path = $link[1];
				$link_name = stripslashes($link[0]);
				unset($link);
			} else {
				if(strlen($nav_menu_item['text_id']) > 0) {
					$link_path = "index.php?page=".$nav_menu_item['text_id'];
				} else {
					$link_path = "index.php?id=".$nav_menu_item['id'];
				}
				$link_name = stripslashes($nav_menu_item['title']);
			}
			$item_template->menu_item_url = $link_path;
			$item_template->menu_item_label = $link_name;
			$item_template->menu_item_id = 'menuitem_'.$nav_menu_item['id'];
			// Generate hidden child div
			if ($haschild == 1) {
				$item_template->child_placeholder = display_child_menu($nav_menu_item['id']);
			} else {
				$item_template->child_placeholder = NULL;
			}
			$menu .= (string)$item_template;
			unset($item_template);
		} // FOR
		$menu_template->menu_placeholder = $menu;
		return $menu_template;
	}
	public function display_left() {
		$template = new template;
		$template->load_file('left');
		$template->nav_bar = $this->nav_menu();
		$template->nav_login = display_login_box();

		// Prepare blocks
		$left_blocks_content = NULL;
		$left_blocks = explode(',',$this->blocksleft);
		for ($bk = 1; $bk <= count($left_blocks); $bk++) {
			$left_blocks_content .= get_block($left_blocks[$bk - 1]);
		}
		$template->left_content = $left_blocks_content;
		echo $template;
	}
	public function display_right() {
		$template = new template;
		$template->load_file('right');

		// Prepare blocks
		$right_blocks_content = NULL;
		$right_blocks = explode(',',$this->blocksright);
		for ($bk = 1; $bk <= count($right_blocks); $bk++) {
			$right_blocks_content .= get_block($right_blocks[$bk - 1]);
		}
		$template->right_content = $right_blocks_content;
		echo $template;
	}

	public function display_content() {
		global $db;

		$template = new template;
		$template->load_file('content');
		$template->page_path = page_path($this->id);

		// Display the page title if the configuration says to
		if ($this->showtitle === true) {
			$template->body_title = $this->page_title;
			// Remove marker comments
			$template->body_title_start = NULL;
			$template->body_title_end = NULL;
		} else {
			// Remove comments referring to 'body_title'
			$template->replace_range('body_title',NULL);
		}

		// Display page notifications
		if (strlen($this->notification) > 0) {
			$template->notification = $this->notification;
			$template->notification_start = NULL;
			$template->notification_end = NULL;
		} else {
			$template->replace_range('notification',NULL);
		}

		// Get page messages
		$page_message_query = 'SELECT * FROM `' . PAGE_MESSAGE_TABLE . '`
			WHERE `page_id` = '.$this->id.'
			ORDER BY `start_date` ASC';
		$page_message_handle = $db->sql_query($page_message_query);
		$page_message = NULL;
		if ($db->error[$page_message_handle] === 0) { // Don't run the loop if the query failed
			for ($i = 1; $db->sql_num_rows($page_message_handle) >= $i; $i++) {
				$page_message_content = $db->sql_fetch_assoc($page_message_handle);
				$page_message .= '<div class="page_message">'.stripslashes($page_message_content['text']).'</div>';
			}
		}
		// Display page messages
		if (strlen($page_message) > 0) {
			$template->page_message = $page_message;
			$template->page_message_start = NULL;
			$template->page_message_end = NULL;
		} else {
			$template->replace_range('page_message',NULL);
		}

		$template->content = $this->get_page_content();

		// This must be done after $template->content is set because the
		// following could be used within the content.
		$template->page_id = $this->id;
		$template->page_ref = $this->url_reference;

		echo $template;
		unset($template);
	}
	public function display_footer() {
		$template = new template;
		$template->load_file('footer');
		$template->footer = get_config('footer');
		echo $template;
		unset($template);
	}
	public function display_debug() {
		global $db;
		global $debug;

		$template = new template;
		$template->load_file('debug');
		$template->debug_queries = $db->print_queries();
		$template->debug_query_stats = $db->print_query_stats();
		$template->debug_log = $debug->display_traces();
		echo $template;
		unset($template);
	}
}
?>
