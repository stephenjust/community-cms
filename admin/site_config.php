<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2015 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_site_config')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

$action = FormUtil::get('action');
if ($action == 'save') {
    try {
        SysConfig::get()->setValue('site_name', FormUtil::post('site_name', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('site_url', FormUtil::post('site_url', FILTER_SANITIZE_URL));
        SysConfig::get()->setValue('admin_email', FormUtil::post('admin_email', FILTER_SANITIZE_EMAIL));
        SysConfig::get()->setValue('comment', FormUtil::post('site_desc', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('site_active', FormUtil::postCheckbox('active'));
        SysConfig::get()->setValue('cookie_name', FormUtil::post('cookie_name', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('cookie_path', FormUtil::post('cookie_path', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('password_expire', FormUtil::post('password_expire', FILTER_SANITIZE_NUMBER_INT));
        SysConfig::get()->setValue('time_format', FormUtil::post('time_format', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('tel_format', FormUtil::post('tel_format', FILTER_SANITIZE_STRING));
        SysConfig::get()->setValue('footer', FormUtil::post('footer', FILTER_UNSAFE_RAW));
        SysConfig::get()->setValue('news_num_articles', FormUtil::post('num_articles', FILTER_SANITIZE_NUMBER_INT));
        SysConfig::get()->setValue('news_default_date_setting', FormUtil::post('date', FILTER_SANITIZE_NUMBER_INT));
        SysConfig::get()->setValue('news_show_author', FormUtil::postCheckbox('author'));
        SysConfig::get()->setValue('news_show_edit_time', FormUtil::postCheckbox('etime'));
        SysConfig::get()->setValue('news_default_publish_value', FormUtil::post('default_publish_value', FILTER_SANITIZE_NUMBER_INT));
        SysConfig::get()->setValue('contacts_display_mode', FormUtil::post('contacts_display_mode', FILTER_SANITIZE_STRING));
        echo 'Successfully edited site information.<br />'."\n";
        Log::addMessage('Updated site information.');
    } catch (\Exception $ex) {
        echo 'Failed to update site information.<br />'."\n";
    }
} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

$tab_content['config'] = null;
$form = new Form;
$form->set_target('admin.php?module=site_config&action=save');
$form->set_method('post');
$form->add_heading('General Settings');
$form->add_textbox('site_name', 'Site Name', SysConfig::get()->getValue('site_name'));
$form->add_textbox('site_desc', 'Site Description', SysConfig::get()->getValue('comment'));
$form->add_textbox('site_url', 'Site URL', SysConfig::get()->getValue('site_url'));
$form->add_checkbox('active', 'Site Active', SysConfig::get()->getValue('site_active'));
$form->add_textbox('admin_email', 'Admin E-Mail Address', SysConfig::get()->getValue('admin_email'));
$form->add_heading('General Display Settings');
$form->add_textarea('footer', 'Footer Text', SysConfig::get()->getValue('footer'));
$form->add_select(
    'time_format', 'Time Format',
    array('g:i a','g:i A','h:i a','h:i A','G:i','H:i'),
    array('4:05 am','4:05 AM','04:05 am','04:05 AM','4:05','04:05'),
    SysConfig::get()->getValue('time_format')
);
$form->add_select(
    'tel_format', 'Telephone Number Format',
    array('(###) ###-####',
            '###-###-####',
            '###.###.####'),
    array('(555) 555-1234',
            '555-555-1234',
            '555.555.1234'),
    SysConfig::get()->getValue('tel_format')
);
$form->add_heading('User Settings');
$form->add_select(
    'password_expire', 'Password Expire Time',
    array('0','1209600','2592000','7776000','15552000','31104000'),
    array('No Expiration','2 Weeks','1 Month','3 Months','6 Months','1 Year'),
    SysConfig::get()->getValue('password_expire')
);
$form->add_heading('Cookie Settings');
$form->add_textbox('cookie_name', 'Cookie Name', SysConfig::get()->getValue('cookie_name'));
$form->add_textbox('cookie_path', 'Cookie Path', SysConfig::get()->getValue('cookie_path'));
// TODO: template, disable messaging

$form->add_heading('News Settings');
$form->add_textbox('num_articles', '# Articles per Page', SysConfig::get()->getValue('news_num_articles'), 'size="3" maxlength="3"');
$form->add_select(
    'date', 'Default Date View', array(0,1,2), array('Hide Date',
    'Show Date','Show Mini'), SysConfig::get()->getValue('news_default_date_setting')
);
$form->add_checkbox('author', 'Show Author', SysConfig::get()->getValue('news_show_author'));
$form->add_checkbox('etime', 'Show Edit Time', SysConfig::get()->getValue('news_show_edit_time'));
$form->add_select('default_publish_value', 'Articles default to', array(0,1), array('Un-published','Published'), SysConfig::get()->getValue('news_default_publish_value'));

$form->add_heading('Contact List Settings');
$form->add_select(
    'contacts_display_mode', 'Display Mode',
    array('card','compact'),
    array('Business Card','Compact'),
    SysConfig::get()->getValue('contacts_display_mode')
);

$form->add_submit('submit', 'Save Configuration');
$tab_content['config'] .= $form;
$tab['config'] = $tab_layout->add_tab('Configuration', $tab_content['config']);
echo $tab_layout;
