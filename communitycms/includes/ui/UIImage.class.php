<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/ui/UI.class.php');

class UIImage extends UI {
	protected $width;
	protected $height;
	protected $src;
	protected $alt;
	protected $border = false;
	
	protected function parse_params($params) {
		if (array_key_exists('width', $params))
			$this->width = $params['width'];
		if (array_key_exists('height', $params))
			$this->height = $params['height'];
		if (array_key_exists('src', $params))
			$this->src = $params['src'];
		if (array_key_exists('alt', $params))
			$this->alt = $params['alt'];
	}
	
	public function __toString() {
		// Require image source to be set
		assert($this->src != NULL);
		
		$r = '<img src="'.HTML::schars($this->src).'" ';
		if ($this->width)
			$r .= 'width="'.$this->width.'" ';
		if ($this->height)
			$r .= 'height="'.$this->height.'" ';
		if ($this->alt)
			$r .= 'alt="'.HTML::schars($this->alt).'" ';
		if ($this->border !== false)
			$r .= 'border="'.$this->border.'" ';
		$r .= '/>';

		return $r;
	}
}
?>
