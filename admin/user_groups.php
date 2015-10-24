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

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

require_once(ROOT.'includes/acl/acl_functions.php');

function perm_list($group = 0) 
{
    $return = null;
    $permission_list = acl::get()->permission_list;
    $form_var_list = array_keys($permission_list);
    $form_var_list = implode(',', $form_var_list);
    $return .= '<input type="hidden" name="var_list" value="'.$form_var_list.'" />';

    $return .= permission_list(acl::get()->permission_list, $group, true);
    return $return;
}

if (!acl::get()->check_permission('adm_user_groups')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

if ($_GET['action'] == 'delete') {
    try {
        $group = new UserGroup($_GET['id']);
        $group->delete();
        echo 'Successfully deleted group.<br />';
    } catch (\Exception $ex) {
        echo '<span class="errormessage">'.$ex->getMessage().'</span><br />';
    }
}

if ($_GET['action'] == 'new') {
    if (acl::get()->check_permission('group_create')) {
        try {
            UserGroup::create($_POST['group_name'], $_POST['label_format']);
            echo 'Created group \''.$_POST['group_name'].'\'.<br />';
        } catch (\Exception $ex) {
            echo '<span class="errormessage">'.$ex->getMessage().'</span><br />';
        }
    }
}

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'permsave') {
    $set_perm_error = 0;
    if (!isset($_POST['id']) || !isset($_POST['var_list'])) {
        echo '<span class="errormessage">Failed to update permissions.</span><br />';
    } else {
        $var_list = explode(',', $_POST['var_list']);
        $id = (int)$_POST['id'];
        unset($_POST['id']);
        foreach ($var_list as $form_var) {
            if (!isset($_POST[$form_var])) {
                $form_var_value = null;
            } else {
                $form_var_value = $_POST[$form_var];
            }
            $new_setting = checkbox($form_var_value);
            if (array_key_exists($form_var, acl::get()->permission_list)) {
                $set_perm = acl::get()->set_permission($form_var, $new_setting, $id, true);
                if (!$set_perm) {
                    $set_perm_error = 1;
                }
                unset($set_perm);
            } else {
                Debug::get()->addMessage('Permission \''.$form_var.'\' does not exist', true);
            }
        }
        unset($form_var);
        unset($form_var_value);
        if ($set_perm_error == 0) {
            echo 'Updated permissions for group.<br />';
            Log::addMessage('Updated group permissions');
        } else {
            echo '<span class="errormessage">Failed to update permissions.</span><br />';
        }
    }
    // in_array($string,$array)
}

// ----------------------------------------------------------------------------

$tab_layout = new Tabs;

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'perm') {
    $tab_content['permission'] = '<form method="post" action="admin.php?module=user_groups&action=permsave">
		<input type="hidden" name="id" value="'.(int)$_GET['id'].'" />';
    $tab_content['permission'] .= perm_list((int)$_GET['id']);
    $tab_content['permission'] .= '<input type="submit" value="Save" /></form>';
    unset($permission);

    $tab_layout->add_tab('Manage Group Permissions', $tab_content['permission']);
}

// ----------------------------------------------------------------------------

$user_groups = UserGroup::getAll();
$group_rows = [];
foreach ($user_groups as $user_group) {
    $group_rows[] = [
        $user_group->getId(),
        sprintf('<span style="%s">%s</span>', $user_group->getLabelCss(), $user_group->getLabel()),
        HTML::link("admin.php?module=user_groups&action=delete&id={$user_group->getId()}",
            HTML::templateImage("delete.png", "Delete")),
        HTML::link("admin.php?module=user_groups&action=perm&id={$user_group->getId()}",
            Html::templateImage("permissions.png", "Permissions"))
    ];
}

$tab['manage'] = $tab_layout->add_tab('Manage Groups', Component\TableComponent::create(["ID", "Name", "", ""], $group_rows));

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('group_create')) {
    $tab_content['create'] = null;
    $tab_content['create'] .= '<form method="POST" action="admin.php?module=user_groups&action=new"><table class="admintable">
		<tr><td>Group Name:</td><td><input type="text" name="group_name" /></td>
		</tr>
		<tr><td>Styling:</td><td><input type="text" name="label_format" />CSS Code</td>
		</tr>
		<tr><td class="empty"></td><td><input type="submit" value="Create Group" /></td>
		</tr>
		</table></form>';
    $tab['create'] = $tab_layout->add_tab('Create Group', $tab_content['create']);
}

echo $tab_layout;
