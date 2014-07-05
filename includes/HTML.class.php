<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Helper class for generating HTML strings 
 */
class HTML {
	/**
	 * Generate an HTML div tag
	 * @param string $class
	 * @param string $content
	 * @return string
	 */
	public static function div($class, $content) {
		return sprintf('<div class="%s">%s</div>', HTML::schars($class), $content);
	}

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
		if ($class !== null) {
			$link .= 'class="'.HTML::schars($class).'" ';
		}
		if ($id !== null) {
			$link .= 'id="'.HTML::schars($id).'" ';
		}
		$link .= '>'.$label.'</a>';
		return $link;
	}

	/**
	 * Generate an HTML image tag, using an image from the template path
	 * @param string $src Image file name
	 * @param string $alt_text
	 * @param string $class
	 * @param string $style
	 * @return string
	 */
	public static function templateImage($src, $alt_text, $class = null, $style = null) {
		return HTML::image('<!-- $IMAGE_PATH$ -->'.$src, $alt_text, $class, $style);
	}

	/**
	 * Generate an HTML image tag
	 * @param string $src Image file path
	 * @param string $alt_text
	 * @param string $class
	 * @param string $style
	 * @return string
	 */
	public static function image($src, $alt_text, $class = null, $style = null) {
		$image = '<img src="'.$src.'" alt="'.HTML::schars($alt_text).'" ';
		if ($class) {
			$image .= 'class="'.$class.'" ';
		}
		if ($style) {
			$image .= 'style="'.$style.'" ';
		}
		$image .= '/>';
		return $image;
	}

	/**
	 * Convert HTML special characters to entities
	 * @param string $string
	 * @return string
	 */
	public static function schars($string) {
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
	}

	/**
	 * Generate an HTML meta tag
	 * @param string $name
	 * @param string $content
	 * @return string
	 */
	public static function meta($name, $content) {
		return sprintf('<meta name="%s" content="%s" />', HTML::schars($name), HTML::schars($content));
	}

	/**
	 * Generate an HTML meta http-equiv tag
	 * @param string $name
	 * @param string $content
	 * @return string
	 */
	public static function metaHTTP($name, $content) {
		return sprintf('<meta http-equiv="%s" content="%s" />', HTML::schars($name), HTML::schars($content));
	}
}
