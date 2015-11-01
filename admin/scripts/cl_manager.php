<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

/**#@+
 * @ignore
 */
define('ADMIN', 1);
define('SECURITY', 1);
define('ROOT', '../../');
/**#@-*/

$content = null;
require_once ROOT.'vendor/autoload.php';
require ROOT . 'include.php';

initialize('ajax');

if (!acl::get()->check_permission('adm_contacts_manage') || !acl::get()->check_permission('admin_access')) {
    die ('You do not have the necessary permissions to access this page.');
}

$page_id = FormUtil::get('page', FILTER_VALIDATE_INT);
if ($page_id === null) {
    die ('No page ID provided to script.');
}

switch (FormUtil::get('action')) {
default:
    break;
case 'add':
    try {
        $c = new Contact(FormUtil::get('id'));
        $c->addToList($page_id);
        $content .= 'Successfully added contact to the list.<br />';
    } catch (ContactException $e) {
        $content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'remove':
    try {
        $c = new Contact(FormUtil::get('id'));
        $c->deleteFromList($page_id);
        $content .= 'Successfully removed contact from the list.<br />';
    } catch (ContactException $e) {
        $content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'order':
    try {
        $c = new Contact(FormUtil::get('id'));
        $c->setListOrder(FormUtil::get('order'), $page_id);
        $content .= 'Saved list order.<br />';
    } catch (ContactException $e) {
        $content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
}

// Get contact list
$contact_list_query = 'SELECT `contacts`.*, `content`.`order`, `content`.`id` AS `cnt_id`
	FROM `'.CONTACTS_TABLE.'` `contacts`, `'.CONTENT_TABLE.'` `content`
	WHERE `content`.`ref_id` = `contacts`.`id`
	AND `content`.`page_id` = :page_id
	ORDER BY `content`.`order` ASC';
try {
    $results = DBConn::get()->query($contact_list_query, [":page_id" => $page_id], DBConn::FETCH_ALL);
} catch (Exceptions\DBException $ex) {
    throw new \Exception("Failed to load contact lists.");
}
$list_rows = array();
$contact_ids = array();
foreach ($results as $result) {
    $current_row = array();
    $contact_ids[] = $result['id'];
    $current_row[] = $result['id'];
    $current_row[] = HTML::schars($result['name']);
    if(acl::get()->check_permission('contacts_edit_lists')) {
        $current_row[] = '<a href="javascript:update_cl_manager_remove(\''.$result['id'].'\')">Remove</a>';
    }

    $current_row[] = '<input type="text" size="3" maxlength="11" id="cl_order_'.$result['id'].'" value="'.$result['order'].'" onBlur="update_cl_manager_order(\''.$result['id'].'\')" />';
    $list_rows[] = $current_row;
} // FOR

$label_array = array('ID','Name');
if (acl::get()->check_permission('contacts_edit_lists')) {
    $label_array[] = 'Delete';
}
$label_array[] = 'Order';

$content .= TableComponent::create($label_array, $list_rows);
$content .= '<input type="hidden" name="page" value="'.$page_id.'" />'."\n";
$content .= 'Add contact: '."\n";
$all_contacts = Contact::getAll();
if (count($all_contacts) === 0) {
    $content .= 'No contacts exist. Please create some contacts.<br />'."\n";
    echo $content;
    exit;
}
$cl_add_select = new UISelect(array('name' => 'cl_add_contact', 'id' => 'cl_add_contact'));
foreach ($all_contacts as $contact) {
    $cl_add_select->addOption($contact->getId(), $contact->getName());
}
$content .= $cl_add_select;
$content .= '<input type="hidden" id="cl_contact_ids" value="'.implode(',', $contact_ids).'" name="contact_ids" />'."\n";
$content .= '<input type="button" value="Add" onClick="update_cl_manager_add()" /><br />'."\n";

echo $content;

clean_up();
