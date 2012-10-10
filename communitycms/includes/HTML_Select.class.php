<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class HTML_Select {
	private $name;
	private $id;
	private $options = array();

	public function __construct($name, $id = NULL) {
		$this->name = $name;
		$this->id = $id;
	}
	
	public function addOption($value, $label) {
		$this->options[] = array($value, $label, false);
	}

	public function setChecked($value) {
		foreach ($this->options AS $i => $option) {
			if ($option[0] == $value) {
				$this->options[$i][2] = true;
			}
		}
	}
	
	public function __toString() {
		$result = '<select name="'.$this->name.'"';
		if ($this->id != NULL) $result .= ' id="'.$this->id.'"';
		$result .= ">\n";
		
		foreach ($this->options AS $option) {
			$result .= "\t<option";
			$result .= ' value="'.$option[0].'"';
			if ($option[2]) $result .= ' selected';
			$result .= '>'.$option[1]."</option>\n";		}
		
		$result .= "</select>\n";
		return $result;
	}
}
?>
