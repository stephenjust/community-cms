<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * content - Container class for all content items
 * 
 * @todo Use this class
 * @package CommunityCMS.main
 */
class content {
	/**
	 * @var int Unique content ID
	 */
	var $content_id = 0;
	/**
	 * @var array Information about content from database
	 */
	var $content_info = array();
	function __construct($content_id) {
		if (!is_numeric($content_id)) {
			return false;
		}
		$this->content_id = $content_id;
		if (!$this->get_content_info()) {
			return false;
		}
	}

	private function get_content_info() {
		global $db;
		global $debug;
		$content_info_query = 'SELECT * FROM `' . CONTENT_TABLE . '`
			WHERE `content_id` = '.$this->content_id.' LIMIT 1';
		$content_info_handle = $db->sql_query($content_info_query);
		if ($db->error[$content_info_handle] === 1) {
			$debug->add_trace('Failed to read from database',true,'content->get_content_info()');
			return false;
		}
		if ($db->sql_num_rows($content_info_handle) === 0) {
			$debug->add_trace('Content not found',true,'content->get_content_info');
			return false;
		}
		$content_info = $db->sql_fetch_assoc($content_info_handle);
		$this->content_info = $content_info;
		return true;
	}

}
?>
