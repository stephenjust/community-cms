<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

/**
 * pagination - Create links to previous an next page, when needed
 * @param int   $start        First content number (starts at 1)
 * @param int   $num          Number of content items per page
 * @param array $all_elements Array of all elements in series
 * @global Debug $debug Debug object
 * @return mixed False if paginating failed for some reason, a string if success
 */
function pagination($start, $num, $all_elements) 
{
    global $debug;
    // Validate variables
    if (!is_array($all_elements)) {
        $debug->addMessage('List of elements is not an array', true);
        return false;
    }
    if (!is_numeric($start) || (int)$start < 0) {
        $debug->addMessage('Start number is not an integer or is an invalid integer', true);
        return false;
    }
    if (!is_numeric($num)) {
        $debug->addMessage('Number of entries to display on a page is not an integer', true);
        return false;
    }
    $num = (int)$num;
    // If the total number of items to paginate will not fill more than one
    // page, don't bother calculating whether anything must be done.
    if (count($all_elements) <= $num) {
        $debug->addMessage('There is not enough elements to paginate', false);
        return false;
    }
    $debug->addMessage('The total number of visible elements for this page is '.count($all_elements), false);
    // If the element number we're starting on is greater than the total number
    // of elements, something is wrong.
    if ($start > count($all_elements)) {
        $debug->addMessage('Attempt to start at an increment that is too high', true);
        return false;
    }
    $return = '';


    $template = new template;
    $template->load_file('pagination');
    // If there's any previous pages...
    if ($start > 1) {
        if($start <= $num) {
            $prev_offset = 1;
        } else {
            $prev_offset = $start - $num;
        }
        $template->prev_page = HTML::link('index.php?'.Page::$url_reference.'&start='.$prev_offset, 'Previous Page', 'prev_page', 'prev_page');
    } else {
        $template->prev_page = '';
    }
    // If there's another page...
    if ($start + $num - 1 < count($all_elements)) {
        $next_offset = $start + $num;
        $template->next_page = HTML::link('index.php?'.Page::$url_reference.'&start='.$next_offset, 'Next Page', 'next_page', 'next_page');
    } else {
        $template->next_page = '';
    }
    $return .= $template;
    return $return;
}
?>
