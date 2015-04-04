<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
global $acl;

if (!$acl->check_permission('adm_site_config')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

if ($_GET['action'] == 'save') {
    // We don't really need to escape any of this because the set_config()
    // function already does that. This will just cause issues from double-
    // escaping. Only not escaping footer for now.
    $site_name = addslashes(strip_tags($_POST['site_name']));
    $site_desc = addslashes(strip_tags($_POST['site_desc']));
    $site_url = addslashes(strip_tags($_POST['site_url']));
    $admin_email = addslashes(strip_tags($_POST['admin_email']));
    $cookie_name = addslashes($_POST['cookie_name']);
    $cookie_path = addslashes($_POST['cookie_path']);
    $password_expire = addslashes($_POST['password_expire']);
    $time_format = addslashes($_POST['time_format']);
    $tel_format = addslashes($_POST['tel_format']);
    $num_articles = (int)$_POST['num_articles'];
    $def_date = (int)$_POST['date'];
    $show_author = (isset($_POST['author'])) ? checkbox($_POST['author']) : 0;
    $show_edit = (isset($_POST['etime'])) ? checkbox($_POST['etime']) : 0;
    $def_pub_val = (int)$_POST['default_publish_value'];
    if (set_config('site_name', $site_name) 
        && set_config('site_url', $site_url) 
        && set_config('admin_email', $admin_email) 
        && set_config('comment', $site_desc) 
        && set_config('site_active', checkbox($_POST['active'])) 
        && set_config('cookie_name', $cookie_name) 
        && set_config('cookie_path', $cookie_path) 
        && set_config('password_expire', $password_expire) 
        && set_config('time_format', $time_format) 
        && set_config('tel_format', $tel_format) 
        && set_config('footer', $_POST['footer']) 
        && set_config('news_num_articles', $num_articles) 
        && set_config('news_default_date_setting', $def_date) 
        && set_config('news_show_author', $show_author) 
        && set_config('news_show_edit_time', $show_edit) 
        && set_config('news_default_publish_value', $def_pub_val) 
        && set_config('gallery_app', $_POST['gallery_app']) 
        && set_config('gallery_dir', $_POST['gallery_dir']) 
        && set_config('contacts_display_mode', $_POST['contacts_display_mode'])
    ) {
        echo 'Successfully edited site information.<br />'."\n";
        Log::addMessage('Updated site information.');
    } else {
        echo 'Failed to update site information.<br />'."\n";
    }
} // IF 'save'

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

$tab_content['config'] = null;
$form = new form;
$form->set_target('admin.php?module=site_config&action=save');
$form->set_method('post');
$form->add_heading('General Settings');
$form->add_textbox('site_name', 'Site Name', get_config('site_name'));
$form->add_textbox('site_desc', 'Site Description', get_config('comment'));
$form->add_textbox('site_url', 'Site URL', get_config('site_url'));
$form->add_checkbox('active', 'Site Active', get_config('site_active'));
$form->add_textbox('admin_email', 'Admin E-Mail Address', get_config('admin_email'));
$form->add_heading('General Display Settings');
$form->add_textarea('footer', 'Footer Text', get_config('footer'));
$form->add_select(
    'time_format', 'Time Format',
    array('g:i a','g:i A','h:i a','h:i A','G:i','H:i'),
    array('4:05 am','4:05 AM','04:05 am','04:05 AM','4:05','04:05'),
    get_config('time_format')
);
$form->add_select(
    'tel_format', 'Telephone Number Format',
    array('(###) ###-####',
            '###-###-####',
            '###.###.####'),
    array('(555) 555-1234',
            '555-555-1234',
            '555.555.1234'),
    get_config('tel_format')
);
$form->add_heading('User Settings');
$form->add_select(
    'password_expire', 'Password Expire Time',
    array('0','1209600','2592000','7776000','15552000','31104000'),
    array('No Expiration','2 Weeks','1 Month','3 Months','6 Months','1 Year'),
    get_config('password_expire')
);
$form->add_heading('Cookie Settings');
$form->add_textbox('cookie_name', 'Cookie Name', get_config('cookie_name'));
$form->add_textbox('cookie_path', 'Cookie Path', get_config('cookie_path'));
// TODO: template, disable messaging

$form->add_heading('News Settings');
$form->add_textbox('num_articles', '# Articles per Page', get_config('news_num_articles'), 'size="3" maxlength="3"');
$form->add_select(
    'date', 'Default Date View', array(0,1,2), array('Hide Date',
    'Show Date','Show Mini'), get_config('news_default_date_setting')
);
$form->add_checkbox('author', 'Show Author', get_config('news_show_author'));
$form->add_checkbox('etime', 'Show Edit Time', get_config('news_show_edit_time'));
$form->add_select('default_publish_value', 'Articles default to', array(0,1), array('Un-published','Published'), get_config('news_default_publish_value'));

$form->add_heading('Gallery Settings');
$form->add_select(
    'gallery_app',
    'Gallery Type',
    array('built-in','simpleviewer'),
    array('Built-In','SimpleViewer'),
    get_config('gallery_app')
);
$form->add_textbox(
    'gallery_dir',
    'Gallery Directory',
    get_config('gallery_dir')
);

$form->add_heading('Contact List Settings');
$form->add_select(
    'contacts_display_mode', 'Display Mode',
    array('card','compact'),
    array('Business Card','Compact'),
    get_config('contacts_display_mode')
);

$form->add_submit('submit', 'Save Configuration');
$tab_content['config'] .= $form;
$tab['config'] = $tab_layout->add_tab('Configuration', $tab_content['config']);
echo $tab_layout;

?>