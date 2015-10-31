<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2009-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_contacts_manage');

$tab_layout = new Tabs;

// ----------------------------------------------------------------------------

/**
 * Get contact list
 * @param integer $page Page ID
 * @return array Contact information (or false on failure)
 */
function contact_list($page = '*') 
{
    // Check parameters
    if (!is_numeric($page) && $page != '*') {
        Debug::get()->addMessage('Invalid parameter', true);
        return false;
    }
    $query_page = ($page != '*') ? ' WHERE `page` = :page' : null;
    $contact_list_query = 'SELECT * FROM `'.CONTACTS_TABLE.'`'.$query_page;
    try {
        $results = DBConn::get()->query($contact_list_query, [":page" => $page], DBConn::FETCH_ALL);
    } catch (Exceptions\DBException $ex) {
        throw new \Exception("Failed to fetch contact list.", $ex);
    }
    return $results;
}

// ----------------------------------------------------------------------------

switch (FormUtil::get('action')) {
default:
    break;

case 'delete':
    try {
        $c = new Contact(FormUtil::get('id'));
        $c->delete();
        echo 'Successfully deleted contact.<br />';
    }
    catch (ContactException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

case 'create':
    try {
        Contact::create(
            $_POST['name'],
            $_POST['title'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['email']
        );
        echo 'Successfully created contact.<br />'."\n";
    }
    catch (ContactException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

case 'edit':
    try {
        $c = new Contact(FormUtil::get('id'));

        // Create form
        $edit_form = new Form;
        $edit_form->set_method('post');
        $edit_form->set_target('admin.php?module=contacts_manage&action=editsave&id='.$c->getId());
        $edit_form->add_textbox('name', 'Name', $c->getName());
        $edit_form->add_textbox('title', 'Title', $c->getTitle());
        $edit_form->add_textbox('phone', 'Telephone', $c->getPhone());
        $edit_form->add_textbox('address', 'Address', $c->getAddress());
        $edit_form->add_textbox('email', 'E-Mail', $c->getEmail());
        $edit_form->add_submit('submit', 'Submit');

        $tab_content['edit'] = $edit_form;
        $tab_layout->add_tab('Edit Contact', $tab_content['edit']);
    }
    catch (ContactException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

// ----------------------------------------------------------------------------

case 'editsave':
    try {
        $c = new Contact(FormUtil::get('id'));
        $c->edit(
            $_POST['name'],
            $_POST['title'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['email']
        );
        echo 'Successfully edited contact.<br />';
    }
    catch (ContactException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
}

// ----------------------------------------------------------------------------

$contact_list = contact_list();
$tab_content['manage'] = null;
if (count($contact_list) == 0) {
    $tab_content['manage'] .= 'There are currently no contacts in the database.<br />'."\n";
} else {
    $tab_content['manage'] .= <<<EOT
<table class="admintable">
<tr>
<th width="10px">ID</th><th>Name</th><th colspan="2" width="10px"></th>
</tr>
EOT;
    foreach ($contact_list as $contact) {
        $tab_content['manage'] .= <<<EOT
<tr>
<td>{$contact['id']}</td>
<td>{$contact['name']}</td>
<td><a href="?module=contacts_manage&action=edit&id={$contact['id']}"><img src="<!-- \$IMAGE_PATH\$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
<td><a href="javascript:confirm_delete('?module=contacts_manage&amp;action=delete&amp;
	id={$contact['id']}')"><img src="<!-- \$IMAGE_PATH\$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
</tr>
EOT;
    }
    $tab_content['manage'] .= '</table>';
}
$tab_layout->add_tab('Manage Contacts', $tab_content['manage']);

// ----------------------------------------------------------------------------

// A contact list is the same thing as a contacts page. One list per page.
$tab_content['manage_lists'] = null;
$contact_lists = Contact::getContactLists();

if (count($contact_lists) == 0) {
    $tab_content['manage_lists'] .= 'No Contact Lists exist. Please create a new Contacts page to add one.<br />';
} else {
    $cn_select = new UISelect(
        array('name' => 'cl',
        'id' => 'adm_cl_list',
        'onChange' => 'update_cl_manager(\'-\')')
    );
    if (array_key_exists('page', $_POST)) { $cn_cur = $_POST['page'];
    }
    foreach ($contact_lists as $contact_list) {
        if (!isset($cn_cur)) {
            $cn_cur = $contact_list;
        }
        $cn_select->addOption($contact_list, PageUtil::getTitle($contact_list));
    }
    $cn_select->setChecked($cn_cur);
    $tab_content['manage_lists'] .= $cn_select."\n";
    $tab_content['manage_lists'] .= '<div id="adm_contact_list_manager">Loading...</div>'."\n";
    $tab_content['manage_lists'] .= '<script type="text/javascript">update_cl_manager(\''.$cn_cur.'\');</script>';
}
$tab_layout->add_tab('Contact Lists', $tab_content['manage_lists']);

// ----------------------------------------------------------------------------

$new_form = new Form;
$new_form->set_method('post');
$new_form->set_target('admin.php?module=contacts_manage&action=create');
$new_form->add_textbox('name', 'Name');
$new_form->add_textbox('title', 'Title');
$new_form->add_textbox('phone', 'Telephone');
$new_form->add_textbox('address', 'Address');
$new_form->add_textbox('email', 'E-Mail');
$new_form->add_submit('submit', 'Submit');

$tab_content['create'] = $new_form;
$tab_layout->add_tab('Create Contact', $tab_content['create']);

echo $tab_layout;
