<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_newsletter')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

$tab_layout = new Tabs;

switch (FormUtil::get('action')) {
default:

    break;
case 'new':
    $_POST['file_list'] = (isset($_POST['file_list'])) ? $_POST['file_list'] : null;
    try {
        Newsletter::create(
            $_POST['label'],
            $_POST['file_list'],
            $_POST['page'], $_POST['year'], $_POST['month']
        );
        echo 'Successfully added newsletter entry.<br />';
    }
    catch (NewsletterException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
    break;
case 'delete':
    try {
        $nl = new Newsletter(FormUtil::get('id'));
        $nl->delete();
        echo 'Successfully deleted newsletter entry.<br />'."\n";
    }
    catch (NewsletterException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
    break;
case 'edit':
    $newsletter = new Newsletter(FormUtil::get('id'));
    $edit_form = new Form;
    $edit_form->set_target('admin.php?module=newsletter&action=editsave');
    $edit_form->set_method('post');
    $edit_form->add_textbox('label', 'Label', $newsletter->getLabel());
    $edit_form->add_hidden('id', $newsletter->getId());
    $edit_form->add_textbox('file', 'File', $newsletter->getPath(), 'size="35" disabled');
    $edit_form->add_select(
        'month', 'Month', array(1,2,3,4,5,6,7,8,9,10,11,12), array('January',
        'February','March','April','May','June','July','August','September','October',
        'November','December'), $newsletter->getMonth()
    );
    $edit_form->add_textbox('year', 'Year', $newsletter->getYear(), 'maxlength="4" size="4"');
    $edit_form->add_page_list('page', 'Page', 2, 0, $newsletter->getPage());
    $edit_form->add_submit('submit', 'Save Changes');
    $tab_content['edit'] = $edit_form;
    $tab_layout->add_tab('Edit Newsletter', $tab_content['edit']);
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
			SET `label` = :label,
			`month` = :month,
			`year` = :year,
			`page` = :page WHERE `id` = :id';
    try {
        DBConn::get()->query($edit_query, [":label" => $_POST['label'], ":month" => $_POST['month'], ":year" => $_POST['year'], ":page" => $_POST['page'], ":id" => $_POST['id']], DBConn::NOTHING);
        Log::addMessage('Edited newsletter \''.$_POST['label'].'\'');
        echo 'Updated newsletter entry.<br />'."\n";
    } catch (Exceptions\DBException $ex) {
        echo 'Failed to edit newsletter entry.<br />'."\n";
        break;
    }
    break;

case 'multi':
    $selected_items = FormUtil::postArray('selected', FILTER_VALIDATE_INT);
    $target_page = FormUtil::post('bulk_op_page', FILTER_VALIDATE_INT);
    if (!PageUtil::exists($target_page)) {
        break;
    }

    $edit_query = 'UPDATE `'.NEWSLETTER_TABLE.'`
                SET `page` = :page WHERE `id` = :id';
    foreach ($selected_items as $item) {
        $n = new Newsletter($item);
        try {
            DBConn::get()->query($edit_query, [":page" => $target_page, ":id" => $item], DBConn::NOTHING);
            Log::addMessage("Moved newsletter '{$n->getLabel()}'");
        } catch (Exceptions\DBException $ex) {
            echo 'Failed to edit newsletter entry.<br />'."\n";
            break;
        }
    }
    break;
}

$page = FormUtil::get('page', FILTER_DEFAULT, null, FormUtil::post('page', FILTER_DEFAULT, null, '*'));

$page_list = new UISelectPageList([
    "id" => "adm_newsletter_page_list",
    "pagetype" => 2,
    "onChange" => "update_newsletter_list('-')"]);
$page_list->addOption("*", "All Pages");
$page_list->setChecked($page);
$tab_content['manage'] = $page_list;
$tab_content['manage'] .= '<form method="post" action="admin.php?module=newsletter&amp;action=multi">';

$tab_content['manage'] .= '<div id="adm_newsletter_list">Loading...</div>';
$tab_content['manage'] .= '<script type="text/javascript">update_newsletter_list(\''.$page.'\');</script>';

$bulk_op_page_list = new UISelectPageList([
    "id" => "adm_newsletter_bulk_op_page_list",
    "name" => "bulk_op_page",
    "pagetype" => 2
]);
$bulk_op_submit = '<input type="submit" value="Submit" />';
$tab_content['manage'] .= "Move selected items to: $bulk_op_page_list $bulk_op_submit";
$tab_layout->add_tab('Manage Newsletters', $tab_content['manage']);

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('newsletter_create')) {
    $form = new Form;
    $form->set_target('admin.php?module=newsletter&action=new');
    $form->set_method('post');
    $form->add_textbox('label', 'Label');
    $form->add_file_list('file', 'File', 'newsletters');
    $form->add_file_upload('upload');
    $form->add_select(
        'month', 'Month', array(1,2,3,4,5,6,7,8,9,10,11,12), array('January',
        'February','March','April','May','June','July','August','September','October',
        'November','December'), date('m')
    );
    $form->add_textbox('year', 'Year', date('Y'), 'maxlength="4" size="4"');
    $form->add_page_list('page', 'Page', 2);
    $form->add_submit('submit', 'Create Newsletter');
    $tab_content['create'] = $form;
    $tab_layout->add_tab('Create Newsletter', $tab_content['create']);
}
echo $tab_layout;
