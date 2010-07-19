<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * pagination - Create links to previous an next page, when needed
 * @param int $start First content number (starts at 1)
 * @param int $num Number of content items per page
 * @param array $all_elements Array of all elements in series
 * @global object $debug Debug object
 * @global object $page Page object
 * @return mixed False if paginating failed for some reason, a string if success
 */
function pagination($start, $num, $all_elements) {
	global $debug;
	global $page;
	// Validate variables
	if (!is_array($all_elements)) {
		$debug->add_trace('List of elements is not an array',true,'pagination');
		return false;
	}
	if (!is_int($start) || (int)$start < 0) {
		$debug->add_trace('Start number is not an integer or is an invalid integer',true,'pagination');
		return false;
	}
	if (!is_numeric($num)) {
		$debug->add_trace('Number of entries to display on a page is not an integer',true,'pagination');
		return false;
	}
	$num = (int)$num;
	// If the total number of items to paginate will not fill more than one
	// page, don't bother calculating whether anything must be done.
	if (count($all_elements) <= $num) {
		$debug->add_trace('There is not enough elements to paginate',false,'pagination');
		return false;
	}
	$debug->add_trace('The total number of visible elements for this page is '.count($all_elements),false,'pagination');
	// If the element number we're starting on is greater than the total number
	// of elements, something is wrong.
	if ($start > count($all_elements)) {
		$debug->add_trace('Attempt to start at an increment that is too high',true,'pagination');
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
		$template->prev_page = '<a href="index.php?'.$page->url_reference.'&amp;start='.$prev_offset.'" class="prev_page" id="prev_page">Previous Page</a>';
	} else {
		$template->prev_page = '';
	}
	// If there's another page...
	if ($start + $num - 1 < count($all_elements)) {
		$next_offset = $start + $num;
		$template->next_page = '<a href="index.php?'.$page->url_reference.'&amp;start='.$next_offset.'" class="next_page" id="next_page">Next Page</a>';
	} else {
		$template->next_page = '';
	}
	$return .= $template;
	return $return;
}
?>
