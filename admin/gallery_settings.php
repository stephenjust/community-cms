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

if (!$acl->check_permission('adm_gallery_settings')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;

// ----------------------------------------------------------------------------

if($_GET['action'] == 'save') {
	set_config('gallery_app',$_POST['gallery_app']);
	set_config('gallery_dir',$_POST['gallery_dir']);
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=gallery_settings&amp;action=save');
$form->set_method('post');
$form->add_select('gallery_app',
		'Gallery Type',
		array('built-in','simpleviewer'),
		array('Built-In','SimpleViewer'),
		get_config('gallery_app'));
$form->add_textbox('gallery_dir',
		'Gallery Directory',
		get_config('gallery_dir'));
$form->add_submit('submit','Save Configuration');
$tab_content['settings'] = $form;
$tab_content['settings'] .= '<div id="_gallery_dir_check_" style="display: none;"></div>';
$tab_layout->add_tab('Gallery Settings',$tab_content['settings']);
$content .= $tab_layout;

?>
