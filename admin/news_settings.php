<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$root = "./";
$content = NULL;

// ----------------------------------------------------------------------------

if($_GET['action'] == 'save') {
    $def_date = (int)$_POST['date'];
    $show_author = (int)checkbox($_POST['author']);
    $show_edit = (int)checkbox($_POST['etime']);
    $news_config_update_query = 'UPDATE '.$CONFIG['db_prefix']."news_settings
        SET `default_date_setting`='$def_date',`show_author`='$show_author',
        `show_edit_time`='$show_edit'";
    $news_config_update_handle = $db->query($news_config_update_query);
    if (!$news_config_update_handle) {
        $content .= 'Failed to update configuration.<br />';
    } else {
        $content .= 'Successfully updated configuration.<br />'.
            log_action('Updated news configuration');
    }
}

// ----------------------------------------------------------------------------

$news_config_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news_settings LIMIT 1';
$news_config_handle = $db->query($news_config_query);
if (!$news_config_handle) {
    $content .= 'Could not load configuration from the database.<br />';
} elseif ($news_config_handle->num_rows == 0) {
    $content .= 'There is no configuration record in the database.<br />';
}
$news_config = $news_config_handle->fetch_assoc();

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
