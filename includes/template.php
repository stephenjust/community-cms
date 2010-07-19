<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class template {
	public $template;
	public $template_name;
	public $path;
	public $return;
	public function __set($name,$value) {
		if ($name == 'template' || $name == 'path') {
			$this->$name = $value;
		} elseif (isset($this->template) && isset($this->path)) {
			$this->template = str_replace('<!-- $'.mb_convert_case($name, MB_CASE_UPPER, "UTF-8").'$ -->',$value,$this->template);
		} else {
			echo 'Template file not loaded yet.';
		}
	}

	/**
	 * load_file - Loads a template file from the current frontend template
	 */
	public function load_file($file = 'index') {
		$path = './';
		$file .= '.html';
		if ($this->load_template($path,$file)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * load_admin_file - Loads a template file from the admin template
	 */
	public function load_admin_file($file = 'index') {
		$path = './admin/';
		$file .= '.html';
		if ($this->load_template($path,$file)) {
			return true;
		} else {
			return false;
		}
	}

	private function load_template($path,$file) {
		global $db;

		$template_query = 'SELECT * FROM ' . TEMPLATE_TABLE . '
			WHERE id = '.get_config('site_template').' LIMIT 1';
		$template_handle = $db->sql_query($template_query);
		try {
			if (!$template_handle || $db->sql_num_rows($template_handle) == 0) {
				throw new Exception('Failed to load template file.');
			} else {
				$template = $db->sql_fetch_assoc($template_handle);
				$path .= $template['path'];
				$template_name = str_replace('templates/',NULL,$template['path']);
				$template_name = str_replace('/',NULL,$template_name);
				$this->template_name = $template_name;
				if (!file_exists($path.$file)) {
					throw new Exception('Template file does not exist.');
				}
				if (filesize($path.$file) === 0) {
					throw new Exception('Template file is empty.');
				}
				$handle = fopen($path.$file, 'r');
				$template_contents = fread($handle,filesize($path.$file));
				if (!$template_contents) {
					throw new Exception('Failed to open template file.');
				} else {
					$this->template = $template_contents;
				}
				fclose($handle);
			}
		}
		catch(Exception $e) {
			if (DEBUG === 1) {
				echo '<span style="font-size: x-small; color: #FF0000;">'.$e.'</span>';
			}
			return false;
		}
		$this->path = $path;
		return true;
	}

	function replace_range($field,$string) {
		$start_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_START$ -->';
		$end_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_END$ -->';
		$start = strpos($this->template,$start_string);
		$end = strpos($this->template,$end_string);
		if ($start && $end) {
			$replace_length = $end - $start + strlen($end_string);
			$this->template = substr_replace($this->template,$string,$start,$replace_length);
		}
	}

	function get_range($field) {
		$start_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_START$ -->';
		$end_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_END$ -->';
		$start = strpos($this->template,$start_string);
		$end = strpos($this->template,$end_string);
		if ($start && $end) {
			$length = $end - $start - strlen($start_string);
			return substr($this->template,$start + strlen($start_string),$length);
		}
	}

	function replace_variable($variable,$replacement) {
		if (!is_string($variable)) {
			return false;
		}
		if (!is_string($replacement)) {
			return false;
		}

		$matches = array();
		preg_match_all('/\$'.$variable.'\-[\d\w]+\$/i',$this->template,$matches);

		foreach ($matches as $match) {
			if (count($match) == 0) {
				continue;
			}
			for ($i = 0; $i < count($match); $i++) {
				preg_match('/\-(?P<value>[\d\w]+)\$/i',$match[$i],$submatch);
				if (isset($submatch['value'])) {
					$a = $submatch['value'];
				} else {
					return false;
				}
				eval('$newvalue = '.$replacement);
				$this->template = str_replace($match[$i],$newvalue,$this->template);
			}
		}
	}

	function split($split_marker) {
		$content = $this->template;
		$temp = explode('<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->',$content);
		$this->template = $temp[0];
		if (isset($temp[1])) {
			$new_temp = $temp[1];
		} else {
			$new_temp = NULL;
		}
		unset($temp);
		unset($content);
		$new_template = new template;
		$new_template->path = $this->path;
		$new_template->template = '<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->'.$new_temp;
		unset($new_temp);
		return $new_template;
	}

	function __toString() {
		if (isset($this->template)) {
			$this->return = (string)$this->template;
		} else {
			$this->return = 'Template file not loaded.';
		}
		return $this->return;
	}
}

?>
