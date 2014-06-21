<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/PageMessage.class.php');

/**
 * Generates a page
 * @package CommunityCMS.main
 */
class Page {
	/**
	 * Unique identifier for page
	 * @var integer Page ID
	 */
	public static $id = 0;
	/**
	 * Marker that records whether the page exists or not
	 * @var bool True if page exists, false if it does not.
	 */
	public static $exists = false;
	/**
	 * Unique string identifier for page
	 * @var string Page Text ID
	 */
	public static $text_id = NULL;
	/**
	 * Notification to display in the notification area of the page
	 * @var string
	 */
	public static $notification = '';
	/**
	 * How scripts should reference the page
	 * @var string Either Text ID or ID
	 */
	public static $url_reference = NULL;
	/**
	 * Text to display in the page's title bar
	 * @var string text
	 */
	public static $title = NULL;
	/**
	 * Page title in database
	 * @var string text
	 */
	public static $page_title = NULL;
	/**
	 * True if title is to be displayed on page.
	 * @var boolean
	 */
	public static $showtitle = true;
	/**
	 * If 'true' when display_left() called, user menu will be displayed.
	 * @var boolean
	 */
	public static $showlogin = true;
	/**
	 * Page type
	 * @var string Page type
	 */
	public static $type = 'news.php';
	/**
	 * Only print content when displaying page (unused)
	 * @var boolean 
	 */
	private static $content_only = false;
	/**
	 * Stores the content of the body
	 * @var string
	 */
	public static $content;
	private static $blocksleft = NULL;
	private static $blocksright = NULL;
	/**
	 * Page meta-description for search engines
	 * @var string
	 */
	private static $meta_description;
	/**
	 * Page group
	 * @var integer
	 */
	public static $page_group = 0;
//	/**
//	 * Contains the hierarchial structure of page IDs
//	 * @var array
//	 */
//	private static $page_hierarchy = array();
	/**
	 * Contains various properties about pages that need to be accessed repeatedly
	 * @var array 
	 */
	private static $page_properties = array();
	
	/**
	 * Set type of page to load for pages without ID
	 * @param string $type Name of page type to load
	 * @return void
	 */
	public static function set_type($type) {
		switch ($type) {
			default:
				return;
				break;
		}
	}

	/**
	 * set_page - Set the current page by whatever identifier is provided
	 * @global Debug $debug Debug object
	 * @param mixed $reference Numeric ID or String
	 * @param boolean $is_id If $reference is a numeric ID or special page, true; else a text ID
	 * @return boolean Success
	 */
	public static function set_page($reference, $is_id = true) {
		global $debug;

		if ($is_id == true) {
			if (!is_numeric($reference)) {
				// Handle special page types
				switch ($reference) {
					default:
						// Error case
						$debug->addMessage('Unknown special page type',true);
						Page::$exists = false;
						Page::get_page_content();
						return false;

					case 'change_password':
						// Change Password
						Page::$text_id = $reference;
						Page::$showlogin = false;
						Page::$url_reference = 'id=change_password';
						Page::get_special_page();
						return true;
				}
			}
			Page::$id = (int)$reference;
		} else {
			if (strlen($reference) == 0) {
				return false;
			}
			Page::$text_id = (string)$reference;
			Page::$url_reference = 'page='.Page::$text_id;
		}
		Page::get_page_information();
		return true;
	}

	/**
	 * If a page exists, collect all information about it from the database.
	 * @global db $db Database connection object
	 * @global Debug $debug Debug object
	 * @return void
	 */
	public static function get_page_information() {
		global $db;
		global $debug;

		// Article Page
		if (isset($_GET['showarticle'])) {
			$debug->addMessage('Loading single article only',false);
			Page::$id = 0;
			Page::$text_id = NULL;
			Page::$showtitle = false;
			require(ROOT . 'pagetypes/news_class.php');
			$article = new news_item;
			$article->set_article_id((int)$_GET['showarticle']);
			if (!$article->get_article()) {
				header("HTTP/1.0 404 Not Found");
				Page::$exists = false;
				return;
			}
			Page::$title .= $article->article_title;
			Page::$exists = true;
			Page::$content = $article->article;
			return;
		}

		// Get either the page ID or text ID for use in the section below
		if (Page::$id > 0 && strlen(Page::$text_id) == 0) {
			$debug->addMessage('Using numeric ID to get page information',false);
			$page_query_id = '`page`.`id` = '.Page::$id;
		} elseif (strlen(Page::$text_id) > 0) {
			$debug->addMessage('Using text ID to get page information',false);
			$page_query_id = '`page`.`text_id` = \''.Page::$text_id.'\'';
		} else {
			return;
		}

		// Look up information (including page type) for the current page
		$page_query = 'SELECT `page`.*, `pt`.`filename`
			FROM `'.PAGE_TABLE.'` `page`, `'.PAGE_TYPE_TABLE.'` `pt`
			WHERE '.$page_query_id.'
			AND `page`.`type` = `pt`.`id`
			LIMIT 1';
		$page_handle = $db->sql_query($page_query);
		if ($db->error[$page_handle] == 1) {
			header("HTTP/1.0 404 Not Found");
			$debug->addMessage('Error looking up page information',true);
			return;
		}
		if ($db->sql_num_rows($page_handle) != 1) {
			header("HTTP/1.0 404 Not Found");
			$debug->addMessage('Page is not listed in database',true);
			return;
		}
		$page = $db->sql_fetch_assoc($page_handle);

		// Page was found; populate the class fields
		Page::$id = $page['id'];
		Page::$text_id = $page['text_id'];
		Page::$showtitle = ($page['show_title'] == 1) ? true : false;
		Page::$blocksleft = $page['blocks_left'];
		Page::$blocksright = $page['blocks_right'];
		Page::$exists = true;
		Page::$meta_description = $page['meta_desc'];
		Page::$page_group = $page['page_group'];
		Page::$type = $page['filename'];
		if (strlen(Page::$text_id) == 0) {
			Page::$url_reference = 'id='.Page::$id;
		} else {
			if(isset($_GET['id'])) {
				header("HTTP/1.1 301 Moved Permanently");
				$matches = NULL;
				$old_page_address = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
				preg_match('/id=[0-9]+/i',$old_page_address,$matches);
				$new_page_address = str_replace($matches,'page='.Page::$text_id,$old_page_address);
				header('Location: '.$new_page_address);
			}
			Page::$url_reference = 'page='.Page::$text_id;
		}
		Page::$title = $page['title'];
		Page::$page_title = Page::$title;
		if(!isset(Page::$content)) {
			Page::$content = include(ROOT.'pagetypes/'.Page::$type);
			if(!Page::$content) {
				// Including the pagetype file failed - either a file is missing,
				// or the included file returned 'false'
				header("HTTP/1.0 404 Not Found");
				Page::$exists = false;
				Page::$notification = '<strong>Error: </strong>System file not found.<br />';
				$debug->addMessage('Including '.Page::$type.' returned false',true);
			}
		}
		return;
	}

	/**
	 * Handle "special" pages (i.e. change password page)
	 * @global Debug $debug
	 */
	private static function get_special_page() {
		global $debug;

		Page::$type = 'special.php';
		Page::$showtitle = false;
		Page::$blocksleft = NULL;
		Page::$blocksright = NULL;
		Page::$exists = true;
		Page::$meta_description = NULL;
		if(!isset(Page::$content)) {
			Page::$content = include(ROOT.'pagetypes/'.Page::$type);
			if(!Page::$content) {
				Page::$exists = false;
				Page::$notification = '<strong>Error: </strong>System file not found.<br />';
				$debug->addMessage('Including '.Page::$type.' returned false',true);
			}
		}
	}

	public static function get_page_content() {
		if (Page::$exists === false) {
			Page::$title .= 'Page Not Found';
			Page::$notification .= '<strong>Error: </strong>The requested page
				could not be found.<br />';
			return;
		} else {
			return Page::$content;
		}
	}

	public static function display_page() {
		// Read template.xml for current template to figure out which order
		// to spit out content

		// If $this->content_only === true, only print the part of the template
		// with type="content"

		// FIXME: Stub
	}

	/**
	 * display_header - Print the page header
	 */
	public static function display_header() {
		$template = new template;
		$template->load_file('header');

		// Include javascript
		$js_include = '<script language="javascript" type="text/javascript"
			src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery-ui.js" /></script>
				<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery-cycle.js" /></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/jquery-fe.js" /></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/ajax.js"></script>
			<script language="javascript" type="text/javascript"
			src="'.ROOT.'scripts/cms_fe.js"></script>';
		if (Page::$type == 'tabs.php') {
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
		$meta_desc = Page::$meta_description;
		$meta_wrapper[1] = '<meta name="description" content="';
		$meta_wrapper[2] = '" />';
		if (strlen($meta_desc) > 1) {
			$template->meta_desc = $meta_wrapper[1].$meta_desc.$meta_wrapper[2];
		} else {
			$template->meta_desc = NULL;
		}

		if (Page::$exists === false) {
			Page::$title .= 'Page Not Found';
		}
		Page::$title .= ' - '.get_config('site_name');
		$template->page_title = Page::$title;
		echo $template;
		unset($template);
	}

	/**
	 * nav_menu - Returns HTML for navigation menu
	 * @global db $db Database object
	 * @global Debug $debug Debugging object
	 * @return string HTML for menu
	 */
	private static function nav_menu() {
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
			if ($nav_menu_item['has_children'] == true && Page::$id == $nav_menu_item['id']) {
				$item_template = clone $cmenus_item_template;
				$haschild = 1;
			} elseif ($nav_menu_item['has_children'] == true) {
				$item_template = clone $menus_item_template;
				$haschild = 1;
			} elseif (Page::$id == $nav_menu_item['id']) {
				$item_template = clone $cmenu_item_template;
			} else {
				$item_template = clone $menu_item_template;
			}
			if ($nav_menu_item['type'] == 0) {
				$link = explode('<LINK>',$nav_menu_item['title']); // Check if menu entry is a link
				$link_path = $link[1];
				$link_name = $link[0];
				unset($link);
			} else {
				if(strlen($nav_menu_item['text_id']) > 0) {
					$link_path = "index.php?page=".$nav_menu_item['text_id'];
				} else {
					$link_path = "index.php?id=".$nav_menu_item['id'];
				}
				$link_name = $nav_menu_item['title'];
			}
			$item_template->menu_item_url = $link_path;
			$item_template->menu_item_label = $link_name;
			$item_template->menu_item_id = 'menuitem_'.$nav_menu_item['id'];
			// Generate hidden child div
			if ($haschild == 1) {
				$item_template->child_placeholder = Page::nav_child_menu($nav_menu_item['id']);
			} else {
				$item_template->child_placeholder = NULL;
			}
			$menu .= (string)$item_template;
			unset($item_template);
		} // FOR
		$menu_template->menu_placeholder = $menu;
		return $menu_template;
	}

	private static function nav_child_menu($parent) {
		global $db;

		if (!is_numeric($parent) || is_array($parent)) {
			return false;
		}
		$parent = (int)$parent;
		$return = NULL;

		$items_query = 'SELECT * FROM `'.PAGE_TABLE.'`
			WHERE `parent` = '.$parent.' AND `menu` = 1 ORDER BY `list` ASC';
		$items_handle = $db->sql_query($items_query);
		if ($db->error[$items_handle] == 1) {
			return false;
		}
		if ($db->sql_num_rows($items_handle) == 0) {
			return false;
		}

		// Read template
		$template = new template();
		$template->load_file('nav_bar');
		// Grab the sub-menu part of the template
		$sub_template = $template->split_range('nav_submenu');
		unset($template);

		// Pull out the styles for the types of items contained within
		$item_temp = $sub_template->split_range('menu_item');
		$currentitem_temp = $sub_template->split_range('current_menu_item');
		$itemchild_temp = $sub_template->split_range('menu_item_with_child');
		$currentitemchild_temp = $sub_template->split_range('current_menu_item_with_child');

		$sub_template->nav_menu_id = 'nav-menu-sub-'.$parent;

		// Populate the menu with items
		$menu_items = NULL;
		for ($i = 1; $i <= $db->sql_num_rows($items_handle); $i++) {
			$items_result = $db->sql_fetch_assoc($items_handle);
			$haschild = 0;
			$extra_text = NULL;
			// Select the proper template
			if (Page::has_children($items_result['id']) === true && Page::$id !== $items_result['id']) {
				$this_item = clone $itemchild_temp;
				$this_item->child_placeholder = Page::nav_child_menu($items_result['id']);
			} elseif (Page::has_children($items_result['id']) === true && Page::$id === $items_result['id']) {
				$this_item = clone $currentitemchild_temp;
				$this_item->child_placeholder = Page::nav_child_menu($items_result['id']);
			} elseif (Page::has_children($items_result['id']) === false && Page::$id !== $items_result['id'])
				$this_item = clone $item_temp;
			else
				$this_item = clone $currentitem_temp;

			$this_item->menu_item_id = 'menuitem_'.$items_result['id'];
			if ($items_result['type'] == 0) {
				$link = explode('<LINK>',$items_result['title']); // Check if menu entry is a link
				$link_path = $link[1];
				$link_name = $link[0];
				unset($link);
			} else {
				if(strlen($items_result['text_id']) > 0) {
					$link_path = "index.php?page=".$items_result['text_id'];
				} else {
					$link_path = "index.php?id=".$items_result['id'];
				}
				$link_name = $items_result['title'];
			} // IF is link
			$this_item->menu_item_url = $link_path;
			$this_item->menu_item_label = $link_name;
			$menu_items .= (string)$this_item;
			unset($this_item);
		}
		$sub_template->menu_placeholder = $menu_items;

		// Output the template
		return $sub_template;
	}

	/**
	* Test if there are any children to the given page
	* @global db $db Database connection object
	* @param integer $id Page ID of page to test
	* @param boolean $visible_children_only Only consider items that will appear in the menu
	* @return boolean
	*/
	public static function has_children($id, $visible_children_only = false) {
		global $db;

		if (!is_numeric($id) || is_array($id)) {
			return false;
		}
		$id = (int)$id;

		if (isset(Page::$page_properties[$id]['haschild']) && !$visible_children_only) {
			return Page::$page_properties[$id]['haschild'];
		} elseif (isset(Page::$page_properties[$id]['haschild_vis']) && $visible_children_only) {
			return Page::$page_properties[$id]['haschild_vis'];
		}
		
		$visible = NULL;
		if ($visible_children_only == true) {
			$visible = 'AND `menu` = 1 ';
		}

		$query = 'SELECT * FROM `'.PAGE_TABLE.'`
			WHERE `parent` = '.$id.' '.$visible.'LIMIT 1';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1) {
			return false;
		}
		if ($db->sql_num_rows($handle) == 0)
			$return = false;
		else
			$return = true;

		if (!$visible_children_only)
			Page::$page_properties[$id]['haschild'] = $return;
		else
			Page::$page_properties[$id]['haschild_vis'] = $return;
		return $return;
	}

	public static function display_left() {
		$template = new template;
		$template->load_file('left');
		$template->nav_bar = Page::nav_menu();

		// Hide login box when it may cause issues
		if (Page::$showlogin === true) {
			$template->nav_login = Page::display_login_box();
		} else {
			$template->nav_login = NULL;
		}

		// Prepare blocks
		$left_blocks_content = NULL;
		$left_blocks = explode(',',Page::$blocksleft);
		for ($bk = 1; $bk <= count($left_blocks); $bk++) {
			$left_blocks_content .= get_block($left_blocks[$bk - 1]);
		}
		$template->left_content = $left_blocks_content;
		echo $template;
	}
	public static function display_right() {
		$template = new template;
		$template->load_file('right');

		// Prepare blocks
		$right_blocks_content = NULL;
		$right_blocks = explode(',',Page::$blocksright);
		for ($bk = 1; $bk <= count($right_blocks); $bk++) {
			$right_blocks_content .= get_block($right_blocks[$bk - 1]);
		}
		$template->right_content = $right_blocks_content;
		echo $template;
	}
	
	private static function getPageMessages() {
		$messages = PageMessage::getByPage(Page::$id);
		$return = NULL;
		foreach ($messages AS $message) {
			$return .= sprintf('<div class="page_message">%s</div>', $message->getContent());
		}
		return $return;
	}

	public static function display_content() {
		global $db;

		$template = new template;
		$template->load_file('content');
		$template->page_path = page_path(Page::$id);

		// Display the page title if the configuration says to
		if (Page::$showtitle === true) {
			$template->body_title = Page::$page_title;
			// Remove marker comments
			$template->body_title_start = NULL;
			$template->body_title_end = NULL;
		} else {
			// Remove comments referring to 'body_title'
			$template->replace_range('body_title',NULL);
		}

		// Display page edit bar
		$edit_bar = new editbar;
		$edit_bar->set_label('Page');
		$edit_bar->class = 'edit_bar page_edit_bar';
		if (Page::$id != 0) {
			$permission_list = array('admin_access','page_edit');
			if (Page::$page_group !== 0) {
				$permission_list[] = 'pagegroupedit-'.Page::$page_group;
			}
			$edit_bar->add_control('admin.php?module=page&action=edit&id='.Page::$id,
					'edit.png','Edit',$permission_list);
			unset($permission_list);
		}
		$template->page_edit_bar = $edit_bar;

		// Display page notifications
		if (strlen(Page::$notification) > 0) {
			$template->notification = Page::$notification;
			$template->notification_start = NULL;
			$template->notification_end = NULL;
		} else {
			$template->replace_range('notification',NULL);
		}

		if (Page::$type != 'special.php') {
			$page_message = Page::getPageMessages();
		} else {
			$page_message = NULL;
		}
		$template->page_message = $page_message;
		$template->page_message_start = NULL;
		$template->page_message_end = NULL;

		$template->content = Page::get_page_content();

		// This must be done after $template->content is set because the
		// following could be used within the content.
		$template->page_id = Page::$id;
		$template->page_ref = Page::$url_reference;

		echo $template;
		unset($template);
	}
	public static function display_footer() {
		$template = new template;
		$template->load_file('footer');
		$template->footer = get_config('footer');
		echo $template;
		unset($template);
	}
	public static function display_debug() {
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

	/**
	* display_login_box - Generate and return content of login box area
	* @global db $db
	* @global object $acl
	* @return string
	*/
	public static function display_login_box() {
		global $db;
		global $acl;
		global $user;
		if (!$user->logged_in) {
			$template_loginbox = new template;
			$template_loginbox->load_file('login');
			$template_loginbox->login_username = '<input type="text" name="user" id="login_user" />';
			$template_loginbox->login_password = '<input type="password" name="passwd" id="login_password" />';
			$template_loginbox->login_button = '<input type="submit" value="Login!" id="login_button" />';
			$return = "<form method='post' action='index.php?".$_SERVER['QUERY_STRING']."&amp;login=1'>\n".$template_loginbox."</form>\n";
			unset($template_loginbox);
		} else {
			$return = $_SESSION['name']."<br />\n".HTML::link('index.php?'.$_SERVER['QUERY_STRING'].'&login=2','Log Out')."<br />\n";
			$check_message_query = 'SELECT * FROM ' . MESSAGE_TABLE . '
				WHERE recipient = '.$_SESSION['userid'];
			$check_message_handle = $db->sql_query($check_message_query);
			if (!$check_message_handle) {
				$return .= 'Could not check messages.';
			} else {
				$check_message = $db->sql_num_rows($check_message_handle);
				$return .= '<a href="messages.php">'.$check_message." new messages</a><br />\n";
			}
			unset($check_message_handle);
			unset($check_message_query);
			$return .= HTML::link('index.php?id=change_password','Change Password')."<br />\n";
			if ($acl->check_permission('admin_access')) {
				$return .= HTML::link('admin.php','Admin');
			}
		}
		return $return;
	}
}
?>
