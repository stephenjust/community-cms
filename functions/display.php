<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function display_page($page_info,$site_info,$view="") {
		global $db;
		global $CONFIG;
		$template_handle = load_template_file();
		$template = $template_handle['contents'];
		$template_path = $template_handle['template_path'];
		global $page_title;
		$content = get_page_content($page_info['id'],$page_info['type'],$view);
		// Initialize session variable if unset.
		if(!isset($_SESSION['type'])) {
			$_SESSION['type'] = 0;
			}
		if($_SESSION['type'] == 1) {
			$admin_include = "<script src=\"./scripts/admin.js\" type=\"text/javascript\"></script>";
			} else {
			$admin_include = NULL;
			}
		$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
		$image_path = $template_path.'images/';
		$page_message = NULL;
		if($page_info['show_title'] == 1) {
			$page_message .= '<h1>'.$page_info['title'].'</h1>';
			}
		if($content != 'Page not found.') {
			$page_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'page_messages WHERE `page_id` = '.$page_info['id'].' ORDER BY `order`, `start_date` ASC';
			$page_message_handle = $db->query($page_message_query);
			$i = 1;
			while($page_message_handle->num_rows >= $i) {
				$page_message_content = $page_message_handle->fetch_assoc();
				$page_message .= '<div class="page_message">'.stripslashes($page_message_content['text']).'</div>';
				$i++;
				}
			}
		$nav_bar = display_nav_bar();
		$nav_login = display_login_box();
		$template = str_replace('<!-- $ADMIN_INCLUDE$ -->',$admin_include,$template);
		$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
		$template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
		$template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
		$template = str_replace('<!-- $PAGE_MESSAGE$ -->',$page_message,$template);
		$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
		global $special_title;
		$page_title = $page_info['title'].' - '.$special_title.$site_info['name'];
		$template = str_replace('<!-- $PAGE_TITLE$ -->',$page_title,$template);
//		$template = str_replace('<!-- $LEFT_CONTENT$ -->',include(ROOT.'content_blocks/poll_block.php'),$template);
		$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
		$template = str_replace('<!-- $PAGE_ID$ -->',$page_info['id'],$template);
		$template = str_replace('<!-- $FOOTER$ -->','<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a>
<br />Powered by Community CMS',$template);
		echo $template;
		}

	function display_nav_bar($mode = "1") {
		global $page_info;
		global $db;
		global $CONFIG;
		$nav_menu_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE menu = '.$mode.' ORDER BY list ASC';
		$nav_menu_handle = $db->query($nav_menu_query);
		$i = 1;
		$return = NULL;
		while ($nav_menu_handle->num_rows >= $i) {
			$nav_menu = $nav_menu_handle->fetch_assoc();
			if ($nav_menu['id'] == $page_info['id']) {
				$return .= $nav_menu['title']."<br />";
				} else {
				if($nav_menu['type'] == 0) {
					$link = explode('<LINK>',$nav_menu['title']);
					$link_path = $link[1];
					$link_name = $link[0];
					$return .= "<a href='".$link_path."'>".$link_name."</a><br />";
					} else {
					$return .= "<a href='index.php?id=".$nav_menu['id']."'>".$nav_menu['title']."</a><br />";
					}
				}
			$i++;
			}
		return $return;
		}
	
	function display_login_box() {
		global $page_info;
		global $site_info;
		global $db;
		global $CONFIG;
	  if(!isset($_SESSION['user']) || !isset($_SESSION['pass'])) {
	  	$template_handle = load_template_file('login.html');
			$template = $template_handle['contents'];
			$template_path = $template_handle['template_path'];
			$template = str_replace('<!-- $LOGIN_USERNAME$ -->','<input type="text" name="user" id="login_user" />',$template);
			$template = str_replace('<!-- $LOGIN_PASSWORD$ -->','<input type="password" name="passwd" id="login_password" />',$template);
			$template = str_replace('<!-- $LOGIN_BUTTON$ -->','<input type="submit" value="Login!" id="login_button" />',$template);
	    $return = "<form method='post' action='index.php?id=".$page_info['id']."&amp;login=1'>\n".$template."</form>\n";
	  } else { 
	    $return = $_SESSION['name']."<br />\n<a href='index.php?login=2'>Log Out</a><br />\n";
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