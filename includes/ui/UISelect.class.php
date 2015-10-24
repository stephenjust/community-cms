<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class UISelect extends UI
{
    private $options = array();
    private $categories = array();
    
    /**
     * Add an option to the list
     * @param string $value
     * @param string $label
     * @param string $category
     */
    public function addOption($value, $label, $category = null) 
    {
        $this->options[] = array($value, $label, false, $category);
        if (!in_array($category, $this->categories)) {
            $this->categories[] = $category; 
        }
    }

    /**
     * Set the value to be selected by default
     * @param string $value
     */
    public function setChecked($value) 
    {
        foreach ($this->options AS $i => $option) {
            if ($option[0] == $value) {
                $this->options[$i][2] = true;
            }
        }
    }
    
    public function __toString() 
    {
        $result = '<select ';
        if ($this->name) {
            $result .= 'name="'.HTML::schars($this->name).'" '; 
        }
        if ($this->id) {
            $result .= 'id="'.HTML::schars($this->id).'" '; 
        }
        if ($this->onChange) {
            $result .= 'onChange="'.HTML::schars($this->onChange).'" '; 
        }
        $result .= ">\n";
        
        // Display categorised list entries
        sort($this->categories);
        foreach ($this->categories AS $category) {
            if ($category != null) {
                $result .= "\t<optgroup label=\"".HTML::schars($category)."\">\n"; 
            }
            
            foreach ($this->options as $option) {
                if ($option[3] != $category) { continue; 
                }
                
                $result .= "\t<option";
                $result .= ' value="'.HTML::schars($option[0]).'"';
                if ($option[2]) { $result .= ' selected'; 
                }
                $result .= '>'.HTML::schars($option[1])."</option>\n";
            }
            
            if ($category != null) {
                $result .= "\t</optgroup>\n"; 
            }
        }
        
        $result .= "</select>\n";
        return $result;
    }
}
