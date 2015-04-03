<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

require_once ROOT.'includes/ui/UIImage.class.php';

class UIIcon extends UIImage
{
    protected $width = 16;
    protected $height = 16;
    protected $border = 0;

    public function __toString() 
    {
        // Require image source to be set
        assert($this->src != null);
        
        $r = '<img src="<!-- $IMAGE_PATH$ -->'.HTML::schars($this->src).'" ';
        if ($this->width) {
            $r .= 'width="'.$this->width.'" '; 
        }
        if ($this->height) {
            $r .= 'height="'.$this->height.'" '; 
        }
        if ($this->alt) {
            $r .= 'alt="'.HTML::schars($this->alt).'" '; 
        }
        if ($this->border !== false) {
            $r .= 'border="'.$this->border.'" '; 
        }
        $r .= '/>';

        return $r;
    }
}
?>
