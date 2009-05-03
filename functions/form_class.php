<?php
/**
 * generate a form
 * 
 * Basic class for creating HTML forms. Most form input types are supported. 
 * When the appropriate fields have been added to the form, pass the form variable
 * to a command, and it will return the HTML for a form.
 *
 * @author stephen
 */
class form {
    function __construct() {
        $this->form = "";
        $this->target = "#";
        $this->method = "get";
    }
    function __destruct() {
        
    }
    function __toString() {
        $this->form = '<form method="'.$this->method.'" action="'.$this->target.'">
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
     * @param string $name Unique name for the field
     * @param string $label Text displayed beside field
     * @param string $value Default value of field
     * @param string $props Extra HTML properties for field
     */
    function add_textbox($name, $label, $value = NULL, $props = NULL) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">'.$label.'</label>
            <input type="text" name="'.$name.'" id="_'.$name.'"
            value="'.$value.'" '.$props.' /></div><br />';
        $this->form .= $form_var;
    }
    /**
     * add_password - Add a password box to a form
     * @param string $name Unique name for the field
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
    /**
     * add_hidden - Add a hidden field to a form
     * @param string $name Unique name for the field
     * @param string $value Default value of field
     */
    function add_hidden($name, $value) {
        $form_var = '<input type="hidden" name="'.$name.'" id="_'.$name.'"
            value="'.$value.'" />';
        $this->form .= $form_var;
    }
    /**
     * add_textarea - Add a text area to a form
     * @param string $name Unique name for the field
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
     * @param string $name Unique name for the field
     * @param string $label Text displayed beside field
     * @param array $values Array of values for each entry
     * @param array $strings Array of labels for each entry
     * @param int $selected Entry selected by default (numerical, starts at 1)
     * @param string $props Extra HTML properties for field
     * @return null
     */
    function add_select($name, $label, $values, $strings, $selected = 0, $props = NULL) {
        if(count((array)$values) != count((array)$strings)) {
            return;
        }
        $options = NULL;
        for ($i = 1; $i <= count((array)$values); $i++) {
            $select_this = NULL;
            if($selected == $i) {
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
    // TODO: Add documentation
    function add_radio($name, $label, $values, $strings, $selected = 0, $props = NULL) {
        if(count((array)$values) != count((array)$strings)) {
            return;
        }
        $options = NULL;
        for ($i = 1; $i <= count((array)$values); $i++) {
            $select_this = NULL;
            if($selected == $i) {
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
    // TODO: Add documentation
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
    // TODO: Add documentation
    function add_multiselect($name, $label, $values, $strings, $selected = NULL, $size = 5, $params = NULL) {
        if(count((array)$values) != count((array)$strings)) {
            return;
        }
        $selected = explode(',',$selected);
        $options = NULL;
        for ($i = 1; $i <= count((array)$values); $i++) {
            $select_this = NULL;
            $num_selected = count($selected);
            for($count = 0; $count < $num_selected; $count++) {
                if($selected[$count] == $values[$i - 1]) {
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
     * add_page_list - Add a page list to a form
     * @global array $CONFIG Database configuration information
     * @global resource $db Databasee connection resource
     * @param string $name Unique name for the field
     * @param string $label Text to display beside field
     * @param int $pagetype ID of type of page to list
     * @param bool $nopageallowed Add the 'No Page' list item
     * @param int $value Page ID of default selected page
     * @param string $props Extra HTML properties for field
     */
    function add_page_list($name, $label, $pagetype = '*', $nopageallowed = 0,
        $value = NULL, $props = NULL) {
        global $CONFIG;
        global $db;
        $page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages
            WHERE type = '.$pagetype.' ORDER BY list ASC';
        $page_query_handle = $db->query($page_query);
        $options = NULL;
		for ($i = 1; $i <= $page_query_handle->num_rows; $i++) {
			$page = $page_query_handle->fetch_assoc();
			$options .= '<option value="'.$page['id'].'" >'.$page['title'].'</option>'."\n";
			}
        if($nopageallowed == 1) {
            $options .= '<option value="0">No Page</option>'."\n";
        }
        $form_var = '<div class="admin_form_element"><label for="_'.$name.'">'.$label.'</label>
            <select name="'.$name.'" id="_'.$name.'" '.$props.'>
            '.$options.'</select></div><br />';
        $this->form .= $form_var;
    }
    /**
     * add_icon_list - Add a list of icons from a specified folder to a form
     * @param string $name Unique name for the field
     * @param string $label Text displayed beside field
     * @param string $folder Directory to look for images (relative to ./files)
     * @param string $selected File name (with path) to default selected file
     * @return null
     */
    function add_icon_list($name,$label,$folder,$selected = NULL) {
        if(eregi('[.]',$folder)) {
            return;
        }
        $relative_path = ROOT.'files/'.$folder;
        $app_path = './files/'.$folder;
        $icon_list = scandir($relative_path);
        $num_icons = count($icon_list);
        $options = '<input type="radio" name="'.$name.'" id="_'.$name.'_noImg"
            value="" checked />No Image<br />';
        for ($i = 1; $i <= $num_icons; $i++) {
            if(ereg('\.png|\.jpg$',$icon_list[$i - 1]) == 1) {
                $options .= '<div class="admin_image_list_item">';
                $file_info = get_file_info($relative_path.'/'.$icon_list[$i - 1]);
                if($relative_path.'/'.$icon_list[$i - 1] == $selected) {
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
     * @param string $name Unique name for the field
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
     * @param <type> $name
     */
    function add_file_upload($name) {
        $this->form .= '<div class="admin_form_element">
            <label for="_'.$name.'">&nbsp;</label><input type="button" value="Upload File"
            onClick="window.open(\'./admin/upload_mini.php\',\'mywindow\',
            \'width=400,height=200\')" id="_'.$name.'" />';
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
    // TODO: Add documentation
    function add_submit($name,$label) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">&nbsp;</label>
            <input type="submit" name="'.$name.'" value="'.$label.'" id="_'.$name.'" />
            </div><br />';
        $this->form .= $form_var;
    }
}
?>
