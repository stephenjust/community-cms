<?php
/**
 * Basic class for creating HTML forms. Most form input types are supported.
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
    function set_target($target) {
        $this->target = $target;
    }
    function set_method($method) {
        $this->method = $method;
    }
    function add_textbox($name, $label, $value = NULL, $props = NULL) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">'.$label.'</label>
            <input type="text" name="'.$name.'" id="_'.$name.'"
            value="'.$value.'" '.$props.' /></div><br />';
        $this->form .= $form_var;
    }
    function add_password($name, $label, $value = NULL, $props = NULL) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">'.$label.'</label>
            <input type="password" name="'.$name.'" id="_'.$name.'"
            value="'.$value.'" '.$props.' /></div><br />';
        $this->form .= $form_var;
    }
    function add_hidden($name, $value) {
        $form_var = '<input type="hidden" name="'.$name.'" id="_'.$name.'"
            value="'.$value.'" />';
        $this->form .= $form_var;
    }
    function add_textarea($name, $label, $value = NULL, $props = NULL) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">'.$label.'</label>
            <textarea name="'.$name.'" id="_'.$name.'" '.$props.'>'.$value.'</textarea>
            </div><br />';
        $this->form .= $form_var;
    }
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
    function add_text($text) {
        $form_var = '<div class="admin_form_element">
            <div class="admin_form_text">'.$text.'</div>
            </div><br />';
        $this->form .= $form_var;
    }
    function add_submit($name,$label) {
        $form_var = '<div class="admin_form_element">
            <label for="_'.$name.'">&nbsp;</label>
            <input type="submit" name="'.$name.'" value="'.$label.'" id="_'.$name.'" />
            </div><br />';
        $this->form .= $form_var;
    }
}
?>
