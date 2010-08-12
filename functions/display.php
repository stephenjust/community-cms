<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

function display_child_menu($parent) {
	global $db;
	global $page;

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

	$return .= '<ul id="nav-menu-sub-'.$parent.'" class="nav_submenu">';
	for ($i = 1; $i <= $db->sql_num_rows($items_handle); $i++) {
		$items_result = $db->sql_fetch_assoc($items_handle);
		$haschild = 0;
		$extra_text = NULL;
		if (page_has_children($items_result['id']) == true) {
			$link_class = 'submenuitem_haschild';
			$extra_text = '<div class="childarrow"></div>';
			if ($page->id == $items_result['id']) {
				$link_class = 'submenuitem_haschild_current';
			}
			$haschild = 1;
		} elseif ($page->id == $items_result['id']) {
			$link_class = 'submenuitem_current';
		} else {
			$link_class = 'submenuitem';
		}
		if ($items_result['type'] == 0) {
			$link = explode('<LINK>',$items_result['title']); // Check if menu entry is a link
			$link_path = $link[1];
			$link_name = stripslashes($link[0]);
			unset($link);
		} else {
			if(strlen($items_result['text_id']) > 0) {
				$link_path = "index.php?page=".$items_result['text_id'];
			} else {
				$link_path = "index.php?id=".$items_result['id'];
			}
			$link_name = stripslashes($items_result['title']);
		} // IF is link
		$return .= '<li class="'.$link_class.'" id="menuitem_'.$items_result['id'].'">'."\n";
		// Generate hidden child div
		if ($haschild == 1) {
			$return .= display_child_menu($items_result['id']);
		}
		$return .= '<a href="'.$link_path.'">'.$link_name.'</a>'.$extra_text;
		$return .= '</li>'."\n";
	}
	$return .= '</ul>';

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
		$return .= '<a href="index.php?id=change_password">Change Password</a><br />'."\n";
		if ($acl->check_permission('admin_access')) {
			$return .= '<a href="admin.php">Admin</a>';
		}
	}
	return $return;
}

/**
 * news_edit_bar - Display quick-edit buttons for news articles
 * @global object $acl
 * @global object $db
 * @param integer $article_id
 * @return string
 */
function news_edit_bar($article_id) {
	global $acl;
	global $db;

	$page_group_id = page_group_news($article_id);

	// Make sure the user can edit content in this page group
	if (!$acl->check_permission('pagegroupedit-'.$page_group_id)) {
		return NULL;
	}

	$return = NULL;
	if ($acl->check_permission('news_edit') && $acl->check_permission('adm_news')) {
		$return .= '<a href="admin.php?module=news&amp;action=edit&amp;id='.$article_id.'">
			<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" /></a>';
	}

	return $return;
}
?>