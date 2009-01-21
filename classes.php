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
					$template_file = $template['path'].$file.'.html';
					$handle = fopen($template_file, 'r');
					$template_contents = fread($handle,filesize($template_file));
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
			$this->path = $template['path'];
			return true;
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
?>