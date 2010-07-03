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

// ----------------------------------------------------------------------------

if($_GET['action'] == 'save') {
	$num_articles = (int)$_POST['num_articles'];
	$def_date = (int)$_POST['date'];
	$show_author = (isset($_POST['author'])) ? checkbox($_POST['author']) : 0;
	$show_edit = (isset($_POST['etime'])) ? checkbox($_POST['etime']) : 0;
	if (!set_config('news_num_articles',$num_articles) ||
			!set_config('news_default_date_setting',$def_date) ||
			!set_config('news_show_author',$show_author) ||
			!set_config('news_show_edit_time',$show_edit)) {
		$content .= 'Failed to update configuration.<br />';
	} else {
		$content .= 'Successfully updated configuration.<br />'.
			log_action('Updated news configuration');
	}
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=news_settings&amp;action=save');
$form->set_method('post');
$form->add_textbox('num_articles','# Articles per Page',get_config('news_num_articles'),'size="3" maxlength="3"');
$form->add_select('date','Default Date View',array(0,1,2),array('Hide Date',
	'Show Date','Show Mini'),get_config('news_default_date_setting'));
$form->add_checkbox('author','Show Author',get_config('news_show_author'));
$form->add_checkbox('etime','Show Edit Time',get_config('news_show_edit_time'));
$form->add_submit('submit','Save Configuration');
$tab_layout->add_tab('Configure Module',$form);
$content .= $tab_layout;

?>
