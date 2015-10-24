<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_user')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

switch ($_GET['action']) {
case 'delete':
    try {
        $u = new User($_GET['id']);
        $u->delete();
        echo 'Successfully deleted user.<br />';
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
        
case 'create':
    try {
        if (!isset($_POST['username']) || !isset($_POST['pass']) 
            || !isset($_POST['pass_conf']) || !isset($_POST['first_name']) 
            || !isset($_POST['surname']) || !isset($_POST['telephone']) 
            || !isset($_POST['address']) && isset($_POST['email'])
        ) {
            throw new \Exception('You did not fill out a required field.');
        }
        if ($_POST['pass'] != $_POST['pass_conf']) {
            throw new \Exception('Passwords do not match.'); 
        }
        $_POST['groups'] = (isset($_POST['groups'])) ? $_POST['groups'] : null;
        User::create(
            $_POST['username'], $_POST['pass'], $_POST['first_name'],
            $_POST['surname'], $_POST['telephone'], $_POST['address'],
            $_POST['email'], $_POST['title'], $_POST['groups']
        );

        echo "Account created.";
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

default:
    break;
}

$tab_layout = new Tabs;

// ----------------------------------------------------------------------------

$users = User::getAll();
$table_cols = ["ID", "Username", "Name", ""];
if (acl::get()->check_permission("user_delete")) {
    $table_cols[] = "";
}

$table_rows = [];
foreach ($users as $user) {
    $row = [$user->getId(), HTML::schars($user->getUsername()), HTML::schars($user->getName())];
    if (acl::get()->check_permission("user_delete")) {
        $row[] = HTML::link("?module=user&action=delete&id={$user->getId()}",
            HTML::templateImage('delete.png', 'Delete', null, 'width: 16px; height: 16px; border: 0;'));
    }
    $row[] = HTML::link("?module=user_edit&id={$user->getId()}",
        HTML::templateImage('edit.png', 'Edit', null, 'width: 16px; height: 16px; border: 0;'));
    $table_rows[] = $row;
}

$tab_layout->add_tab('Manage Users', TableComponent::create($table_cols, $table_rows));

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('user_create')) {
    $form = new Form;
    $form->set_target('admin.php?module=user&action=create');
    $form->set_method('post');
    $form->add_textbox('username', 'User Name');
    $form->add_password('pass', 'Password');
    $form->add_password('pass_conf', 'Confirm Password');
    $form->add_textbox('first_name', 'First Name');
    $form->add_textbox('surname', 'Surname');
    $form->add_textbox('title', 'Title/Position');
    $form->add_textbox('telephone', 'Phone Number');
    $form->add_textbox('address', 'Address');
    $form->add_textbox('email', 'Email Address');
    $user_groups = UserGroup::getAll();
    if (count($user_groups) == 0) {
        $form->add_text(' An error may have occured. No groups were found.');
    } else {
        $group_list_id = $group_list_name = [];
        foreach ($user_groups as $user_group) {
            $group_list_id[] = $user_group->getId();
            $group_list_name[] = $user_group->getLabel();
        }
        $form->add_multiselect('groups', 'Groups', $group_list_id, $group_list_name, null, 5, 'style="height: 4em;"');
    }
    $form->add_submit('submit', 'Create User');

    $tab_layout->add_tab('Create User', $form);
}

echo $tab_layout;
