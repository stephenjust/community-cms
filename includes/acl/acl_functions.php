<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;
/**
 * @ignore
 */
if (!defined('SECURITY')) {
    exit;
}

/**
 * Return the permission_list.xml file in array form
 * @return array boolean Permission list, false on failure
 */
function permission_file_read() 
{
    $xml_file = ROOT.'includes/acl/permission_list.xml';
    $permissions = array();
    $category_count = 0;
    $xmlreader = new XMLReader;
    // Open XML file
    if (!$xmlreader->open($xml_file)) {
        return false;
    }
    // Step through each element in the file
    while ($xmlreader->read()) {
        // Skip comments and other useless nodes
        if ($xmlreader->nodeType == XMLREADER::DOC_TYPE 
            || $xmlreader->nodeType == XMLREADER::COMMENT 
            || $xmlreader->nodeType == XMLREADER::XML_DECLARATION
        ) {
            continue;
        }
        // Handle categories
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
            $key_count = 0;
            $cat_name = $xmlreader->getAttribute('name');
            $permissions[$category_count] = array();
            $permissions[$category_count]['name'] = $cat_name;
            $permissions[$category_count]['items'] = array();
        }
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {
            $category_count++;
        }
        // Handle discrete items
        if ($xmlreader->name == 'key' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
            $key_name = $xmlreader->getAttribute('name');
            $key_title = $xmlreader->getAttribute('title');
            $key_description = $xmlreader->getAttribute('description');
            $key_default = $xmlreader->getAttribute('default');
            $permissions[$category_count]['items'][$key_count]['name'] = $key_name;
            $permissions[$category_count]['items'][$key_count]['title'] = $key_title;
            $permissions[$category_count]['items'][$key_count]['description'] = $key_description;
            $permissions[$category_count]['items'][$key_count]['regex'] = false;
            if ($key_default == '') {
                $permissions[$category_count]['items'][$key_count]['default'] = 0;
            } else {
                $permissions[$category_count]['items'][$key_count]['default'] = 1;
            }
            $key_count++;
        }
        // Handle regex items
        if ($xmlreader->name == 'key_range' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
            $key_name = $xmlreader->getAttribute('regex');
            $permissions[$category_count]['items'][$key_count]['name'] = $key_name;
            $permissions[$category_count]['items'][$key_count]['regex'] = true;
            $key_count++;
        }
    }

    $xmlreader->close();
    return $permissions;
}

/**
 * Create a permission list with HTML tables
 * @global acl $acl
 * @global Debug $debug
 * @param array   $permission_list
 * @param integer $group
 * @param boolean $form
 * @return string
 */
function permission_list($permission_list,$group = 0,$form = false) 
{
    global $acl;
    global $debug;

    $permission_file_array = permission_file_read();
    $xml_file = ROOT.'includes/acl/permission_list.xml';
    $sorted_permissions = array();
    $category_count = 0;
    $return = null;
    $permission_list_base = $permission_list;
    $permission_list = array_keys($permission_list);

    // Find the difference between the list of permissions in the xml file
    // and the list in the database so that regex's can be handled.
    foreach($permission_file_array AS $permission_file_cats) {
        for ($i = 0; $i < count($permission_file_cats['items']); $i++) {
            $permission_index = array_search($permission_file_cats['items'][$i]['name'], $permission_list);
            if ($permission_index !== false) {
                unset($permission_list[$permission_index]);
            }
        }
    }

    // Reset array key numbering to 0,1,2...
    $permission_list = array_values($permission_list);

    foreach ($permission_file_array AS $cat_permissions) {
        $return .= '<h3>'.$cat_permissions['name'].'</h3>'."\n";
        if (count($cat_permissions['items']) == 0) {
            $return .= 'This category contains no permission values.<br />'."\n";
        } else {
            $return .= '<table class="admintable">';
            if ($form === true) {
                // Make sure there is an extra header column to conpensate for
                // checkboxes that will be added
                $return .= '<tr><th width="1px"></th><th>Name</th><th>Description</th></tr>'."\n";
            } else {
                $return .= '<tr><th>Name</th><th>Description</th></tr>'."\n";
            }
            foreach ($cat_permissions['items'] AS $cat_items) {
                $items = array();
                // Handle regex entries
                if ($cat_items['regex'] === true) {
                    for ($i = 0; $i < count($permission_list); $i++) {
                        if (preg_match($cat_items['name'], $permission_list[$i])) {
                            $permission = $permission_list[$i];
                            $items[] = array('name'=>$permission,
                            'title'=>$permission_list_base[$permission]['longname'],
                            'description'=>$permission_list_base[$permission]['description']);
                            unset($permission_list[$i]);
                        }
                    }
                    // Reset array numbering to 0,1,2...
                    $permission_list = array_values($permission_list);
                } else {
                    $items[0] = $cat_items;
                }
                for ($i = 0; $i < count($items); $i++) {
                    $return .= '<tr>'."\n";
                    if ($form === true) {
                        // Need to add a checkbox
                        $perm = $acl->check_permission($items[$i]['name'], $group, false);
                        $return .= "\t".'<td>';
                        if ($perm === true) {
                            $return .= '<input type="checkbox" name="'.$items[$i]['name'].'" checked />';
                        } else {
                            $return .= '<input type="checkbox" name="'.$items[$i]['name'].'" />';
                        }
                        $return .= '</td>'."\n";
                    }
                    $return .= '<td>'.$items[$i]['title'].'</td>'."\n";
                    $return .= '<td>'.$items[$i]['description'].'</td>'."\n";
                    $return .= '</tr>'."\n";
                }
            }
            $return .= '</table>';
        }
    }
    foreach($permission_list AS $leftover_permission) {
        $debug->addMessage('Permission \''.$leftover_permission.'\' exists in the database but it is not found in the permission_list.xml file', true);
    }
    return $return;
}

/**
 * Update permission list to reflect the XML file
 * @global acl $acl
 * @global db $db
 * @global Debug $debug
 * @return mixed Number of changes, or false on failure
 */
function permission_list_refresh() 
{
    global $acl;
    global $db;
    global $debug;

    $num_changes = 0;
    $regex_list = array();
    $permission_list_file = permission_file_read();
    if ($permission_list_file === false) {
        return false;
    }
    $permission_list_db = $acl->permission_list;
    foreach ($permission_list_file AS $permission_list) {
        // Step through each permission category
        for ($i = 0; $i < count($permission_list['items']); $i++) {
            if ($permission_list['items'][$i]['regex'] == 1) {
                $regex_list[] = $permission_list['items'][$i]['name'];
                continue;
            }
            if (isset($permission_list_db[$permission_list['items'][$i]['name']])) {
                // Key already exists in database
                // Check each field for consistency
                $db_perm = $permission_list_db[$permission_list['items'][$i]['name']];
                if ($db_perm['longname'] != $permission_list['items'][$i]['title'] 
                    || $db_perm['description'] != $permission_list['items'][$i]['description'] 
                    || $db_perm['default'] != $permission_list['items'][$i]['default']
                ) {
                    $update_query = 'UPDATE `'.ACL_KEYS_TABLE.'`
						SET `acl_longname` = \''.addslashes($permission_list['items'][$i]['title']).'\',
						`acl_description` = \''.addslashes($permission_list['items'][$i]['description']).'\',
						`acl_value_default` = '.(int)$permission_list['items'][$i]['default'].'
						WHERE `acl_id` = '.$db_perm['id'];
                    $update_handle = $db->sql_query($update_query);
                    if ($db->error[$update_handle] === 1) {
                        $debug->addMessage('Failed to update permission \''.$db_perm['shortname'].'\'', true);
                    } else {
                        Log::addMessage('Modified permission key \''.$db_perm['longname'].'\'');
                        $num_changes++;
                    }
                }
                unset($permission_list_db[$permission_list['items'][$i]['name']]);
            } else {
                // Key does not exist in database. Create it.
                $create_query = 'INSERT INTO `'.ACL_KEYS_TABLE.'`
					(`acl_name`,`acl_longname`,`acl_description`,`acl_value_default`)
					VALUES
					(\''.addslashes($permission_list['items'][$i]['name']).'\',
					\''.addslashes($permission_list['items'][$i]['title']).'\',
					\''.addslashes($permission_list['items'][$i]['description']).'\',
					'.(int)$permission_list['items'][$i]['default'].')';
                $create_handle = $db->sql_query($create_query);
                if ($db->error[$create_handle] === 1) {
                    $debug->addMessage('Failed to create permission key \''.$permission_list['items'][$i]['name'].'\'', true);
                } else {
                    Log::addMessage('Created permission key \''.$permission_list['items'][$i]['title'].'\'');
                    $num_changes++;
                }
            }
        }
    }
    foreach ($permission_list_db AS $db_list) {
        for ($i = 0; $i < count($regex_list); $i++) {
            if (preg_match($regex_list[$i], $db_list['shortname'])) {
                // Go to next foreach iteration
                // (match found)
                continue 2;
            }
        }
        // No matches found - delete entry
        // Delete all permission records
        $recdel_query = 'DELETE FROM `'.ACL_TABLE.'`
			WHERE `acl_id` = '.$db_list['id'];
        $recdel_handle = $db->sql_query($recdel_query);
        if ($db->error[$recdel_handle] === 1) {
            $debug->addMessage('Failed to delete permission records associated with \''.$db_list['shortname'].'\'', true);
        } else {
            // Delete key
            $del_query = 'DELETE FROM `'.ACL_KEYS_TABLE.'`
				WHERE `acl_id` = '.$db_list['id'];
            $del_handle = $db->sql_query($del_query);
            if ($db->error[$del_handle] === 1) {
                $debug->addMessage('Failed to delete key \''.$db_list['longname'].'\'', true);
            } else {
                Log::addMessage('Deleted permission key \''.$db_list['longname'].'\'');
                $num_changes++;
            }
        }
    }
    return $num_changes;
}
?>
