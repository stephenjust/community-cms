<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class UI
{
    protected $class;
    protected $id;
    protected $name;
    protected $onChange;
    
    /**
     * Populate common parameters
     * @param array $params
     */
    public function __construct($params) 
    {
        if (array_key_exists('id', $params)) {
            $this->id = $params['id']; 
        }
        if (array_key_exists('class', $params)) {
            $this->class = $params['class']; 
        }
        if (array_key_exists('name', $params)) {
            $this->name = $params['name']; 
        }
        if (array_key_exists('onChange', $params)) {
            $this->onChange = $params['onChange']; 
        }
        
        $this->parse_params($params);
        $this->preload();
    }
    
    /**
     * Override this function to parse more parameters than the basic ones
     * @param array $params
     */
    protected function parse_params($params) 
    { 
    }
    
    /**
     * Override this function to provide some extra initialisation behavior
     */
    protected function preload() 
    {
    }
}

?>
