<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Generates a bar with various buttons to link to different functions
 * 
 * @package CommunityCMS.main
 */
class editbar {
	public $visible = true;
	public $control_count = 0;
	public $class = 'edit_bar';
	public $label = '';
	public $string = NULL;

	/**
	 * Initialize the editbar class
	 * @global acl $acl
	 * @global Debug $debug
	 */
	function __construct() {
		global $acl;
		global $debug;

		if (!$acl->check_permission('show_editbar')) {
			$this->visible = false;
			$debug->add_trace('Not displaying edit bar because the user lacks permissions to see it',false);
		}
	}

	/**
	 * Add a button to the editbar
	 * @global acl $acl
	 * @param string $url URL to link to
	 * @param string $image Name of image to use as icon
	 * @param string $label Alt-text for the image
	 * @param array $required_permissions Permissions that must be met to display icon
	 * @return boolean Success
	 */
	function add_control($url,$image,$label,$required_permissions = array()) {
		global $acl;

		foreach ($required_permissions AS $permission) {
			if (!$acl->check_permission($permission)) {
				return false;
			}
		}

		$this->control_count++;
		$this->string .= '<a href="'.$url.'"><img src="<!-- $IMAGE_PATH$ -->'.$image.'" alt="'.$label.'" border="0px"></a>';
		return true;
	}

	function set_label($label) {
		if (strlen($label) == 0) {
			$this->label = '';
			return;
		}
		$this->label = $label.': ';
	}

	/**
	 * Return the string of icons (links) for the edit bar
	 * @return string
	 */
	function __toString() {
		if ($this->visible === false) {
			return '';
		}
		if ($this->control_count === 0) {
			return '';
		}
		return '<div class="'.$this->class.'">'.$this->label.$this->string.'</div>';
	}
}
?>
