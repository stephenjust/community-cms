<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class UISelectDirList extends UISelect
{
    protected function preload() 
    {
        $list = File::getCategorizedDirList();
        
        $this->addOption('', 'Default');
        
        foreach ($list AS $category => $values) {
            if ($category == 'Uncategorized') { $category = null; 
            }
            
            foreach ($values AS $value) {
                $this->addOption($value, $value, $category);
            }
        }
    }
}
?>
