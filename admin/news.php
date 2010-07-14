<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$content = NULL;
include(ROOT.'functions/news.php');

if (!$acl->check_permission('adm_news')) {
	$content .= '<span class="errormessage">You do not have the necessary permissions to access this module.</span>';
	return true;
}

// ----------------------------------------------------------------------------

/**
 * get_selected_items - Return the IDs of the selected form items
 * @param string $prefix Form name prefix
 * @return array Array of all IDs
 */
// FIXME: Check if empty form vars are sent in other browsers (not firefox)
function get_selected_items($prefix = 'item') {
	$form_keys = array_keys($_POST);
	$item_keys = array();
	for ($i = 0; $i < count($form_keys); $i++) {
		if (preg_match('/^'.$prefix.'_/',$form_keys[$i])) {
			$item_keys[] = $form_keys[$i];
		}
	}
	$items = array();
	for ($i = 0; $i < count($item_keys); $i++) {
		$items[] = str_replace($prefix.'_',NULL,$item_keys[$i]);
	}
	return $items;
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

switch ($_GET['action']) {
	default:

		break;
	case 'multi':
		if (isset($_POST['pri'])) {
			if (save_priorities($_POST)) {
				$content .= 'Updated priorities.<br />';
			} else {
				$content .= 'Failed to update priorities.<br />';
			}
			break;
		}
		$selected_items = get_selected_items();

		// Check if any items are selected
		if (count($selected_items) == 0) {
			$content .= 'No items are selected.<br />'."\n";
			break;
		}

		// Check if an action is selected
		if (!isset($_POST['news_action'])) {
			$content .= 'No action was selected.<br />'."\n";
			break;
		}

		// Check if a valid action was given
		if ($_POST['news_action'] != 'del' &
			$_POST['news_action'] != 'move' &
			$_POST['news_action'] != 'copy')
		{
			$content .= 'Invalid action.<br />'."\n";
			break;
		}

		if ($_POST['news_action'] == 'del') {
			if (!delete_article($selected_items)) {
				$content .= '<span class="errormessage">Failed to delete article(s)</span><br />'."\n";
			} else {
				$content .= 'Successfully deleted article(s)<br />'."\n";
			}
			break;
		}

		if (!isset($_POST['where'])) {
			$content .= 'No location provided.<br />'."\n";
			break;
		}
		if (!is_numeric($_POST['where'])) {
			$content .= 'Invalid location.<br />'."\n";
			break;
		}
		if ($_POST['news_action'] == 'move') {
			move_article($selected_items,$_POST['where']);
		}
		if ($_POST['news_action'] == 'copy') {
			copy_article($selected_items,$_POST['where']);
		}
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		if (!delete_article($_GET['id'])) {
			$content .= 'Failed to delete article<br />'."\n";
		} else {
			$content .= 'Successfully deleted article<br />'."\n";
		}
		break;

// ----------------------------------------------------------------------------

	case 'new':
		if (!$acl->check_permission('news_create')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions required to create new news articles.</span><br />';
			break;
		}
		// Clean up variables.
		$title = addslashes($_POST['title']);
		$title = str_replace('"','&quot;',$title);
		$title = str_replace('<','&lt;',$title);
		$title = str_replace('>','&gt;',$title);
		$article_content = addslashes(remove_comments($_POST['content']));
		$author = addslashes($_POST['author']);
		$image = addslashes($_POST['image']);
		$page = addslashes($_POST['page']);
		$showdate = $_POST['date_params'];
		if(strlen($image) <= 3) {
			$image = NULL;
		}
		$new_article_query = 'INSERT INTO `' . NEWS_TABLE . "`
			(`page`,`name`,`description`,`author`,`image`,`date`,`date_edited`,`showdate`)
			VALUES ($page,'$title','$article_content','$author','$image','".DATE_TIME."','','$showdate')";
		$new_article = $db->sql_query($new_article_query);
		if($db->error[$new_article] === 1) {
			$content .= 'Failed to add article. <br />';
		} else {
			$page_title_query = 'SELECT * FROM `'.PAGE_TABLE.'` WHERE `id` = '.$_POST['page'].' LIMIT 1';
			$page_title_handle = $db->sql_query($page_title_query);
			if ($db->error[$page_title_handle] === 1) {
				$content .= 'Failed to process log message.<br />'."\n";
				break;
			}
			if ($db->sql_num_rows($page_title_handle) == 1) {
				$page_title_ = $db->sql_fetch_assoc($page_title_handle);
				$page_title = stripslashes($page_title_['title']);
			} else {
				$page_title = 'No Page';
			}
			unset($page_title_query);
			unset($page_title_handle);
			unset($page_title_);
			$content .= 'Successfully added article. <br />';
			log_action('Article \''.$title.'\' added to \''.$page_title.'\'');
		}
		break;

// ----------------------------------------------------------------------------

	case 'publish':
		if (!news_publish($_GET['id'])) {
			$content .= '<span class="errormessage">Failed to publish article.</span><br />'."\n";
			break;
		}
		$content .= 'Successfully published article.<br />'."\n";
		break;
	case 'unpublish':
		if (!news_publish($_GET['id'],false)) {
			$content .= '<span class="errormessage">Failed to unpublish article</span><br />'."\n";
			break;
		}
		$content .= 'Successfully unpublished article<br />'."\n";
		break;

// ----------------------------------------------------------------------------

	case 'edit':
		if (!$acl->check_permission('news_edit')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
			break;
		}
		if (!isset($_GET['id'])) {
			break;
		}
		if (!is_numeric($_GET['id'])) {
			$content .= '<span class="errormessage">Invalid article ID.</span><br />';
			break;
		}
		$article_id = (int)$_GET['id'];

		// Get article information
		$edit_query = 'SELECT * FROM ' . NEWS_TABLE . '
			WHERE id = '.$article_id.' LIMIT 1';
		$edit_handle = $db->sql_query($edit_query);
		if ($db->sql_num_rows($edit_handle) == 0) {
			$content .= '<span class="errormessage">The article you are trying to edit does not exist.</span><br />';
			break;
		}
		$article_page_group = page_group_news($article_id);
		if (!$acl->check_permission('pagegroupedit-'.$article_page_group)) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
			break;
		}

		$edit = $db->sql_fetch_assoc($edit_handle);
		$edit_form = new form;
		$edit_form->set_method('post');
		$edit_form->set_target('admin.php?module=news&amp;action=editsave');
		$edit_form->add_hidden('id',$edit['id']);
		$edit_form->add_textbox('title','Heading',stripslashes($edit['name']));
		$edit_form->add_textarea('update_content','Content',stripslashes($edit['description']));
		$edit_form->add_page_list('page', 'Page', 1, 1, $edit['page']);
		$edit_form->add_icon_list('image','Image','newsicons',$edit['image']);
		$edit_form->add_select('date_params','Date',array(0,1,2),
				array('Hide Date','Show Date','Show Mini'),$edit['showdate']);
		$edit_form->add_submit('submit','Submit');
		$tab_layout->add_tab('Edit Article',$edit_form);
		break;

// ----------------------------------------------------------------------------

	case 'editsave':
		if (!$acl->check_permission('news_edit')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
			break;
		}
		if (!isset($_POST['id'])) {
			$content .= '<span class="errormessage">Invalid article ID.</span><br />';
			break;
		}
		if (!is_numeric($_POST['id'])) {
			$content .= '<span class="errormessage">Invalid article ID.</span><br />';
			break;
		}
		$article_id = (int)$_POST['id'];

		// Pre-save checks
		$edit_query = 'SELECT * FROM ' . NEWS_TABLE . '
			WHERE id = '.$article_id.' LIMIT 1';
		$edit_handle = $db->sql_query($edit_query);
		if ($db->sql_num_rows($edit_handle) == 0) {
			$content .= '<span class="errormessage">The article you are trying to edit does not exist.</span><br />';
			break;
		}
		$article_page_group = page_group_news($article_id);
		if (!$acl->check_permission('pagegroupedit-'.$article_page_group)) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to edit this article.</span><br />';
			break;
		}

		// Clean up variables.
		if (strlen($_POST['image']) <= 3) {
			$_POST['image'] = NULL;
		}
		$edit_content = addslashes(remove_comments($_POST['update_content']));
		$edit_id = addslashes($_POST['id']);
		$name = $_POST['title'];
		$name = str_replace('"','&quot;',$name);
		$name = str_replace('<','&lt;',$name);
		$name = str_replace('>','&gt;',$name);
		$name = addslashes($name);
		$showdate = (int)$_POST['date_params'];
		$image = $_POST['image'];
		$page = (int)$_POST['page'];
		$edit_article_query = 'UPDATE `' . NEWS_TABLE . "`
			SET `name`='$name',`description`='$edit_content',`page`='$page',
			`image`='$image',`date_edited`='".DATE_TIME."',`showdate`='$showdate'
			WHERE `id` = $edit_id";
		$edit_article = $db->sql_query($edit_article_query);
		if ($db->error[$edit_article] === 1) {
			$content .= '<span class="errormessage">Failed to edit article.</span><br />';
			break;
		}
		$content .= 'Successfully edited article. <br />';
		log_action('Edited news article \''.$name.'\'');
		break;
}

// ----------------------------------------------------------------------------

$page_list = '<select name="page" id="adm_article_page_list" onChange="update_article_list(\'-\')">';
$page_query = 'SELECT * FROM `' . PAGE_TABLE . '`
    WHERE `type` = 1 ORDER BY `title` ASC';
$page_query_handle = $db->sql_query($page_query);
for ($i = 1; $i <= $db->sql_num_rows($page_query_handle); $i++) {
    $page = $db->sql_fetch_assoc($page_query_handle);
    if (!isset($_POST['page'])) {
		if (isset($_GET['page'])) {
			$_POST['page'] = $_GET['page'];
		} else {
			$home_info = page_get_info(get_config('home'),array('type'));
			if ($home_info['type'] == 1) {
				$_POST['page'] = get_config('home');
			} else {
				$_POST['page'] = $page['id'];
			}
		}
	}
	if ($page['id'] == $_POST['page']) {
		$page_list .= '<option value="'.$page['id'].'" selected />'.
			stripslashes($page['title']).'</option>';
	} else {
		$page_list .= '<option value="'.$page['id'].'" />'.
			stripslashes($page['title']).'</option>';
	}
	$pages[$i] = $page['id'];
} // FOR $i
if ($_POST['page'] == 0) {
    $no_page = 'selected';
} else {
    $no_page = NULL;
}
if ($_POST['page'] == '*') {
    $all_page = 'selected';
} else {
    $all_page = NULL;
}
$page_list .= '<option value="0" '.$no_page.'>No Page</option>
    <option value="*" '.$all_page.'>All Pages</option>
    </select>';

// Change page form
$tab_content['manage'] = $page_list;

// Form for action on selected item(s)
$tab_content['manage'] .= '<form method="post" action="admin.php?module=news&amp;action=multi">';

$tab_content['manage'] .= '<div id="adm_news_article_list">Loading...</div>'."\n";
$tab_content['manage'] .= '<script type="text/javascript">update_article_list(\''.$_POST['page'].'\');</script>';

$a_page_list = '<select name="where" id="a_where">';
$a_page_query = 'SELECT * FROM `' . PAGE_TABLE . '`
    WHERE `type` = 1 ORDER BY `title` ASC';
$a_page_query_handle = $db->sql_query($a_page_query);
for ($i = 1; $i <= $db->sql_num_rows($a_page_query_handle); $i++) {
    $a_page = $db->sql_fetch_assoc($a_page_query_handle);
	$a_page_list .= '<option value="'.$a_page['id'].'" />'.
		$a_page['title'].'</option>';
    $a_pages[$i] = $a_page['id'];
} // FOR $i
$a_page_list .= '<option value="0">No Page</option>
    </select>';

$tab_content['manage'] .= '<input type="submit" name="pri" value="Update Priorities" /><br /><br />'."\n".
	'With selected:<br />'."\n";
if ($acl->check_permission('news_delete')) {
	$tab_content['manage'] .= '<input type="radio" id="a_del" name="news_action" value="del" />'."\n".
		'<label for="a_del" class="ws">Delete</label><br />'."\n";
}
$tab_content['manage'] .= '<input type="radio" id="a_move" name="news_action" value="move" />'."\n".
	'<label for="a_move" class="ws">Move</label><br />'."\n".
	'<input type="radio" id="a_copy" name="news_action" value="copy" />'."\n".
	'<label for="a_copy" class="ws">Copy</label><br />'."\n".
	"$a_page_list\n".
	'<label for="a_where" class="wsl">Move/copy to:</label><br />'."\n";


$tab_content['manage'] .= '<input type="submit" value="Submit" />';

// End form for action on selected item(s)
$tab_content['manage'] .= '</form>'."\n";

$tab_layout->add_tab('Manage News',$tab_content['manage']);

if ($acl->check_permission('news_create')) {
	$form = new form;
	$form->set_target('admin.php?module=news&amp;action=new');
	$form->set_method('post');
	$form->add_textbox('title','Heading');
	$form->add_hidden('author',$_SESSION['name']);
	$form->add_textarea('content','Content',NULL,'rows="20"');
	$form->add_page_list('page','Page',1,1);
	$form->add_icon_list('image','Image','newsicons');
	$form->add_select('date_params','Date Settings',
			array(0,1,2),array('Hide','Show','Show Mini'),
			get_config('news_default_date_setting'));
	$form->add_submit('submit','Create Article');
	$tab_content['create'] = $form;
	$tab_layout->add_tab('Create Article',$tab_content['create']);
}

$content .= $tab_layout;
?>