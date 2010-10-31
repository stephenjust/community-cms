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

function permission_list($permission_list,$group = 0,$form = false) {
	global $acl;
	$xml_file = ROOT.'includes/acl/permission_list.xml';
	$sorted_permissions = array();
	$category_count = 0;
	$return = NULL;
	$permission_list_base = $permission_list;
	$permission_list = array_keys($permission_list);

	$xmlreader = new XMLReader;
	$xmlreader->open($xml_file);
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
			$sorted_permissions[$category_count] = array();
			$sorted_permissions[$category_count]['name'] = $cat_name;
			$sorted_permissions[$category_count]['items'] = array();
		}
		if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {
			$category_count++;
		}
		// Handle discrete items
		if ($xmlreader->name == 'key' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
			$key_name = $xmlreader->getAttribute('name');
			$key_title = $xmlreader->getAttribute('title');
			$key_description = $xmlreader->getAttribute('description');
			$sorted_permissions[$category_count]['items'][$key_count]['name'] = $key_name;
			$sorted_permissions[$category_count]['items'][$key_count]['title'] = $key_title;
			$sorted_permissions[$category_count]['items'][$key_count]['description'] = $key_description;
			$sorted_permissions[$category_count]['items'][$key_count]['regex'] = false;

			// Remove key name from permission list to "mark it as used"
			$key_pos = array_search($key_name,$permission_list);
			if ($key_pos !== false) {
				unset($permission_list[$key_pos]);
			}

			$key_count++;
		}
		// Handle regex items
		if ($xmlreader->name == 'key_range' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
			$key_name = $xmlreader->getAttribute('regex');
			$sorted_permissions[$category_count]['items'][$key_count]['name'] = $key_name;
			$sorted_permissions[$category_count]['items'][$key_count]['regex'] = true;
			$key_count++;
		}
	}

	// Reset array key numbering to 0,1,2...
	$permission_list = array_values($permission_list);

	$xmlreader->close();
	foreach ($sorted_permissions AS $cat_permissions) {
		$return .= '<h3>'.$cat_permissions['name'].'</h3>'."\n";
		if (count($cat_permissions['items']) == 0) {
			$return .= 'This category contains no permission values.<br />'."\n";
		} else {
			$return .= '<table class="admintable">';
			if ($form === true) {
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
					print_r($items);
				} else {
					$items[0] = $cat_items;
				}
				for ($i = 0; $i < count($items); $i++) {
					$return .= '<tr>'."\n";
					if ($form === true) {
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
	return $return;
}
?>
