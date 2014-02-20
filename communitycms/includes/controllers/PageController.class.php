<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/controllers/Controller.class.php');
require_once(ROOT.'includes/controllers/LoginController.class.php');

class PageController extends Controller {
	public function __construct() {
		parent::__construct();
		$lc = new LoginController();
		$lc->run();
	}
	
	protected function setParameterDefaults() {
		$this->setParameterDefault('page', 'index');
	}

	public function run() {
		
	}
}
?>
