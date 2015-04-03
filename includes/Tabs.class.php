<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

/**
 * Class to generate jQuery tabs
 *
 * @package CommunityCMS.main
 */
class Tabs
{
    public $num_tabs;
    private $tab_list;
    private $tab_contents;

    function __construct() 
    {
        $num_tabs = 0;
        $tab_list = null;
        $tab_contents = null;
    }

    public function add_tab($tab_name,$tab_content) 
    {
        $this->num_tabs++;
        $tab_list_string = '<li>
			<a href="#tabs-'.$this->num_tabs.'">'.$tab_name.'</a>
			</li>'."\n";
        $this->tab_list .= $tab_list_string;
        unset($tab_string);
        $tab_content = '<div id="tabs-'.$this->num_tabs.'">'.$tab_content.'</div>';
        $this->tab_contents .= $tab_content."\n";
        unset($tab_content);
        return $this->num_tabs;
    }

    function __toString() 
    {
        $tab_layout = '<div id="tabs">'."\n".
        '<ul>'.$this->tab_list.'</ul>'.$this->tab_contents.'</div>';
        return $tab_layout;
    }
}
?>
