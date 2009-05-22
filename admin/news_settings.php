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

// ----------------------------------------------------------------------------

if($_GET['action'] == 'save') {
    $def_date = (int)$_POST['date'];
    $show_author = (isset($_POST['author'])) ? checkbox($_POST['author']) : 0;
    $show_edit = (isset($_POST['etime'])) ? checkbox($_POST['etime']) : 0;
    $news_config_update_query = 'UPDATE ' . NEWS_CONFIG_TABLE . "
        SET default_date_setting='$def_date',show_author='$show_author',
        show_edit_time='$show_edit'";
    $news_config_update_handle = $db->sql_query($news_config_update_query);
    if ($db->error[$news_config_update_handle] === 1) {
        $content .= 'Failed to update configuration.<br />';
    } else {
        $content .= 'Successfully updated configuration.<br />'.
            log_action('Updated news configuration');
    }
}

// ----------------------------------------------------------------------------

$news_config_query = 'SELECT * FROM ' . NEWS_CONFIG_TABLE . ' LIMIT 1';
$news_config_handle = $db->sql_query($news_config_query);
if ($db->error[$news_config_handle] === 1) {
    $content .= 'Could not load configuration from the database.<br />';
} elseif ($db->sql_num_rows($news_config_handle) == 0) {
    $content .= 'There is no configuration record in the database.<br />';
}
$news_config = $db->sql_fetch_assoc($news_config_handle);

$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=news_settings&amp;action=save');
$form->set_method('post');
$form->add_select('date','Default Date View',array(0,1,2),array('Hide Date',
    'Show Date','Show Mini'),$news_config['default_date_setting'] + 1);
$form->add_checkbox('author','Show Author',$news_config['show_author']);
$form->add_checkbox('etime','Show Edit Time',$news_config['show_edit_time']);
$form->add_submit('submit','Save Configuration');
$tab_layout->add_tab('Configure Module',$form);
$content .= $tab_layout;

?>
