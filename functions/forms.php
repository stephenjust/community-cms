<?php
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * checkbox - Convert between the checkbox response, and boolean, or from boolean to HTML
 * @param mixed $var     current state
 * @param int   $reverse Switch from form response -> boolean to boolean -> HTML code
 * @return mixed
 */
function checkbox($var,$reverse = 0) 
{
    // Interperet form information
    if ($reverse == 0) {
        if ($var == "on") {
            return 1;
        } else {
            return 0;
        }
    }
    // Turn boolean into input parameter
    if ($var == 1) {
        return 'checked';
    } else {
        return null;
    }
}

/**
 * Convert possible time formats to H:i format
 * @param string $time Time in any format
 * @return string Time in H:i format
 */
function parse_time($time) 
{
    $new_time = 0;
    if (preg_match('#^[0-1]?[0-9]:[0-9][0-9] ?[ap]m?$#i', $time)) {
        // Check for time in h:i a format
        $time = str_replace(array(' ','m','M'), null, $time);
        $time = explode(':', $time);
        $hour = $time[0];
        if (preg_match('/a$/i', $time[1])) {
            $minute = str_replace(array('a','A'), null, $time[1]);
            if ($hour == '12') {
                $hour = 0;
            }
        } else {
            $minute = str_replace(array('p','P'), null, $time[1]);
            if ($hour != 12) {
                $hour = $hour + 12;
            }
        }
        $new_time = $hour.':'.$minute;
        if (strlen($new_time) === 4) {
            $new_time = '0'.$new_time;
        }
    } elseif (preg_match('#^[0-2]?[0-9]:[0-5][0-9]$#i', $time)) {
        // Check for time in G:i format
        $new_time = $time;
        if (strlen($new_time) === 4) {
            $new_time = '0'.$new_time;
        }
    } elseif (preg_match('#^[0-1]?[0-9] ?[ap]m?$#i', $time)) {
        // Check for time in g:i a format
        $time = str_replace(array(' ','m','M'), null, $time);
        $minute = '00';
        $hour = $time;
        if (preg_match('#a$#i', $time)) {
            $hour = str_replace(array('a','A'), null, $time);
            if ($hour == '12') {
                $hour = 0;
            }
        } else {
            $hour = str_replace(array('p','P'), null, $time);
            if ($hour != 12) {
                $hour = $hour + 12;
            }
        }
        $new_time = $hour.':'.$minute;
        if (strlen($new_time) === 4) {
            $new_time = '0'.$new_time;
        }
    }
    return $new_time;
}

/**
 * Remove HTML comment tags from a string, or an array of strings
 * @param mixed $text Input string(s)
 * @return mixed Output, without comments
 */
function remove_comments($text) 
{
    if ($text == null) {
        return null;
    }

    // Convert strings into an array for consistent processing
    if (!is_array($text)) {
        $text = array((string)$text);
    }

    $new_text = array();
    foreach ($text as $cur_text) {
        $cur_text = preg_replace('/<!--.+-->/', null, $cur_text);
        $new_text[] = $cur_text;
    }
    // If the array has only one element, convert it back to a string
    if (count($new_text) == 1) {
        $new_text = $new_text[0];
    }
    return $new_text;
}

function replace_char_codes($input) 
{
    $output = $input;
    $output = str_replace('–', '&ndash;', $output);
    $output = str_replace('’', '&rsquo;', $output);
    return $output;
}

function dynamic_article_link_list($page = 0) 
{
    global $db;

    $return = '<table style="border: 0px;">'."\n";

    $page_query = 'SELECT `id`,`title` FROM `'.PAGE_TABLE.'`
		WHERE `type` = 1 ORDER BY `title` ASC';
    $page_handle = $db->sql_query($page_query);
    if ($db->sql_num_rows($page_handle) == 0) {
        $return .= '<tr><td>There are no news pages available.</td></tr></table>';
        return $return;
    }
    $pagelist = '<select name="page" id="page_select" onChange="update_dynamic_article_link_list();">';
    for ($i = 1; $i <= $db->sql_num_rows($page_handle); $i++) {
        $page_result = $db->sql_fetch_assoc($page_handle);
        $pagelist .= '<option value="'.$page_result['id'].'"';
        if ($page == $page_result['id']) {
            $pagelist .= ' selected';
        }
        $pagelist .= '>'.stripslashes($page_result['title']).'</option>';
    }
    $no_page_selected = null;
    if ($page == 0) {
        $no_page_selected = ' selected';
    }
    $pagelist .= '<option value="0"'.$no_page_selected.'>No Page</option>';
    $pagelist .= '</select>';
    $return .= '<tr><td>Article Page</td><td>'.$pagelist.'</td></tr>';

    $article_query = 'SELECT `id`,`name` FROM `'.NEWS_TABLE.'`
		WHERE `page` = '.$page.' ORDER BY `name` ASC';
    $article_handle = $db->sql_query($article_query);
    if ($db->sql_num_rows($article_handle) == 0) {
        $return .= '<tr><td colspan="2">There are no articles on this page.</td></tr></table>';
        return $return;
    }
    $articlelist = '<select name="article" id="article_select">';
    for ($i = 1; $i <= $db->sql_num_rows($article_handle); $i++) {
        $article_result = $db->sql_fetch_assoc($article_handle);
        $articlelist .= '<option value="'.$article_result['id'].'">'.
        stripslashes($article_result['name']).'</option>';
    }
    $articlelist .= '</select>';
    $return .= '<tr><td>Article Title</td><td>'.$articlelist.'</td></tr>';
    $return .= '</table>';
    return $return;
}
?>
