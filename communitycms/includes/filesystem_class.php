<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Manage all interaction with files more complex than including
 * 
 * @todo Implement this class
 * @package CommunityCMS.main
 */
class FileSystem {
	/**
	 * Local path where the Community CMS installation resides
	 * @var string Local path
	 */
	public $root_dir;

	/**
	 * FileSystem initialization function
	 */
	public function FileSystem() {
		$this->root_dir = dirname(__DIR__);
	}
}
?>
