<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2010-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use XMLReader;

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
        if ($xmlreader->nodeType == XMLReader::DOC_TYPE
            || $xmlreader->nodeType == XMLReader::COMMENT
            || $xmlreader->nodeType == XMLReader::XML_DECLARATION
        ) {
            continue;
        }
        // Handle categories
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLReader::ELEMENT) {
            $key_count = 0;
            $cat_name = $xmlreader->getAttribute('name');
            $permissions[$category_count] = array();
            $permissions[$category_count]['name'] = $cat_name;
            $permissions[$category_count]['items'] = array();
        }
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLReader::END_ELEMENT) {
            $category_count++;
        }
        // Handle discrete items
        if ($xmlreader->name == 'key' && $xmlreader->nodeType == XMLReader::ELEMENT) {
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
        if ($xmlreader->name == 'key_range' && $xmlreader->nodeType == XMLReader::ELEMENT) {
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
 * @param array   $permission_list
 * @param integer $group
 * @param boolean $form
 * @return string
 */
function permission_list($permission_list,$group = 0,$form = false) 
{
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
                        $perm = acl::get()->check_permission($items[$i]['name'], $group, false);
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
        Debug::get()->addMessage('Permission \''.$leftover_permission.'\' exists in the database but it is not found in the permission_list.xml file', true);
    }
    return $return;
}

/**
 * Update permission list to reflect the XML file
 * @return mixed Number of changes, or false on failure
 */
function permission_list_refresh() 
{
    $num_changes = 0;
    $regex_list = array();
    $permission_list_file = permission_file_read();
    if ($permission_list_file === false) {
        return false;
    }
    $permission_list_db = acl::get()->permission_list;
    foreach ($permission_list_file AS $permission_list) {
        // Step through each permission category
        for ($i = 0; $i < count($permission_list['items']); $i++) {
            if ($permission_list['items'][$i]['regex'] == 1) {
                $regex_list[] = $permission_list['items'][$i]['name'];
                continue;
            }
            $key = $permission_list['items'][$i];
            acl::get()->createKey($key['name'], $key['title'], $key['description'], $key['default']);
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
        if (acl::get()->deleteKey($db_list['id'])) {
            Log::addMessage('Deleted permission key \''.$db_list['longname'].'\'');
            $num_changes++;
        } else {
            Debug::get()->addMessage('Failed to delete key \''.$db_list['longname'].'\'', true);
        }
    }
    return $num_changes;
}
