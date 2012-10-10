<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/HTML_Select.class.php');

/**
 * Helper class for generating HTML strings 
 */
class HTML {
	/**
	 * Generate an HTML anchor
	 * @param string $href Target URL
	 * @param string $label Link label (may contain html)
	 * @param string $class Link class
	 * @param string $id Link id
	 * @return string Link
	 */
	public static function link($href, $label, $class = null, $id = null) {
		$link = '<a href="'.HTML::schars($href).'" ';
		if ($class !== null)
			$link .= 'class="'.HTML::schars($class).'" ';
		if ($id !== null)
			$link .= 'id="'.HTML::schars($id).'" ';
		$link .= '>'.$label.'</a>';
		return $link;
	}
	
	/**
	 * Convert HTML special characters to entities
	 * @param string $string
	 * @return string
	 */
	public static function schars($string) {
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
	}
}
?>
