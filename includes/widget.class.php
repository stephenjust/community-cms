<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

/**
 * A container for content in the sidebars
 *
 * @author Stephen
 */
class widget
{
    private $title = null;
    private $content = null;
    
    private $dimension_w = 0; // auto
    private $dimension_h = 0; // auto
    
    public function setTitle($title) 
    {
        $this->title = htmlspecialchars($title);
    }
    
    public function setContent($content) 
    {
        $this->content = $content;
    }
    
    public function setDimensions($w = 0, $h = 0) 
    {
        $this->dimension_h = $h;
        $this->dimension_w = $w;
    }
    
    public function __toString()
    {
        $template = new template;
        $template->load_file('widget');
        $template->widget_title = $this->title;
        $template->widget_content = $this->content;
        
        if ($this->dimension_h === 0 && $this->dimension_w === 0) {
            $template->widget_dimensions = null;
        } elseif ($this->dimension_h !== 0 && $this->dimension_w !== 0) {
            $template->widget_dimensions = "width=\"$this->dimension_w\" height=\"$this->dimension_h\"";
        } elseif ($this->dimension_h !== 0 && $this->dimension_w === 0) {
            $template->widget_dimensions = "height=\"$this->dimension_h\"";
        } else {
            $template->widget_dimensions = "width=\"$this->dimension_w\"";
        }
        
        return (string) $template;
    }
}

?>
