<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$root = "./";
$content = NULL;

// ----------------------------------------------------------------------------

if($_GET['action'] == 'save') {
    $content .= 'You can\'t do that yet.';
    // FIXME: Not yet implemented
}

// ----------------------------------------------------------------------------

// FIXME: Read current config from database

$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=news_settings&amp;action=save');
$form->set_method('post');
$form->add_select('date','Default Date View',array(0,1,2),array('Hide Date','Show Date','Show Mini'));;
$form->add_checkbox('author','Show Author');
$form->add_checkbox('etime','Show Edit Time');
$form->add_submit('submit','Save Configuration');
$tab_layout->add_tab('Configure Module',$form);
$content .= $tab_layout;

?>
