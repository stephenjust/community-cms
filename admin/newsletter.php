<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

require_once(ROOT.'includes/content/Newsletter.class.php');

global $acl;

if (!$acl->check_permission('adm_newsletter'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

$tab_layout = new Tabs;

switch ($_GET['action']) {
	default:

		break;
	case 'new':
		$_POST['file_list'] = (isset($_POST['file_list'])) ? $_POST['file_list'] : NULL;
		try {
			Newsletter::create($_POST['label'],
					$_POST['file_list'],
					$_POST['page'], $_POST['year'], $_POST['month']);
			echo 'Successfully added newsletter entry.<br />';
		}
		catch (NewsletterException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
		}
		break;
	case 'delete':
		try {
			$nl = new Newsletter($_GET['id']);
			$nl->delete();
			echo 'Successfully deleted newsletter entry.<br />'."\n";
		}
		catch (NewsletterException $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
		}
		break;
	case 'edit':
		if (!is_numeric($_GET['id'])) {
			echo 'Invalid newsletter ID.<br />'."\n";
			break;
		}
		$newsletter_info_query = 'SELECT * FROM `'.NEWSLETTER_TABLE.'`
			WHERE `id` = '.$_GET['id'].' LIMIT 1';
		$newsletter_info_handle = $db->sql_query($newsletter_info_query);
		if ($db->error[$newsletter_info_handle] === 1) {
			echo 'Failed to load newsletter information.<br />'."\n";
			break;
		}
		if ($db->sql_num_rows($newsletter_info_handle) != 1) {
			echo 'The newsletter you selected does not exist.<br />'."\n";
			break;
		}
		$newsletter_info = $db->sql_fetch_assoc($newsletter_info_handle);
		$edit_form = new form;
		$edit_form->set_target('admin.php?module=newsletter&action=editsave');
		$edit_form->set_method('post');
		$edit_form->add_textbox('label','Label',stripslashes($newsletter_info['label']));
		$edit_form->add_hidden('id',$newsletter_info['id']);
		$edit_form->add_textbox('file','File',$newsletter_info['path'],'size="35" disabled');
		$edit_form->add_select('month','Month',array(1,2,3,4,5,6,7,8,9,10,11,12),array('January',
			'February','March','April','May','June','July','August','September','October',
			'November','December'),$newsletter_info['month']);
		$edit_form->add_textbox('year','Year',$newsletter_info['year'],'maxlength="4" size="4"');
		$edit_form->add_page_list('page','Page',2,0,$newsletter_info['page']);
		$edit_form->add_submit('submit','Save Changes');
		$tab_content['edit'] = $edit_form;
		$tab_layout->add_tab('Edit Newsletter',$tab_content['edit']);
		break;
	case 'editsave':
		if (!is_numeric($_POST['id'])) {
			echo 'Invalid newsletter entry.<br />'."\n";
			break;
		}
		if (!is_numeric($_POST['month']) || !is_numeric($_POST['year'])) {
			echo 'Invalid newsletter date.<br />'."\n";
			break;
		}
		$edit_query = 'UPDATE `'.NEWSLETTER_TABLE.'`
			SET `label` = \''.addslashes($_POST['label']).'\',
			`month` = '.$_POST['month'].',
			`year` = '.$_POST['year'].',
			`page` = '.$_POST['page'].' WHERE `id` = '.$_POST['id'];
		$edit_handle = $db->sql_query($edit_query);
		if ($db->error[$edit_handle] === 1) {
			echo 'Failed to edit newsletter entry.<br />'."\n";
			break;
		} else {
			Log::addMessage('Edited newsletter \''.$_POST['label'].'\'');
			echo 'Updated newsletter entry.<br />'."\n";
		}
		break;
}

if (isset($_GET['page'])) {
	$_POST['page'] = $_GET['page'];
}

$tab_content['manage'] = '<select name="page" id="adm_newsletter_page_list" onChange="update_newsletter_list(\'-\')">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . '
	WHERE type = 2 ORDER BY title ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
$first = 0;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!isset($_POST['page'])) {
		$_POST['page'] = get_config('home');
		$first = 1;
	}
	if ($page['id'] == $_POST['page']) {
		$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.stripslashes($page['title']).'</option>'."\n";
	} else {
		$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.stripslashes($page['title']).'</option>'."\n";
		if ($first == 1 && $page['id'] != $_POST['page']) {
			$_POST['page'] = $page['id'];
			$first = 0;
		}
	}
	$i++;
}

// All pages
if ($_POST['page'] == '*') {
	$tab_content['manage'] .= '<option value="*" selected>All Pages</option>'."\n";
} else {
	$tab_content['manage'] .= '<option value="*">All Pages</option>'."\n";
}

$tab_content['manage'] .= '</select>';

$tab_content['manage'] .= '<div id="adm_newsletter_list">Loading...</div>';
$tab_content['manage'] .= '<script type="text/javascript">update_newsletter_list(\''.$_POST['page'].'\');</script>';
$tab_layout->add_tab('Manage Newsletters',$tab_content['manage']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('newsletter_create')) {
	$form = new form;
	$form->set_target('admin.php?module=newsletter&action=new');
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
}
echo $tab_layout;
?>