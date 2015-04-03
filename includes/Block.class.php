<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2008-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

class Block
{
    public $block_id;
    public $type;

    /**
     * $attribute - Array of block attributes
     * @var array
     * @access public
     */
    public $attribute = array();

    public function __set($name,$value) 
    {
        $this->$name = $value;
    }

    public function get_block_information() 
    {
        if (!isset($this->block_id)) {
            return false;
        }
        global $db;
        global $debug;

        $block_attribute_query = 'SELECT * FROM ' . BLOCK_TABLE . '
			WHERE id = '.$this->block_id.' LIMIT 1';
        $block_attribute_handle = $db->sql_query($block_attribute_query);
        $block = $db->sql_fetch_assoc($block_attribute_handle);
        $this->type = $block['type'];
        $debug->addMessage('Block type is '.$this->type, false);
        $block_attribute_temp = $block['attributes'];
        if (strlen($block_attribute_temp) > 0) {
            $block_attribute_temp = explode(",", $block_attribute_temp);
            $block_attribute_count = count($block_attribute_temp);
        } else {
            $block_attribute_count = 0;
        }
        for ($i = 0; $i < $block_attribute_count; $i++) {
            $attribute_temp = explode('=', $block_attribute_temp[$i]);
            $this->attribute[$attribute_temp[0]] = $attribute_temp[1];
            $debug->addMessage('Block '.$this->block_id.' has attribute '.$attribute_temp[0].' = '.$attribute_temp[1], false);
        }
        return;
    }

    function __toString() 
    {

    }
}
?>
