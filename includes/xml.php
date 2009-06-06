<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

class xml {
	/**
	 * $xml_file
	 * @var string Name of currently opened file
	 */
	public $xml_file = NULL;
	public $parser;

	/**
	 * open_file - Sets the XML file for the parser to use
	 * @param string $path Path (relative to ROOT) of xml file
	 * @return boolean
	 */
	public function open_file($path) {
		$file_handle = fopen(ROOT.$path,'r');
		if (!$file_handle) {
			return false;
		}
		$this->xml_file = fread($file_handle,filesize(ROOT.$path));
		fclose($file_handle);
		return true;
	}

	public function parse() {
		xml_parse_into_struct($this->parser,$this->xml_file,$values,$index);
		return array('values' => $values, 'index' => $index);
	}

	// FIXME: Stub
	public function __construct() {
		if (!isset($this->parser)) {
			$this->parser = xml_parser_create('UTF-8');
			xml_parser_set_option($this->parser,XML_OPTION_TARGET_ENCODING,'UTF-8');
			xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);
		}
	}

	public function __destruct() {
		if (isset($this->parser)) {
			xml_parser_free($this->parser);
		}
	}
}

?>
