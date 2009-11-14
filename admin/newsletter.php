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

function delete_newsletter($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid newsletter ID',true,'delete_newsletter()');
		return false;
	}

	// Get newsletter info
	$newsletter_info_query = 'SELECT * FROM `'.NEWSLETTER_TABLE.'` WHERE
		`id` = '.$id.' LIMIT 1';
	$newsletter_info_handle = $db->sql_query($newsletter_info_query);
	if ($db->error[$newsletter_info_handle] === 1) {
		$debug->add_trace('Failed to read newsletter information',true,'delete_newsletter()');
		return false;
	}
	if ($db->sql_num_rows($newsletter_info_handle) != 1) {
		$debug->add_trace('Newsletter entry not found',false,'delete_newsletter()');
		return false;
	}
	$newsletter_info = $db->sql_fetch_assoc($newsletter_info_handle);

	$delete_article_query = 'DELETE FROM ' . NEWSLETTER_TABLE . '
		WHERE id = '.$_GET['id'];
	$delete_article = $db->sql_query($delete_article_query);
	if($db->error[$delete_article]) {
		return false;
	} else {
		log_action('Deleted newsletter \''.stripslashes($newsletter_info['label']).'\'');
		return true;
	}
}

$content = NULL;
$months = array('January','February','March','April','May','June','July',
	'August','September','October','November','December');
if ($_GET['action'] == 'new') {
	$_POST['file_list'] = (isset($_POST['file_list'])) ? $_POST['file_list'] : NULL;
	if (strlen($_POST['file_list']) <= 3) {
		$content .= 'No file selected.';
	} else {
		$new_article_query = 'INSERT INTO ' . NEWSLETTER_TABLE . "
			(label,page,year,month,path) VALUES
			('".$_POST['label']."',".$_POST['page'].",".$_POST['year'].",
			".$_POST['month'].",'".$_POST['file_list']."')";
		$new_article = $db->sql_query($new_article_query);
		if ($db->error[$new_article] === 1) {
			$content .= 'Failed to add article.<br />';
		} else {
			$page_query = 'SELECT title FROM ' . PAGE_TABLE . '
				WHERE id = '.$_POST['page'].' LIMIT 1';
			$page_handle = $db->sql_query($page_query);
			$page = $db->sql_fetch_assoc($page_handle);
			$content .= 'Successfully added newsletter entry. '.log_action('New newsletter \''.$_POST['label'].'\' added to '.$page['title']);
		}
	}
}
if($_GET['action'] == 'delete') {
	if(!delete_newsletter($_GET['id'])) {
		$content .= 'Failed to delete newsletter entry.<br />'."\n";
	} else {
		$content .= 'Successfully deleted newsletter entry.<br />'."\n";
	}
}
$tab_layout = new tabs;
$tab_content['manage'] = '<table class="admintable">
<tr><th colspan="4"><form method="post" action="admin.php?module=newsletter"><select name="page">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . '
	WHERE type = 2 ORDER BY list ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
$first = 0;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!isset($_POST['page'])) {
		$_POST['page'] = $site_info['home'];
		$first = 1;
	}
	if ($page['id'] == $_POST['page']) {
		$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
	} else {
		$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		if ($first == 1 && $page['id'] != $_POST['page']) {
			$_POST['page'] = $page['id'];
			$first = 0;
		}
	}
	$i++;
}
$tab_content['manage'] .= '</select><input type="submit" value="Change Page" /></form></th></tr>
	<tr><th width="350">Label:</th><th>Month</th><th>Year</th><th>Del</th></tr>';
// Get page message list in the order defined in the database. First is 0.
$nl_query = 'SELECT * FROM ' . NEWSLETTER_TABLE . '
	WHERE page = '.stripslashes($_POST['page']).' ORDER BY year DESC,month DESC';
$nl_handle = $db->sql_query($nl_query);
$i = 1;
if($db->sql_num_rows($nl_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="4">There are no newsletter entries on this page.</td></tr>';
}
while ($i <= $db->sql_num_rows($nl_handle)) {
	$nl = $db->sql_fetch_assoc($nl_handle);
	$tab_content['manage'] .= '<tr>
		<td class="adm_page_list_item">'.strip_tags(stripslashes($nl['label']),'<br>').'</td>
		<td>'.$months[$nl['month']-1].'</td><td>'.$nl['year'].'</td>
		<td><a href="?module=newsletter&action=delete&id='.$nl['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		</tr>';
	$i++;
}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Newsletters',$tab_content['manage']);
$form = new form;
$form->set_target('admin.php?module=newsletter&amp;action=new');
$form->set_method('post');
$form->add_textbox('label','Label');
$form->add_file_list('file','File','newsletters');
$form->add_file_upload('upload');
$form->add_select('month','Month',array(1,2,3,4,5,6,7,8,9,10,11,12),array('January',
	'February','March','April','May','June','July','August','September','October',
	'November','December'),date('m'));
$form->add_textbox('year','Year',date('Y'),'maxlength="4" size="4"');
$form->add_page_list('page','Page',2);
$form->add_submit('submit','Create Newsletter');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Newsletter',$tab_content['create']);
$content .= $tab_layout;
?>