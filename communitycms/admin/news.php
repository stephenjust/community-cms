<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
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
		try {
			news_create($_POST['title'], $_POST['content'],
					$_POST['page'], $_POST['author'], $_POST['image'],
					$_POST['publish'], $_POST['date_params']);
			$content .= 'Successfully added article.<br />';
		}
		catch (Exception $e) {
			$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
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
		$edit_form->add_textbox('title','Heading',$edit['name']);
		$edit_form->add_textarea('update_content','Content',$edit['description']);
		$edit_form->add_page_list('page', 'Page', 1, 1, $edit['page']);
		$edit_form->add_icon_list('image','Image','newsicons',$edit['image']);
		$edit_form->add_select('date_params','Date',array(0,1,2),
				array('Hide Date','Show Date','Show Mini'),$edit['showdate']);
		$edit_form->add_submit('submit','Submit');
		$tab_layout->add_tab('Edit Article',$edit_form);
		break;

// ----------------------------------------------------------------------------

	case 'editsave':
		try {
			news_edit($_POST['id'], $_POST['title'],
					$_POST['update_content'], $_POST['page'],
					$_POST['image'], $_POST['date_params']);
			$content .= 'Successfully edited article.<br />';
		}
		catch (Exception $e) {
			$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
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
	if ($acl->check_permission('news_publish')) {
		$form->add_select('publish','Publish',array(0,1),array('No','Yes'),get_config('news_default_publish_value'));
	}
	$form->add_submit('submit','Create Article');
	$tab_content['create'] = $form;
	$tab_layout->add_tab('Create Article',$tab_content['create']);
}

$content .= $tab_layout;
?>