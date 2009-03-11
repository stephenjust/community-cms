<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function display_page($page_info,$site_info,$view="") {
		global $db;
		global $CONFIG;
		$template_page = new template;
		$template_page->load_file();
		$page_message = NULL;
		$admin_include = NULL;
		$left_blocks_content = NULL;
		$right_blocks_content = NULL;
		$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_page->path."style.css' />";
		$image_path = $template_page->path.'images/';
		// Check if the page acutally exists before anything else is done
		global $page_not_found;
		if($page_not_found == 1) {
			$page_title = 'Page Not Found - '.$site_info['name'];
			} else {
			// Initialize session variable if unset.
			if(!isset($_SESSION['type'])) {
				$_SESSION['type'] = 0;
				}
			if($_SESSION['type'] == 1) { // Check for admin status
				$admin_include = "<script src=\"./scripts/admin.js\" type=\"text/javascript\"></script>";
				}
			if($page_info['show_title'] == 1) { // Display the page header if required
				$page_message .= '<h1>'.$page_info['title'].'</h1>';
				}
			$page_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'page_messages WHERE `page_id` = '.$page_info['id'].' ORDER BY `order`, `start_date` ASC';
			$page_message_handle = $db->query($page_message_query);
			$i = 1;
			if($page_message_handle) { // Don't run the loop if the query failed
				while($page_message_handle->num_rows >= $i) {
					$page_message_content = $page_message_handle->fetch_assoc();
					$page_message .= '<div class="page_message">'.stripslashes($page_message_content['text']).'</div>';
					$i++;
					}
				}
			// Prepare for and search for content blocks
			$left_blocks = explode(',',$page_info['blocks_left']);
			$bk = 1; // Block iteration count
			while ($bk <= count($left_blocks)) {
				$block_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$left_blocks[$bk - 1].' LIMIT 1';
				$block_handle = $db->query($block_query);
				if($block_handle) {
					if($block_handle->num_rows == 1) {
						$block_info = $block_handle->fetch_assoc();
						$left_blocks_content .= include(ROOT.'content_blocks/'.$block_info['type'].'_block.php');
						}
					}
				$bk++;
				}
			$right_blocks = explode(',',$page_info['blocks_right']);
			$bk = 1; // Block iteration count
			while ($bk <= count($right_blocks)) {
				$block_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$right_blocks[$bk - 1].' LIMIT 1';
				$block_handle = $db->query($block_query);
				if($block_handle) {
					if($block_handle->num_rows == 1) {
						$block_info = $block_handle->fetch_assoc();
						$right_blocks_content .= include(ROOT.'content_blocks/'.$block_info['type'].'_block.php');
						}
					}
				$bk++;
				}
			global $special_title;
			$page_title = $page_info['title'].' - '.$special_title.$site_info['name'];
			}
		$template_page->page_title = $page_title;
		$template_page->page_message = $page_message;
		$template_page->left_content = $left_blocks_content;
		$template_page->right_content = $right_blocks_content;
		$template_page->footer = stripslashes($site_info['footer']);
		$template_page->nav_bar = display_nav_bar();
		$template_page->nav_login = display_login_box();
		$template_page->css_include = $css_include;
		$template_page->admin_include = $admin_include;
		$template_page->page_id = $page_info['id'];
		$template_page->image_path = $image_path;
		$template_page_bottom = $template_page->split('content');
		echo $template_page;
		unset($template_page);
		$content = get_page_content($page_info['id'],$page_info['type'],$view);
		$template_page_bottom->content = $content;
		$template_page_bottom->page_id = $page_info['id'];
		$template_page_bottom->image_path = $image_path;
		echo $template_page_bottom;
		unset($template_page_bottom);
		}

	function display_nav_bar($mode = 1) {
		global $page_info;
		global $db;
		global $CONFIG;
		$nav_menu_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE menu = '.$mode.' ORDER BY list ASC';
		$nav_menu_handle = $db->query($nav_menu_query);
		$return = NULL;
		for ($i = 1; $nav_menu_handle->num_rows >= $i; $i++) {
			$nav_menu = $nav_menu_handle->fetch_assoc();
			if ($nav_menu['id'] == $page_info['id']) {
				$return .= $nav_menu['title']."<br />";
				} else {
				if($nav_menu['type'] == 0) {
					$link = explode('<LINK>',$nav_menu['title']); // Check if menu entry is a link
					$link_path = $link[1];
					$link_name = $link[0];
					unset($link);
					$return .= "<a href='".$link_path."'>".$link_name."</a><br />";
					unset($link_name);
					unset($link_path);
					} else {
					$return .= "<a href='index.php?id=".$nav_menu['id']."'>".$nav_menu['title']."</a><br />";
					} // IF is link
				} // IF is not current page
			} // FOR
		return $return;
		}
	
	function display_login_box() {
		global $page_info;
		global $site_info;
		global $db;
		global $CONFIG;
	  if(!checkuser()) {
	  	$template_loginbox = new template;
	  	$template_loginbox->load_file('login');
	  	$template_loginbox->login_username = '<input type="text" name="user" id="login_user" />';
	  	$template_loginbox->login_password = '<input type="password" name="passwd" id="login_password" />';
	  	$template_loginbox->login_button = '<input type="submit" value="Login!" id="login_button" />';
	    $return = "<form method='post' action='index.php?".$_SERVER['QUERY_STRING']."&amp;login=1'>\n".$template_loginbox."</form>\n";
	    unset($template_loginbox);
	  } else { 
	    $return = $_SESSION['name']."<br />\n<a href='index.php?".$_SERVER['QUERY_STRING']."&amp;login=2'>Log Out</a><br />\n";
	    $check_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'messages WHERE recipient = '.$_SESSION['userid'];
	    $check_message_handle = $db->query($check_message_query);
	    $check_message = $check_message_handle->num_rows;
	    $return .= '<a href="messages.php">'.$check_message." new messages</a><br />\n";
	    $return .= mysqli_error($db);
	    if($_SESSION['type'] >= 1) {
	      $return .= "<a href='admin.php?".SID."'>Admin</a>";
	    }
	  }
	  return $return;
	}
?>