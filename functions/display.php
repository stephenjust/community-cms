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
 * @ignore
 */
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * display_page - Generage page from template
 * @global object $db
 * @global object $page
 * @global int $page_not_found
 * @global string $special_title
 * @param array $site_info
 * @param string $view
 * @return void
 */
function display_page($site_info,$view="") {
	global $db;
	global $page;

	// Initialize template
	$template_page = new template;
	$template_page->load_file();

	// Initialize variables
	$page_message = NULL;
	$admin_include = NULL;
	$left_blocks_content = NULL;
	$right_blocks_content = NULL;

	// Replace <!-- $CSS_INCLUDE$ --> marker
	$template_page->css_include =
		'<link rel="StyleSheet" type="text/css" href="'.$template_page->path.'style.css" />';
	$image_path = $template_page->path.'images/';

	// Check to make sure the page actually exists
	global $page_not_found;
	if ($page_not_found == 1) {
		$page->title = 'Page Not Found - '.$site_info['name'];
	} else {
		// Begin operations that only take place if the page really exists

		// Initialize session variable if unset.
		if (!isset($_SESSION['type'])) {
			$_SESSION['type'] = 0;
		}

		// Display the page title if the configuration says to
		if ($page->showtitle === true) {
			$template_page->body_title = '<h1>'.$page->title.'</h1>';
			// Remove marker comments
			$template_page->body_title_start = NULL;
			$template_page->body_title_end = NULL;
		} else {
			// Remove comments referring to 'body_title'
			$template_page->replace_range('body_title',NULL);
		}

		// Get meta info if available
		$meta_desc = $page->meta_description;
		$meta_wrapper[1] = '<meta name="description" content="';
		$meta_wrapper[2] = '" />';
		if (strlen($meta_desc) > 1) {
			$template_page->meta_desc = $meta_wrapper[1].$meta_desc.$meta_wrapper[2];
		} else {
			$template_page->meta_desc = NULL;
		}

		// Get page messages
		$page_message_query = 'SELECT * FROM `' . PAGE_MESSAGE_TABLE . '`
			WHERE `page_id` = '.$page->id.'
			ORDER BY `start_date` ASC';
		$page_message_handle = $db->sql_query($page_message_query);
		$i = 1;
		if ($db->error[$page_message_handle] === 0) { // Don't run the loop if the query failed
			while ($db->sql_num_rows($page_message_handle) >= $i) {
				$page_message_content = $db->sql_fetch_assoc($page_message_handle);
				$page_message .= '<div class="page_message">'.stripslashes($page_message_content['text']).'</div>';
				$i++;
			}
		}

		// Prepare for and search for content blocks
		// Left side
		$left_blocks = explode(',',$page->blocksleft);
		for ($bk = 1; $bk <= count($left_blocks); $bk++) {
			$left_blocks_content .= get_block($left_blocks[$bk - 1]);
		}
		// Right side
		$right_blocks = explode(',',$page->blocksright);
		for ($bk = 1; $bk <= count($right_blocks); $bk++) {
			$right_blocks_content .= get_block($right_blocks[$bk - 1]);
		}

		// Certain pages may set $special_title (such as calendar)
		global $special_title;

		// Add a space below page messages (if any exist)
		if ($page_message != NULL) {
			$page_message .= '<br /><br />'."\n";
		}
	}

	// Grab list of queries that caused an error for debugging
	if(DEBUG === 1) {
		$query_debug = $db->print_error_query();
	} else {
		$query_debug = NULL;
	}

	// Replace markers
	$template_page->page_message = $page_message;
	$template_page->left_content = $left_blocks_content;
	$template_page->right_content = $right_blocks_content;
	$template_page->footer = stripslashes($site_info['footer']).$query_debug;
	$template_page->nav_bar = display_nav_bar();
	$template_page->nav_login = display_login_box();
	$template_page->admin_include = $admin_include;
	$template_page->page_id = $page->id;
	$template_page->page_ref = $page->url_reference;
	$content = $page->get_page_content();
	$template_page->image_path = $image_path;

	// Split template here so we can send some of the page now
	// (probably shouldn't do this because we've already loaded the content,
	// which would theoretically take the most time in the loading process)
	$template_page_bottom = $template_page->split('content');

	if(strlen($page->notification) > 0) {
		$page->notification = '<div class="notification">'.$page->notification.'</div>';
	}
	$page->title .= ' - '.$special_title.$site_info['name'];
	$template_page->page_title = $page->title;
	$template_page->notification = $page->notification;
	echo $template_page;
	unset($template_page);
	$template_page_bottom->content = $content;
	$template_page_bottom->page_id = $page->id;
	$template_page_bottom->page_ref = $page->url_reference;
	$template_page_bottom->image_path = $image_path;
	echo $template_page_bottom;
	unset($template_page_bottom);
	return;
}

/**
 * display_nav_bar - Display a list of links to other pages on the web site
 * @global object $page
 * @global object $db
 * @param int $mode Type of page to display (1 means visible pages, 0 means
 * hidden pages)
 * @return string
 */
function display_nav_bar($mode = 1) {
	global $page;
	global $db;
	$nav_menu_query = 'SELECT * FROM `' . PAGE_TABLE . '`
		WHERE `menu` = '.$mode.' ORDER BY `list` ASC';
	$nav_menu_handle = $db->sql_query($nav_menu_query);
	$return = NULL;
	for ($i = 1; $db->sql_num_rows($nav_menu_handle) >= $i; $i++) {
		$nav_menu = $db->sql_fetch_assoc($nav_menu_handle);
		if ($nav_menu['id'] == $page->id) {
			$return .= stripslashes($nav_menu['title'])."<br />";
		} else {
			if ($nav_menu['type'] == 0) {
				$link = explode('<LINK>',$nav_menu['title']); // Check if menu entry is a link
				$link_path = $link[1];
				$link_name = stripslashes($link[0]);
				unset($link);
				$return .= "<a href='".$link_path."'>".$link_name."</a><br />";
				unset($link_name);
				unset($link_path);
			} else {
				if(strlen($nav_menu['text_id']) > 0) {
					$return .= "<a href='index.php?page=".$nav_menu['text_id']."'>".stripslashes($nav_menu['title'])."</a><br />";
				} else {
					$return .= "<a href='index.php?id=".$nav_menu['id']."'>".stripslashes($nav_menu['title'])."</a><br />";
				}
			} // IF is link
		} // IF is not current page
	} // FOR
	return $return;
}

/**
 * display_login_box - Generate and return content of login box area
 * @global object $db
 * @global object $acl
 * @return string
 */
function display_login_box() {
	global $db;
	global $acl;
	if (!checkuser()) {
		$template_loginbox = new template;
		$template_loginbox->load_file('login');
		$template_loginbox->login_username = '<input type="text" name="user" id="login_user" />';
		$template_loginbox->login_password = '<input type="password" name="passwd" id="login_password" />';
		$template_loginbox->login_button = '<input type="submit" value="Login!" id="login_button" />';
		$return = "<form method='post' action='index.php?".$_SERVER['QUERY_STRING']."&amp;login=1'>\n".$template_loginbox."</form>\n";
		unset($template_loginbox);
	} else {
		$return = $_SESSION['name']."<br />\n<a href='index.php?".$_SERVER['QUERY_STRING']."&amp;login=2'>Log Out</a><br />\n";
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
		if ($acl->check_permission('admin_access')) {
			$return .= '<a href="admin.php">Admin</a>';
		}
	}
	return $return;
}
?>