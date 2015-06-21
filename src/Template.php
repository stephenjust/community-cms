<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class Template
{
    public $template = "";
    public $template_name;
    public $path;
    public $return;

    function __construct() 
    {
        global $db;
        global $debug;
        global $template_cache;

        if (!isset($template_cache)) {
            $template_cache = array();
        }
        if (!isset($template_cache['path']) || !isset($template_cache['template_name'])) {
            $template_query = 'SELECT * FROM ' . TEMPLATE_TABLE . '
				WHERE id = '.SysConfig::get()->getValue('site_template').' LIMIT 1';
            $template_handle = $db->sql_query($template_query);
            if ($db->sql_num_rows($template_handle) != 1) {
                $debug->addMessage('Current template not found in database', true);
                $this->path = 'default';
            } else {
                $template_result = $db->sql_fetch_assoc($template_handle);
                $this->path = $template_result['path'];
                $this->template_name = preg_replace('#(^templates/|/)#i', null, $template_result['path']);
            }
            $template_cache['path'] = $this->path;
            $template_cache['template_name'] = $this->template_name;
        } else {
            $this->path = $template_cache['path'];
            $this->template_name = $template_cache['template_name'];
        }
    }

    public function __set($name,$value) 
    {
        if ($name == 'template' || $name == 'path' || $name == 'return') {
            $this->$name = $value;
        } elseif (isset($this->template) && isset($this->path)) {
            $this->template = str_replace('<!-- $'.mb_convert_case($name, MB_CASE_UPPER, "UTF-8").'$ -->', $value, $this->template);
        } else {
            echo 'Template file not loaded yet when trying to set \''.$name.'\'.';
        }
    }

    /**
     * load_file - Loads a template file from the current frontend template
     */
    public function load_file($file = 'index') 
    {
        $this->path = ROOT.$this->path;
        $file .= '.html';
        if ($this->load_template($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * load_admin_file - Loads a template file from the admin template
     */
    public function load_admin_file($file = 'index') 
    {
        $this->path = ROOT.'admin/'.$this->path;
        $file .= '.html';
        if ($this->load_template($file)) {
            return true;
        } else {
            return false;
        }
    }

    private function load_template($file) 
    {
        if (!file_exists($this->path.$file)) {
            throw new \Exception('Template file does not exist.');
        }
        $handle = fopen($this->path.$file, 'r');
        $template_contents = fread($handle, filesize($this->path.$file));
        if (!$template_contents) {
            $this->template = '<span class="errormessage">Failed to open template file \''.$file.'\'</span><br />';
        } else {
            $this->template = $template_contents;
        }
        fclose($handle);
        return true;
    }

    function replace_range($field,$string) 
    {
        $start_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_START$ -->';
        $end_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_END$ -->';
        $start = strpos($this->template, $start_string);
        $end = strpos($this->template, $end_string);
        if ($start && $end) {
            $replace_length = $end - $start + strlen($end_string);
            $this->template = substr_replace($this->template, $string, $start, $replace_length);
        }
    }

    /**
     * get_range - Returns the content between two markers in a template file
     * @global Debug $debug
     * @param string $field Marker name
     * @return mixed Content string, or false on failure
     */
    function get_range($field) 
    {
        global $debug;

        $start_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_START$ -->';
        $end_string = '<!-- $'.mb_convert_case($field, MB_CASE_UPPER, "UTF-8").'_END$ -->';
        $start = strpos($this->template, $start_string);
        $end = strpos($this->template, $end_string);
        // Start may be 0, so we need to check with ===
        if ($start !== false && $end !== false) {
            $length = $end - $start - strlen($start_string);
            return substr($this->template, $start + strlen($start_string), $length);
        }
        $debug->addMessage('Could not find start or end of range '.$field, true);
        return false;
    }

    function replace_variable($variable,$replacement) 
    {
        if (!is_string($variable)) {
            return false;
        }
        if (!is_string($replacement)) {
            return false;
        }

        $matches = array();
        preg_match_all('/\$'.$variable.'\-[\d\w]+\$/i', $this->template, $matches);

        foreach ($matches as $match) {
            if (count($match) == 0) {
                continue;
            }
            for ($i = 0; $i < count($match); $i++) {
                preg_match('/\-(?P<value>[\d\w]+)\$/i', $match[$i], $submatch);
                if (isset($submatch['value'])) {
                    $a = $submatch['value'];
                } else {
                    return false;
                }
                try {
                    eval('$newvalue = '.$replacement);
                }
                catch (Exception $e)
                {
                    $newvalue = $e->getMessage();
                }
                $this->template = str_replace($match[$i], $newvalue, $this->template);
            }
        }
    }

    function split($split_marker) 
    {
        $content = $this->template;
        $temp = explode('<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->', $content);
        $this->template = $temp[0];
        if (isset($temp[1])) {
            $new_temp = $temp[1];
        } else {
            $new_temp = null;
        }
        unset($temp);
        unset($content);
        $new_template = new Template;
        $new_template->path = $this->path;
        $new_template->template = '<!-- $'.mb_convert_case($split_marker, MB_CASE_UPPER, "UTF-8").'$ -->'.$new_temp;
        unset($new_temp);
        return $new_template;
    }

    /**
     * split_range - Returns a new template containing the contents of a certain range
     * @param string $range Name of start and end markers
     * @return Template New template
     */
    public function split_range($range) 
    {
        global $debug;

        $content = $this->get_range($range);
        if ($content === false) {
            $debug->addMessage('Failed to get segment of template', true);
            return false;
        }
        $return = new Template;
        $return->path = $this->path;
        $return->template = $content;
        $this->replace_range($range, null);
        return $return;
    }

    function __toString() 
    {
        // Replace things that should be replaced at all times
        if (isset($this->path)) {
            $this->image_path = $this->path.'images/';
            // Don't replace the following in admin view
            if (!defined('ADMIN')) {
                $this->replace_variable('article_url_onpage', '\\CommunityCMS\\article_url_onpage($a);');
                $this->replace_variable('article_url_ownpage', '\\CommunityCMS\\article_url_ownpage($a);');
                $this->replace_variable('article_url_nopage', '\\CommunityCMS\\article_url_nopage($a);');
                $this->replace_variable('gallery_embed', '(string) new \\CommunityCMS\\Gallery($a);');
            }
        }
        $return = (string)$this->template;
        return $return;
    }
}
