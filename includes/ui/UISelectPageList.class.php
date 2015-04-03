<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

require_once ROOT.'includes/ui/UISelect.class.php';
require_once ROOT.'includes/File.class.php';

class UISelectPageList extends UISelect
{
    private $pagetype;
    
    protected function parse_params($params) 
    {
        if (array_key_exists('pagetype', $params)) {
            $this->pagetype = $params['pagetype']; 
        }
    }
    
    protected function preload() 
    {
        global $db;

        if (!$this->pagetype) {
            $query = 'SELECT *
				FROM `'.PAGE_TABLE.'`
				ORDER BY `title` ASC'; 
        }
        else {
            $query = 'SELECT *
				FROM `'.PAGE_TABLE.'`
				WHERE `type` = '.(int)$this->pagetype.'
				ORDER BY `title` ASC'; 
        }
        
        $handle = $db->sql_query($query);
        if ($db->error[$handle] === 1) {
            throw new Exception('Error reading page list.'); 
        }
        for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
            $result = $db->sql_fetch_assoc($handle);
            $this->addOption($result['id'], $result['title']);
        }
    }
}
?>
