<?php
/**
 * Community CMS
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
	$cookie_name = addslashes($_POST['cookie_name']);
	$cookie_path = addslashes($_POST['cookie_path']);
	$time_format = addslashes($_POST['time_format']);
	$tel_format = addslashes($_POST['tel_format']);
	$footer = addslashes($_POST['footer']);
	if (set_config('site_name',$site_name) &&
		set_config('site_url',$site_url) &&
		set_config('admin_email',$admin_email) &&
		set_config('comment',$site_desc) &&
		set_config('site_active',checkbox($_POST['active'])) &&
		set_config('cookie_name',$cookie_name) &&
		set_config('cookie_path',$cookie_path) &&
		set_config('time_format',$time_format) &&
		set_config('tel_format',$tel_format) &&
		set_config('footer',$footer))
	{
		$content .= 'Successfully edited site information.<br />'."\n";
		log_action('Updated site information.');
	} else {
		$content .= 'Failed to update site information.<br />'."\n";
	}
} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$tab_content['config'] = NULL;
$form = new form;
$form->set_target('admin.php?module=site_config&amp;action=save');
$form->set_method('post');
$form->add_textbox('site_name','Site Name',get_config('site_name'));
$form->add_textbox('site_desc','Site Description',get_config('comment'));
$form->add_textbox('site_url','Site URL',get_config('site_url'));
$form->add_textbox('admin_email','Admin E-Mail Address',get_config('admin_email'));
$form->add_select('time_format','Time Format',
		array('g:i a','g:i A','h:i a','h:i A','G:i','H:i'),
		array('4:05 am','4:05 AM','04:05 am','04:05 AM','4:05','04:05'),
		get_config('time_format'));
$form->add_select('tel_format','Telephone Number Format',
		array('(###) ###-####',
			'###-###-####',
			'###.###.####'),
		array('(555) 555-1234',
			'555-555-1234',
			'555.555.1234'),
		get_config('tel_format'));
$form->add_textarea('footer','Footer Text',stripslashes(get_config('footer')));
$form->add_textbox('cookie_name','Cookie Name',get_config('cookie_name'));
$form->add_textbox('cookie_path','Cookie Path',get_config('cookie_path'));
$form->add_checkbox('active','Site Active',get_config('site_active'));
// TODO: template, disable messaging
$form->add_submit('submit','Save Configuration');
$tab_content['config'] .= $form;
$tab['config'] = $tab_layout->add_tab('Configuration',$tab_content['config']);
$content .= $tab_layout;

?>