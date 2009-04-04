<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	class template {
		public $template;
		public $path;
		public $return;
		public function __set($name,$value) {
			if($name == 'template' || $name == 'path') {
				$this->$name = $value;
				} elseif(isset($this->template) && isset($this->path)) {
				$this->template = str_replace('<!-- $'.mb_convert_case($name, MB_CASE_UPPER, "UTF-8").'$ -->',$value,$this->template);
				} else {
				echo 'Template file not loaded yet.';
				}
			}
			
		public function load_file($file = 'index') {
			$path = './';
			$file .= '.html';
			if($this->load_template($path,$file)) {
				return true;
				} else {
				return false;
				}
			}

		public function load_admin_file($file = 'index') {
			$path = './admin/';
			$file .= '.html';
			if($this->load_template($path,$file)) {
				return true;
				} else {
				return false;
				}
			}

		private function load_template($path,$file) {
			global $db; // Used for query
			global $CONFIG; // Used for query
			global $site_info; //Used for query
			$template_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'templates WHERE id = '.$site_info['template'].' LIMIT 1';
			$template_handle = $db->query($template_query);
			try {
				if(!$template_handle || $template_handle->num_rows == 0) {
					throw new Exception('Failed to load template file.');
					} else {
					$template = $template_handle->fetch_assoc();
					$path .= $template['path'];
					$handle = fopen($path.$file, 'r');
					$template_contents = fread($handle,filesize($path.$file));
					if(!$template_contents) {
						throw new Exception('Failed to open template file.');
						} else {
						$this->template = $template_contents;
						}
					fclose($handle);
					}
				}
			catch(Exception $e) {
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
			if($start && $end) {
				$replace_length = $end - $start + strlen($end_string);
				$this->template = substr_replace($this->template,$string,$start,$replace_length);
				}
			}

		function get_range($field) {
			$start_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_START$ -->';
			$end_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_END$ -->';
			$start = strpos($this->template,$start_string);
			$end = strpos($this->template,$end_string);
			if($start && $end) {
				$length = $end - $start - strlen($start_string);
				return substr($this->template,$start + strlen($start_string),$length);
				}
			}

		function split($split_marker) {
			$content = $this->template;
			$temp = explode('<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->',$content);
			$this->template = $temp[0];
			$new_temp = $temp[1];
			unset($temp);
			unset($content);
			$new_template = new template;
			$new_template->path = $this->path;
			$new_template->template = '<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->'.$new_temp;
			unset($new_temp);
			return $new_template;
			}

		function __toString() {
			if(isset($this->template)) {
				$this->return = $this->template;
				} else {
				$this->return = 'Template file not loaded.';
				}
			return $this->return;
			}
		}

// ----------------------------------------------------------------------------

	class block {
		public $block_id;
		public $type;
		public $attributes;

		public function __set($name,$value) {
			$this->$name = $value;
			}

		public function get_block_information() {
			if(!isset($this->block_id)) {
				return false;
				}
			global $CONFIG;
			global $db;
			$block_attribute_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$this->block_id.' LIMIT 1';
			$block_attribute_handle = $db->query($block_attribute_query);
			$block = $block_attribute_handle->fetch_assoc();
			$block_attribute_temp = $block['attributes'];
			if(strlen($block_attribute_temp) > 0) {
				$block_attribute_temp = explode(",",$block_attribute_temp);
				$block_attribute_count = count($block_attribute_temp);
				} else {
				$block_attribute_count = 0;
				}
			for ($i = 0; $i < $block_attribute_count; $i++) {
				$attribute_temp = explode('=',$block_attribute_temp[$i]);
				$this->attribute[$attribute_temp[0]] = $attribute_temp[1];
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