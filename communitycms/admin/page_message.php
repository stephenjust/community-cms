<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

require_once(ROOT.'includes/acl/acl.php');
require_once(ROOT.'includes/PageMessage.class.php');
acl::get()->require_permission('adm_page_message');

// Get current page ID
if (!isset($_POST['page']) && !isset($_GET['page'])) {
	$page_id = get_config('home');
} elseif (!isset($_POST['page']) && isset($_GET['page'])) {
	$page_id = (int)$_GET['page'];
	unset($_GET['page']);
} else {
	$page_id = (int)$_POST['page'];
	unset($_POST['page']);
}

try {
	switch ($_GET['action']) {
		default: break;

		case 'delete':
			$pm = new PageMessage($_GET['id']);
			$pm->delete();
			echo 'Successfully deleted page message.<br />';
			break;
		
		case 'create':
			$_POST['start_year'] = (isset($_POST['start_year'])) ? $_POST['start_year'] : 0;
			$_POST['start_month'] = (isset($_POST['start_month'])) ? $_POST['start_month'] : 0;
			$_POST['start_day'] = (isset($_POST['start_day'])) ? $_POST['start_day'] : 0;
			$_POST['end_year'] = (isset($_POST['end_year'])) ? $_POST['end_year'] : 0;
			$_POST['end_month'] = (isset($_POST['end_month'])) ? $_POST['end_month'] : 0;
			$_POST['end_day'] = (isset($_POST['end_day'])) ? $_POST['end_day'] : 0;
			$start = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'];
			$end = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'];
			$expire = (isset($_POST['expire'])) ? checkbox($_POST['expire']) : 0;
			PageMessage::create($page_id, $_POST['text'], $start, $end, (boolean)$expire);
			echo 'Successfully created page message.<br />';
			break;
	}
}
catch (Exception $e) {
	echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$tab_content['manage'] = '
	<select id="adm_page_message_page_list" name="page" onChange="update_page_message_list(\'-\')">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
$page_query_handle = $db->sql_query($page_query);
$i = 1;
while ($i <= $db->sql_num_rows($page_query_handle)) {
	$page = $db->sql_fetch_assoc($page_query_handle);
	if (!preg_match('/<LINK>/',$page['title'])) {
		if ($page['id'] == $page_id) {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
		} else {
			$tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
		}
	}
	$i++;
}
$tab_content['manage'] .= '</select><br />'."\n";
$tab_content['manage'] .= '<div id="adm_page_message_list">Loading...</div>'."\n";
$tab_content['manage'] .= '<script type="text/javascript">update_page_message_list(\''.$page_id.'\');</script>';
$tab_layout->add_tab('Manage Page Messages',$tab_content['manage']);

// Form to create new page message
if (acl::get()->check_permission('page_message_new')) {
	$form = new form;
	$form->set_target('admin.php?module=page_message&amp;action=create');
	$form->set_method('post');
	$form->add_textarea('text','Content',NULL,'rows="30"');
	$form->add_page_list('page', 'Page');
	$form->add_date('start','Start Date','MDY',NULL,"disabled");
	$form->add_date('end','End Date','MDY',NULL,"disabled");
	$form->add_checkbox('expire','Expire',NULL,"disabled");
	$form->add_submit('submit','Save');
	$create_form = $form;
	$tab_layout->add_tab('Create Page Message',$create_form);
}

echo $tab_layout;
