<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2008-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
require(ROOT.'functions/form_class.php');
class block {
	public $block_id;
	public $type;

	/**
	 * $attribute - Array of block attributes
	 * @var array
	 * @access public
	 */
	public $attribute = array();

	public function __set($name,$value) {
		$this->$name = $value;
	}

	public function get_block_information() {
		if (!isset($this->block_id)) {
			return false;
		}
		global $db;
		global $debug;

		$block_attribute_query = 'SELECT * FROM ' . BLOCK_TABLE . '
			WHERE id = '.$this->block_id.' LIMIT 1';
		$block_attribute_handle = $db->sql_query($block_attribute_query);
		$block = $db->sql_fetch_assoc($block_attribute_handle);
		$this->type = $block['type'];
		$debug->add_trace('Block type is '.$this->type,false,'get_block_information()');
		$block_attribute_temp = $block['attributes'];
		if (strlen($block_attribute_temp) > 0) {
			$block_attribute_temp = explode(",",$block_attribute_temp);
			$block_attribute_count = count($block_attribute_temp);
		} else {
			$block_attribute_count = 0;
		}
		for ($i = 0; $i < $block_attribute_count; $i++) {
			$attribute_temp = explode('=',$block_attribute_temp[$i]);
			$this->attribute[$attribute_temp[0]] = $attribute_temp[1];
			$debug->add_trace('Block '.$this->block_id.' has attribute '.$attribute_temp[0].' = '.$attribute_temp[1],false,'get_block_information()');
		}
		return;
	}

	function __toString() {

	}
}

// ----------------------------------------------------------------------------

class tabs {
	public $num_tabs;
	private $tab_list;
	private $tab_contents;

	function __construct() {
		$num_tabs = 0;
		$tab_list = NULL;
		$tab_contents = NULL;
	}

	public function add_tab($tab_name,$tab_content) {
		$this->num_tabs++;
		$tab_list_string = '<li>
			<a href="#tabs-'.$this->num_tabs.'">'.$tab_name.'</a>
			</li>'."\n";
		$this->tab_list .= $tab_list_string;
		unset($tab_string);
		$tab_content = '<div id="tabs-'.$this->num_tabs.'">'.$tab_content.'</div>';
		$this->tab_contents .= $tab_content."\n";
		unset($tab_content);
		return $this->num_tabs;
	}

	function __toString() {
		$tab_layout = '<div id="tabs">'."\n".
			'<ul>'.$this->tab_list.'</ul>'.$this->tab_contents.'</div>';
		return $tab_layout;
	}
}
?>