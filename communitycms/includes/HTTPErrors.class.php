<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@gmail.com
 * @package CommunityCMS.main
 */

/**
 * Description of HTTPErrors
 *
 * @author Stephen
 */
class HTTPErrors {
	public static function throw404() {
		header("HTTP/1.0 404 Not Found");
		exit;
	}
}

?>
