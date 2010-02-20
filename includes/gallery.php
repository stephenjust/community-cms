<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

function gallery_embed($id) {
	global $db;
	global $debug;

	if (!is_numeric($id)) {
		$debug->add_trace('Gallery id is not numeric',true,'gallery_embed()');
		return false;
	}

	$gallery_info_query = 'SELECT * FROM `'.GALLERY_TABLE.'`
		WHERE `id` = '.$id.' LIMIT 1';
	$db_handle = $db->sql_query($gallery_info_query);
	if ($db->error[$db_handle] === 1) {
		$debug->add_trace('Failed to read from gallery table',true,'gallery_embed()');
		return false;
	}
	if ($db->sql_num_rows($db_handle) != 1) {
		$debug->add_trace('Gallery '.$id.' does not exist',true,'gallery_embed()');
		return false;
	}

	// TODO: Finish gallery display script
}

?>