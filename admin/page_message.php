<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_page_message');

// Get current page ID
if (!isset($_POST['page']) && !isset($_GET['page'])) {
    $page_id = SysConfig::get()->getValue('home');
} elseif (!isset($_POST['page']) && isset($_GET['page'])) {
    $page_id = (int)$_GET['page'];
    unset($_GET['page']);
} else {
    $page_id = (int)$_POST['page'];
    unset($_POST['page']);
}

try {
    switch ($_GET['action']) {
    default: 
        break;

    case 'delete':
        $pm = new PageMessage($_GET['id']);
        $pm->delete();
        echo 'Successfully deleted page message.<br />';
        break;
        
    case 'create':
        PageMessage::create($page_id, $_POST['text']);
        echo 'Successfully created page message.<br />';
        break;
    }
}
catch (\Exception $e) {
    echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;
$tab_content['manage'] = '
	<select id="adm_page_message_page_list" name="page" onChange="update_page_message_list(\'-\')">';
$page_query = 'SELECT * FROM ' . PAGE_TABLE . ' ORDER BY list ASC';
try {
    $results = DBConn::get()->query($page_query, [], DBConn::FETCH_ALL);
} catch (Exceptions\DBException $ex) {
    throw new \Exception("Failed to load page list.");
}
foreach ($results as $page) {
    if (!preg_match('/<LINK>/', $page['title'])) {
        if ($page['id'] == $page_id) {
            $tab_content['manage'] .= '<option value="'.$page['id'].'" selected />'.$page['title'].'</option>';
        } else {
            $tab_content['manage'] .= '<option value="'.$page['id'].'" />'.$page['title'].'</option>';
        }
    }
}

$tab_content['manage'] .= '</select><br />'."\n";
$tab_content['manage'] .= '<div id="adm_page_message_list">Loading...</div>'."\n";
$tab_content['manage'] .= '<script type="text/javascript">update_page_message_list(\''.$page_id.'\');</script>';
$tab_layout->add_tab('Manage Page Messages', $tab_content['manage']);

// Form to create new page message
if (acl::get()->check_permission('page_message_new')) {
    $form = new Form;
    $form->set_target('admin.php?module=page_message&action=create');
    $form->set_method('post');
    $form->add_textarea('text', 'Content', null, 'rows="30"');
    $form->add_page_list('page', 'Page');
    $form->add_submit('submit', 'Save');
    $create_form = $form;
    $tab_layout->add_tab('Create Page Message', $create_form);
}

echo $tab_layout;
