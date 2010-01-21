<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$root = "./";
$content = NULL;

$news_config_query = 'SELECT * FROM `' . NEWS_CONFIG_TABLE . '` LIMIT 1';
$news_config_handle = $db->sql_query($news_config_query);
if ($db->error[$news_config_handle] === 1) {
    $content .= 'Could not load configuration from the database.<br />';
} elseif ($db->sql_num_rows($news_config_handle) == 0) {
    $content .= 'There is no configuration record in the database.<br />';
}
$news_config = $db->sql_fetch_assoc($news_config_handle);

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
		if (ereg('^'.$prefix.'_',$form_keys[$i])) {
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

/**
 * delete_article - Deletes one or more news articles
 * @global object $db
 * @global object $debug
 * @param mixed $article
 * @return boolean
 */
function delete_article($article) {
	global $db;
	global $debug;

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->add_trace('Given non-numeric input',false,'delete_article');
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT `news`.`id`,`news`.`name` FROM
			`' . NEWS_TABLE . '` `news` WHERE
			`news`.`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->add_trace('Query failed',true,'delete_article');
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->add_trace('Article not found',true,'delete_article');
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Delete article
        $delete_query = 'DELETE FROM `' . NEWS_TABLE . '`
			WHERE `id` = '.$current;
        $delete = $db->sql_query($delete_query);
        if ($db->error[$delete] === 1) {
            return false;
        } else {
            log_action('Deleted news article \''.stripslashes($info['name']).'\' ('.$info['id'].')');
        }

		unset($delete_query);
		unset($delete);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

function move_article($article,$new_location) {
	global $db;
	global $debug;

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	if (!is_numeric($new_location)) {
		$debug->add_trace('Given non-numeric input for new location',true,'move_article');
	}

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->add_trace('Given non-numeric input',true,'move_article');
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT `news`.`id`,`news`.`name` FROM
			`' . NEWS_TABLE . '` `news` WHERE
			`news`.`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->add_trace('Query failed',true,'move_article');
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->add_trace('Article not found',true,'move_article');
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Move article
        $move_query = 'UPDATE `' . NEWS_TABLE . '`
			SET `page` = '.$new_location.'
			WHERE `id` = '.$current;
        $move = $db->sql_query($move_query);
        if ($db->error[$move] === 1) {
            return false;
        } else {
            log_action('Moved news article \''.stripslashes($info['name']).'\'');
        }

		unset($move_query);
		unset($move);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

function copy_article($article,$new_location) {
	global $db;
	global $debug;

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	if (!is_numeric($new_location)) {
		$debug->add_trace('Given non-numeric input for new location',true,'copy_article');
	}

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->add_trace('Given non-numeric input',true,'copy_article');
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT * FROM
			`' . NEWS_TABLE . '` WHERE
			`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->add_trace('Query failed',true,'copy_article');
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->add_trace('Article not found',true,'copy_article');
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Move article
        $move_query = 'INSERT INTO `' . NEWS_TABLE . '`
			(`page`,`name`,`description`,`author`,`date`,`date_edited`,`image`,`showdate`)
			VALUES ('.$new_location.",'{$info['name']}','{$info['description']}','{$info['author']}',
			'{$info['date']}','{$info['date_edited']}','{$info['image']}',{$info['showdate']})";
        $move = $db->sql_query($move_query);
        if ($db->error[$move] === 1) {
            return false;
        } else {
            log_action('Copied news article \''.stripslashes($info['name']).'\'');
        }

		unset($move_query);
		unset($move);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
	default:

		break;
	case 'multi':
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
				$content .= 'Failed to delete article(s)<br />'."\n";
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

	case 'pin':
		if (!is_numeric($_GET['id'])) {
			$content .= 'Invalid entry.<br />'."\n";
			break;
		}
		$pin_query = 'UPDATE `'.NEWS_TABLE.'` SET `pin` = 1 WHERE `id` = '.$_GET['id'];
		$pin_handle = $db->sql_query($pin_query);
		if ($db->error[$pin_handle] === 1) {
			$content .= 'Failed to pin entry.<br />'."\n";
			break;
		} else {
			$content .= 'Successfully pinned entry.<br />'."\n";
		}
		break;
	case 'unpin':
		if (!is_numeric($_GET['id'])) {
			$content .= 'Invalid entry.<br />'."\n";
			break;
		}
		$pin_query = 'UPDATE `'.NEWS_TABLE.'` SET `pin` = 0 WHERE `id` = '.$_GET['id'];
		$pin_handle = $db->sql_query($pin_query);
		if ($db->error[$pin_handle] === 1) {
			$content .= 'Failed to unpin entry.<br />'."\n";
			break;
		} else {
			$content .= 'Successfully unpinned entry.<br />'."\n";
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
			$content .= 'Successfully added article. <br />'.log_action('Article \''.$title.'\' added to \''.$page_title.'\'');
		}
		break;
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$page_list = '<select name="page">';
$page_query = 'SELECT * FROM `' . PAGE_TABLE . '`
    WHERE `type` = 1 ORDER BY `title` ASC';
$page_query_handle = $db->sql_query($page_query);
for ($i = 1; $i <= $db->sql_num_rows($page_query_handle); $i++) {
    $page = $db->sql_fetch_assoc($page_query_handle);
    if (!isset($_POST['page'])) {
		if (isset($_GET['page'])) {
			$_POST['page'] = $_GET['page'];
		} else {
			$_POST['page'] = $page['id'];
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
$tab_content['manage'] = '<table class="admintable">';

// Change page form
$tab_content['manage'] .= '<tr><th colspan="4">
	<form method="POST" action="admin.php?module=news">'.$page_list
	.'<input type="submit" value="Change Page" /></form></th></tr>';
	
$tab_content['manage'] .= '<tr>
	<th width="1"></th>
	<th width="30">ID</th>
	<th>Title:</th>
	<th colspan="3"></th></tr>';

// Form for action on selected item(s)
$tab_content['manage'] .= '<form method="post" action="admin.php?module=news&amp;action=multi">
	<input type="hidden" name="page" value="'.$_POST['page'].'" />';

// Get page list in the order defined in the database. First is 0.
if ($_POST['page'] == '*') {
    $page_list_query = 'SELECT * FROM `' . NEWS_TABLE . '` ORDER BY `id` DESC';
} else {
    $page_list_query = 'SELECT * FROM `' . NEWS_TABLE . '`
		WHERE `page` = '.stripslashes($_POST['page']).' ORDER BY `pin` DESC, `id` DESC';
}
$page_list_handle = $db->sql_query($page_list_query);
$page_list_rows = $db->sql_num_rows($page_list_handle);
if ($page_list_rows == 0) {
    $tab_content['manage'] .= '<tr><td colspan="2"></td><td>There are no articles on this
        page.</td><td colspan="3"></td></tr>';
}
for ($i = 1; $i <= $page_list_rows; $i++) {
    $page_list = $db->sql_fetch_assoc($page_list_handle);
	if ($page_list['pin'] == 1) {
		$pin_link = '<a href="?module=news&amp;action=unpin&amp;id='.$page_list['id'].'&amp;page='.$_POST['page'].'">Unpin</a>';
	} else {
		$pin_link = '<a href="?module=news&amp;action=pin&amp;id='.$page_list['id'].'&amp;page='.$_POST['page'].'">Pin</a>';
	}
    $tab_content['manage'] .= '<tr><td>
		<input type="checkbox" name="item_'.$page_list['id'].'" /></td>
		<td>'.$page_list['id'].'</td><td>'.
        stripslashes($page_list['name']).'</td><td>
        <a href="?module=news&amp;action=delete&amp;id='.$page_list['id'].'&amp;page='.$_POST['page'].'">
        <img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px"
        height="16px" border="0px" /></a></td><td>
        <a href="?module=news_edit_article&amp;id='.$page_list['id'].'">
        <img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px"
        height="16px" border="0px" /></a></td>
		<td>'.$pin_link.'</td></tr>';
		} // FOR
$tab_content['manage'] .= '</table>'."\n";

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

$tab_content['manage'] .= 'With selected:<br />'."\n".
	'<input type="radio" id="a_del" name="news_action" value="del" />'."\n".
	'<label for="a_del" class="ws">Delete</label><br />'."\n".
	'<input type="radio" id="a_move" name="news_action" value="move" />'."\n".
	'<label for="a_move" class="ws">Move</label><br />'."\n".
	'<input type="radio" id="a_copy" name="news_action" value="copy" />'."\n".
	'<label for="a_copy" class="ws">Copy</label><br />'."\n".
	"$a_page_list\n".
	'<label for="a_where" class="wsl">Move/copy to:</label><br />'."\n";


$tab_content['manage'] .= '<input type="submit" value="Submit" />';

// End form for action on selected item(s)
$tab_content['manage'] .= '</form>'."\n";

$tab_layout->add_tab('Manage News',$tab_content['manage']);

$form = new form;
$form->set_target('admin.php?module=news&amp;action=new');
$form->set_method('post');
$form->add_textbox('title','Heading');
$form->add_hidden('author',$_SESSION['name']);
$form->add_textarea('content','Content',NULL,'rows="20"');
$form->add_page_list('page','Page',1,1);
$form->add_icon_list('image','Image','newsicons');
$form->add_select('date_params','Date Settings',array(0,1,2),array('Hide','Show','Show Mini'),$news_config['default_date_setting']);
$form->add_submit('submit','Create Article');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Article',$tab_content['create']);

$content .= $tab_layout;
?>