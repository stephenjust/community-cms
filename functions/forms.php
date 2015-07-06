<?php
namespace CommunityCMS;
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
