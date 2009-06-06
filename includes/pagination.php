<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

function pagination($start, $num, $all_elements) {
	// Validate variables
	if (!is_array($all_elements)) {
		return false;
	}
	if (!is_int($start)) {
		return false;
	}
	if (!is_int($num)) {
		return false;
	}
	// If the total number of items to paginate will not fill more than one
	// page, don't bother calculating whether anything must be done.
	if (count($all_elements) <= $num) {
		return false;
	}
	// If the element number we're starting on is greater than the total number
	// of elements, something is wrong.
	if ($start > count($all_elements)) {
		return false;
	}
	// FIXME: Incomplete function
}
?>
