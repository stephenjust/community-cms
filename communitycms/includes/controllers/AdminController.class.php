<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/controllers/PageController.class.php');

class AdminController extends PageController {
	protected function setParameterDefaults() {
		$this->setParameterDefault('module', 'overview');
		$this->setGetDefault('action', NULL);
	}

	public function run() {
		echo $this->getParameter('module');
		exit;
	}
}
?>
