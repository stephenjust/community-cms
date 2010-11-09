<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

/**
 * Return the permission_list.xml file in array form
 * @return array Permission list
 */
function permission_file_read() {
	$xml_file = ROOT.'includes/acl/permission_list.xml';
	$permissions = array();
	$category_count = 0;
	$xmlreader = new XMLReader;
	// Open XML file
	$xmlreader->open($xml_file);
	// Step through each element in the file
	while ($xmlreader->read()) {
		// Skip comments and other useless nodes
		if ($xmlreader->nodeType == XMLREADER::DOC_TYPE ||
				$xmlreader->nodeType == XMLREADER::COMMENT ||
				$xmlreader->nodeType == XMLREADER::XML_DECLARATION) {
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
			$permissions[$category_count]['items'][$key_count]['name'] = $key_name;
			$permissions[$category_count]['items'][$key_count]['title'] = $key_title;
			$permissions[$category_count]['items'][$key_count]['description'] = $key_description;
			$permissions[$category_count]['items'][$key_count]['regex'] = false;
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
 * @global debug $debug
 * @param array $permission_list
 * @param integer $group
 * @param boolean $form
 * @return string
 */
function permission_list($permission_list,$group = 0,$form = false) {
	global $acl;
	global $debug;

	$permission_file_array = permission_file_read();
	$xml_file = ROOT.'includes/acl/permission_list.xml';
	$sorted_permissions = array();
	$category_count = 0;
	$return = NULL;
	$permission_list_base = $permission_list;
	$permission_list = array_keys($permission_list);

	// Find the difference between the list of permissions in the xml file
	// and the list in the database so that regex's can be handled.
	foreach($permission_file_array AS $permission_file_cats) {
		for ($i = 0; $i < count($permission_file_cats['items']); $i++) {
			$permission_index = array_search($permission_file_cats['items'][$i]['name'],$permission_list);
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
						if (preg_match($cat_items['name'],$permission_list[$i])) {
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
						$perm = $acl->check_permission($items[$i]['name'],$group,false);
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
		$debug->add_trace('Permission \''.$leftover_permission.'\' exists in the database but it is not found in the permission_list.xml file',true);
	}
	return $return;
}
?>
