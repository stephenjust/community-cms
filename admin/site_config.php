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
$content = NULL;

if (!$acl->check_permission('adm_site_config')) {
	$content .= 'You do not have the necessary permissions to access this module.';
	return true;
}

if ($_GET['action'] == 'save') {
	$site_name = addslashes(strip_tags($_POST['site_name']));
	$site_desc = addslashes(strip_tags($_POST['site_desc']));
	$site_url = addslashes(strip_tags($_POST['site_url']));
	$admin_email = addslashes(strip_tags($_POST['admin_email']));
	$time_format = addslashes($_POST['time_format']);
	$footer = addslashes($_POST['footer']);
	$site_info_update_query = 'UPDATE ' . CONFIG_TABLE . "
		SET `name`='$site_name',`url`='$site_url',`admin_email`='$admin_email',
		`comment`='$site_desc',`active`=".checkbox($_POST['active']).",
		`time_format`='$time_format',`footer`='$footer'";
	$site_info_update_handle = $db->sql_query($site_info_update_query);
	if ($db->error[$site_info_update_handle] === 1) {
		$content .= 'Failed to update site information.<br />';
	} else {
		$content .= 'Successfully edited site information.<br />'.log_action('Updated site information.');
	}
} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$tab_content['config'] = NULL;
$config_query = 'SELECT * FROM ' . CONFIG_TABLE . ' LIMIT 1';
$config_handle = $db->sql_query($config_query);
if (!$config_handle) {
	$tab_content['config'] .= 'Failed to load configuration information from the database.';
}
$current_config = $db->sql_fetch_assoc($config_handle);
$form = new form;
$form->set_target('admin.php?module=site_config&amp;action=save');
$form->set_method('post');
$form->add_textbox('site_name','Site Name',stripslashes($current_config['name']));
$form->add_textbox('site_desc','Site Description',stripslashes($current_config['comment']));
$form->add_textbox('site_url','Site URL',stripslashes($current_config['url']));
$form->add_textbox('admin_email','Admin E-Mail Address',stripslashes($current_config['admin_email']));
$form->add_select('time_format','Time Format',
		array('g:i a','g:i A','h:i a','h:i A','G:i','H:i'),
		array('4:05 am','4:05 AM','04:05 am','04:05 AM','4:05','04:05'),
		stripslashes($current_config['time_format']));
$form->add_textarea('footer','Footer Text',stripslashes($current_config['footer']));
$form->add_checkbox('active','Site Active',$current_config['active']);
// TODO: Cookie domain, path, name, template, disable messaging
$form->add_submit('submit','Save Configuration');
$tab_content['config'] .= $form;
$tab['config'] = $tab_layout->add_tab('Configuration',$tab_content['config']);
$content .= $tab_layout;

?>