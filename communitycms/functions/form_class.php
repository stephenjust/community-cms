<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2008-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Generate a form
 * 
 * Basic class for creating HTML forms. Most form input types are supported. 
 * When the appropriate fields have been added to the form, pass the form variable
 * to a command, and it will return the HTML for a form.
 *
 * @author stephen
 * @package CommunityCMS.main
 */
class form {
	function __construct() {
		$this->form = "";
		$this->target = "#";
		$this->method = "get";
		$this->enctype = NULL;
	}
	function __destruct() {

	}
	function __toString() {
		$this->form = '<form method="'.$this->method.'" action="'.$this->target.'" '.$this->enctype.'>
			<table class="admintable">
			<tr><td>'.$this->form.'</tr></td></table></form>';
		return $this->form;
	}
	function __get($name) {
		return $this->$name;
	}
	function __set($name,$value) {
		$this->$name = $value;
	}

	/**
	 * set_target - Set the page that the form links to.
	 * @param string $target File that the form links to
	 */
	function set_target($target) {
		$this->target = $target;
	}

	/**
	 * set_method - Set whether the form POSTs or GETs
	 * @param sting $method 'post' or 'get'
	 */
	function set_method($method) {
		$this->method = $method;
	}

	/**
	 * add_textbox - Add a text box to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param string $value Default value of field
	 * @param string $props Extra HTML properties for field
	 * @param string $check Add checkbox with label
	 * @param boolean $checkval Checkbox state
	 */
	function add_textbox($name, $label, $value = NULL, $props = NULL, $check = NULL, $checkval = NULL) {
		if ($check != NULL) {
			$checkval = ($checkval) ? 'checked' : NULL;
			$check = ' '.$check.'<input type="checkbox" id="_'.$name.'_check" name="'.$name.'_check" '.$checkval.' />';
		}
		
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<input type="text" name="'.$name.'" id="_'.$name.'"
			value="'.$value.'" '.$props.' />'.$check.'</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_password - Add a password box to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param string $value Default value of field
	 * @param string $props Extra HTML properties for field
	 */
	function add_password($name, $label, $value = NULL, $props = NULL) {
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<input type="password" name="'.$name.'" id="_'.$name.'"
			value="'.$value.'" '.$props.' /></div><br />';
		$this->form .= $form_var;
	}

	function add_heading($text) {
		$this->form .= '<h4>'.HTML::schars($text).'</h4>';
	}
	
	/**
	 * add_hidden - Add a hidden field to a form
	 * @param string $name Name of form var
	 * @param string $value Default value of field
	 */
	function add_hidden($name, $value) {
		$form_var = '<input type="hidden" name="'.$name.'" id="_'.$name.'"
			value="'.$value.'" />';
		$this->form .= $form_var;
	}

	/**
	 * add_textarea - Add a text area to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param string $value Default value of field
	 * @param string $props Extra HTML properties for field
	 */
	function add_textarea($name, $label, $value = NULL, $props = NULL) {
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<textarea name="'.$name.'" id="_'.$name.'" '.$props.'>'.$value.'</textarea>
			</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_select - Add a list box to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param array $values Array of values for each entry
	 * @param array $strings Array of labels for each entry
	 * @param int $selected Entry selected by default (numerical, starts at 1)
	 * @param string $props Extra HTML properties for field
	 * @return void
	 */
	function add_select($name, $label, $values, $strings, $selected = 0, $props = NULL) {
		if (count((array)$values) != count((array)$strings)) {
			return;
		}
		$options = NULL;
		for ($i = 1; $i <= count((array)$values); $i++) {
			$select_this = NULL;
			if ($selected == $values[$i - 1]) {
				$select_this = 'selected';
			}
			$options .= '<option value="'.$values[$i - 1].'" '.$select_this.'>'.$strings[$i - 1].'</option>'."\n";
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<select name="'.$name.'" id="_'.$name.'" '.$props.'>'.$options.'</select>
			</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_radio - Adds a group of radio buttons to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed next to buttons
	 * @param array $values Array of values of buttons
	 * @param array $strings Array of text for each button
	 * @param int $selected Item number to select by default, 0 for no selection
	 * @param string $props Extra HTML properties for field
	 * @return void
	 */
	function add_radio($name, $label, $values, $strings, $selected = 0, $props = NULL) {
		if (count((array)$values) != count((array)$strings)) {
			return;
		}
		$options = NULL;
		for ($i = 1; $i <= count((array)$values); $i++) {
			$select_this = NULL;
			if ($selected == $i) {
				$select_this = 'checked';
			}
			$options .= '<input type="radio" name="'.$name.'"
				value="'.$values[$i - 1].'" '.$select_this.' />'.$strings[$i - 1].'<br />'."\n";
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<fieldset id="_'.$name.'">'.$options.'</fieldset></div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_checkbox - Adds a checkbox to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside checkbox
	 * @param int $value 1 for checked
	 * @param string $params Extra HTML parameters for form object
	 */
	function add_checkbox($name, $label, $value = NULL, $params = NULL) {
		if ($value == 1) {
			$value = 'checked';
		} else {
			$value = NULL;
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<input type="checkbox" name="'.$name.'" id="_'.$name.'" '.$params.' '.$value.' /></div>';
		$this->form .= $form_var;
	}

	/**
	 * add_multiselect - Add a select box to a form (allow multiple selections)
	 * @param string $name Name of form var
	 * @param string $label Text to display beside form element
	 * @param array $values Values of each entry
	 * @param array $strings Text to be displayed for each entry
	 * @param mixed $selected Value of selected entry
	 * @param int $size Number of entries to display at one time
	 * @param string $params Extra HTML parameters for form element
	 * @return void
	 */
	function add_multiselect($name, $label, $values, $strings, $selected = NULL, $size = 5, $params = NULL) {
		if (count((array)$values) != count((array)$strings)) {
			return;
		}
		$selected = explode(',',$selected);
		$options = NULL;
		for ($i = 1; $i <= count((array)$values); $i++) {
			$select_this = NULL;
			$num_selected = count($selected);
			for ($count = 0; $count < $num_selected; $count++) {
				if ($selected[$count] == $values[$i - 1]) {
					$select_this = 'selected';
				}
			}
			$options .= '<option value="'.$values[$i - 1].'" '.$select_this.'>'.$strings[$i - 1].'</option>'."\n";
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<select name="'.$name.'[]" id="_'.$name.'" '.$params.'
			size="'.(int)$size.'" multiple>'.$options.'</select>
			</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_date - Add a date selector to a form
	 * @param string $name Name of form var
	 * @param string $label Text to display beside field
	 * @param string $format String of characters D, M, and Y, may be any order
	 * @param array $value Array of values (in order of $format), current date if empty
	 * @param string $props Unused
	 * @return null
	 */
	function add_date($name, $label, $format = 'MDY', $value = array(), $props = NULL) {
		$form_var = NULL;
		if (!preg_match('/^[DMY]+$/i',$format)) {
			return;
		}
		$num_fields = strlen($format);
		if ($value == array()) {
			for ($i = 0; $i < $num_fields; $i++) {
				$value[$i] = NULL;
			}
		}
		if (count($value) != $num_fields) {
			return;
		}
		for ($i = 1; $i <= $num_fields; $i++) {
			switch (substr($format,$i - 1, 1)) {
				default:
					return;
					break;
				case 'D':
					$suffix = '_day';
					if ($value[$i - 1] == NULL) {
						$value[$i - 1] = date('d');
					}
					$form_var .= '<input type="text" name="'.$name.$suffix.'"
						id="_'.$name.$suffix.'" size="2" maxlength="2"
						value="'.$value[$i - 1].'" '.$props.' />';
					break;
				case 'M':
					$suffix = '_month';
					if ($value[$i - 1] == NULL) {
						$value[$i - 1] = date('m');
					}
					$months = array('January','February','March','April','May',
						'June','July','August','September','October','November',
						'December');
					$form_var .= '<select name="'.$name.$suffix.'" id="_'.$name.$suffix.'" '.$props.'>';
					for ($monthcount = 1; $monthcount <= 12; $monthcount++) {
						if (date('m') == $monthcount) {
							$form_var .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>";
						} else {
							$form_var .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
						}
					}
					$form_var .= '</select>';
					break;
				case 'Y':
					$suffix = '_year';
					if ($value[$i - 1] == NULL) {
						$value[$i - 1] = date('Y');
					}
					$form_var .= '<input type="text" name="'.$name.$suffix.'"
						id="_'.$name.$suffix.'" size="4" maxlength="4"
						value="'.$value[$i - 1].'" '.$props.' />';
					break;
			}
			if ($i == 1) {
				$first_field_name = $name.$suffix;
			}
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$first_field_name.'">'.$label.'</label>'.$form_var.'</div><br />';
		$this->form .= $form_var;
	}

	function add_date_cal($name,$label,$value = NULL,$props = NULL) {
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<input type="text" name="'.$name.'" id="_'.$name.'"
			value="'.$value.'" class="datepicker" '.$props.' /></div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_page_list - Add a page list to a form
	 * @global resource $db Databasee connection resource
	 * @param string $name Name of form var
	 * @param string $label Text to display beside field
	 * @param int $pagetype ID of type of page to list
	 * @param bool $nopageallowed Add the 'No Page' list item
	 * @param int $value Page ID of default selected page
	 * @param string $props Extra HTML properties for field
	 */
	function add_page_list($name, $label, $pagetype = '*', $nopageallowed = 0,
		$value = NULL, $props = NULL) {
		global $db;
		if ($pagetype == '*')
			$page_query = 'SELECT * FROM ' . PAGE_TABLE . '
				ORDER BY `title` ASC';
		else
			$page_query = 'SELECT * FROM ' . PAGE_TABLE . '
				WHERE type = '.$pagetype.' ORDER BY `title` ASC';
		$page_query_handle = $db->sql_query($page_query);
		$options = new HTML_Select($name, '_'.$name);
		for ($i = 1; $i <= $db->sql_num_rows($page_query_handle); $i++) {
			$page = $db->sql_fetch_assoc($page_query_handle);
			$options->addOption($page['id'], $page['title']);
		}
		if ($nopageallowed == 1) {
			$options->addOption(0, 'No Page');
		}
		if ($value !== NULL)
			$options->setChecked($value);
		$form_var = '<div class="admin_form_element"><label for="_'.$name.'">'.$label.'</label>
			'.(string)$options.'</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_icon_list - Add a list of icons from a specified folder to a form
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param string $folder Directory to look for images (relative to ./files)
	 * @param string $selected File name (with path) to default selected file
	 * @return null
	 */
	function add_icon_list($name,$label,$folder,$selected = NULL) {
		if (preg_match('/[.]/',$folder)) {
			return;
		}
		$relative_path = ROOT.'files/'.$folder;
		$app_path = './files/'.$folder;
		$icon_list = scandir($relative_path);
		$num_icons = count($icon_list);
		$options = '<input type="radio" name="'.$name.'" id="_'.$name.'_noImg"
			value="" checked />No Image<br />';
		for ($i = 1; $i <= $num_icons; $i++) {
			if (preg_match('#\.png|\.jpg$#i',$icon_list[$i - 1]) == 1) {
				$options .= '<div class="admin_image_list_item">';
				$file_info = get_file_info($relative_path.'/'.$icon_list[$i - 1]);
				if ($relative_path.'/'.$icon_list[$i - 1] == $selected) {
					$options .= '<input type="radio" name="image"
						value="'.$app_path.'/'.$icon_list[$i - 1].'" checked />
						<br /><img src="'.$relative_path.'/'.$icon_list[$i - 1].'"
						alt="'.$file_info['label'].'" />';
				} else {
					$options .= '<input type="radio" name="image"
						value="'.$app_path.'/'.$icon_list[$i - 1].'" />
						<br /><img src="'.$relative_path.'/'.$icon_list[$i - 1].'"
						alt="'.$file_info['label'].'" />';
				}
				$options .= '</div>';
			}
		}
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<div id="_'.$name.'" class="admin_image_list">'.$options.'</div>
			</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_file_list - Add a file list defaulting to the specified directory
	 * @param string $name Name of form var
	 * @param string $label Text displayed beside field
	 * @param string $directory Name of the directory to start in, NULL for ./files/
	 */
	function add_file_list($name,$label,$directory) {
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">'.$label.'</label>
			<noscript>You need JavaScript enabled to browse for files.</noscript>
			<div id="_'.$name.'" class="admin_file_list">
			<div id="dynamic_file_list">'.dynamic_file_list($directory).'</div>
			<input type="button" value="Refresh List" onClick="update_dynamic_file_list()" />
			</div></div>';
		$this->form .= $form_var;
	}

	/**
	 * add_file_upload - Add a button to upload a file
	 * @global object $acl Permission object
	 * @param string $name ID for form var
	 */
	function add_file_upload($name,$upload_dir = NULL, $gallery = false) {
		global $acl;
		if (!$acl->check_permission('file_upload')) {
			return;
		}
		if ($upload_dir == NULL) {
			$script_file = ROOT.'admin/upload_mini.php';
		} else {
			$script_file = ROOT.'admin/upload_mini.php?dir='.$upload_dir;
			if ($gallery == true) {
				$script_file .= '&amp;thumb=1';
			}
		}
		$this->form .= '<div class="admin_form_element">
			<label for="_'.$name.'">&nbsp;</label><input type="button" value="Upload File"
			onClick="window.open(\''.$script_file.'\',\'mywindow\',
			\'width=400,height=200\')" id="_'.$name.'" />';
	}

	/**
	 * add_file - Add a file upload box
	 * @param string $name Name of form var
	 * @param string $label Text to show beside form element
	 */
	function add_file($name,$label) {
		// Need to set the proper form encoding format
		$this->enctype = 'enctype="multipart/form-data"';
		$this->form .= '<label for="_'.$name.'">'.$label.'</label>
			<input id="_'.$name.'" name="'.$name.'" type="file" />';
	}

	/**
	 * add_text - Add a block of text to a form
	 * @param string $text Text to be displayed
	 */
	function add_text($text) {
		$form_var = '<div class="admin_form_element">
			<div class="admin_form_text">'.$text.'</div>
			</div><br />';
		$this->form .= $form_var;
	}

	/**
	 * add_submit - Add a submit button to a form
	 * @param string $name Name of form var
	 * @param string $label Label to display on the button
	 */
	function add_submit($name,$label) {
		$form_var = '<div class="admin_form_element">
			<label for="_'.$name.'">&nbsp;</label>
			<input type="submit" name="'.$name.'" value="'.$label.'" id="_'.$name.'" />
			</div><br />';
		$this->form .= $form_var;
	}
}
?>
