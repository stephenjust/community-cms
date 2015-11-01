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

acl::get()->require_permission('adm_user_groups');

$tab_layout = new Tabs;

switch (FormUtil::get('action')) {
    case 'delete':
        try {
            $group = new UserGroup(FormUtil::get('id'));
            $group->delete();
            echo 'Successfully deleted group.<br />';
        } catch (\Exception $ex) {
            echo '<span class="errormessage">'.$ex->getMessage().'</span><br />';
        }
        break;
    case 'new':
        if (acl::get()->check_permission('group_create')) {
            try {
                UserGroup::create($_POST['group_name'], $_POST['label_format']);
                echo 'Created group \''.$_POST['group_name'].'\'.<br />';
            } catch (\Exception $ex) {
                echo '<span class="errormessage">'.$ex->getMessage().'</span><br />';
            }
        }
        break;
    case 'permsave':
        $set_perm_error = 0;
        if (!FormUtil::post('id') || !FormUtil::post('var_list')) {
            echo '<span class="errormessage">Failed to update permissions.</span><br />';
            break;
        }
        $var_list = explode(',', FormUtil::post('var_list'));
        $id = FormUtil::post('id');
        foreach ($var_list as $form_var) {
            $new_setting = FormUtil::postCheckbox($form_var);
            if (array_key_exists($form_var, acl::get()->permission_list)) {
                $set_perm = acl::get()->set_permission($form_var, $new_setting, $id, true);
                if (!$set_perm) {
                    $set_perm_error = 1;
                }
            } else {
                Debug::get()->addMessage('Permission \''.$form_var.'\' does not exist', true);
            }
        }
        if ($set_perm_error == 0) {
            echo 'Updated permissions for group.<br />';
            Log::addMessage('Updated group permissions');
        } else {
            echo '<span class="errormessage">Failed to update permissions.</span><br />';
        }
        break;
    case 'perm':
        $tab_content['permission'] = '<form method="post" action="admin.php?module=user_groups&action=permsave">
            <input type="hidden" name="id" value="'.FormUtil::get('id').'" />';
        $tab_content['permission'] .= perm_list(FormUtil::get('id'));
        $tab_content['permission'] .= '<input type="submit" value="Save" /></form>';

        $tab_layout->add_tab('Manage Group Permissions', $tab_content['permission']);
        break;
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
