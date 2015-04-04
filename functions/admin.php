<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */
namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * admin_nav - Generate the navigation bar for administration pages
 * @return string Menu HTML
 */
function admin_nav() 
{
    $nav_handle = fopen(ROOT.'admin/page_list.json', 'r');
    $nav_json = fread($nav_handle, filesize(ROOT.'admin/page_list.json'));
    $nav = json_decode($nav_json, true);
    
    $result = null;
    foreach ($nav['categories'] as $category) {
        $result .= '<div>';
        $result .= sprintf('<h3>%s</h3>', HTML::schars($category['name']));
        $result .= '<div>';
        foreach ($category['pages'] as $page) {
            if (!array_key_exists('label', $page)) {
                continue;
            }
            if (array_key_exists('acl', $page) && !acl::get()->check_permission($page['acl'])) {
                continue;
            }
            if (array_key_exists('url', $page)) {
                $result .= HTML::link($page['url'], $page['label']);
            } elseif (array_key_exists('module', $page)) {
                $result .= HTML::link('admin.php?module='.$page['module'], $page['label']);
            } else {
                continue;
            }
            $result .= '<br />';
        }
        $result .= '</div></div>';
    }
    return $result;
}

/**
 * create_table - Generate styled tables for the admin interface
 * @global Debug $debug Debug Object
 * @param array $columns Array of column headings
 * @param array $values  2D array of values [row][column]
 * @return string HTML for table or NULL
 */
function create_table($columns, $values) 
{
    global $debug;

    // Validate input
    if (!is_array($columns)) {
        $debug->addMessage('Column list must be an array', true);
        return null;
    }
    if (!is_array($values)) {
        $debug->addMessage('Values must be stored in an array', true);
        return null;
    }
    for ($i = 0; $i < count($values); $i++) {
        if (!is_array($values[$i])) {
            $debug->addMessage('List of values is not a 2D array', true);
        }
        if (count($values[$i]) != count($columns)) {
            $debug->addMessage('Number of values and mumbe of columns are not equal', true);
            print_r($columns);
            print_r($values);
            return null;
        }
    }

    // Generate table
    $return = "<table class=\"admintable\">\n";
    $return .= "\t<tr>\n";
    for ($i = 0; $i < count($columns); $i++) {
        $return .= "\t\t<th>{$columns[$i]}</th>\n";
    }
    $return .= "\t</tr>\n";

    // Handle no content gracefully
    if (count($values) == 0) {
        $return .= "\t<tr>\n";
        $return .= "\t\t<td colspan=\"".count($columns)."\">No data found.</td>";
        $return .= "\t</tr>\n";
    }

    for ($i = 0; $i < count($values); $i++) {
        $return .= "\t<tr>\n";
        for ($j = 0; $j < count($values[$i]); $j++) {
            $return .= "\t\t<td>{$values[$i][$j]}</td>\n";
        }
        $return .= "\t</tr>\n";
    }

    $return .= "</table>\n";
    return $return;
}
