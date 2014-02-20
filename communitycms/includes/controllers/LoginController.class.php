<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/controllers/Controller.class.php');

/**
 * Handle login routines
 */
class LoginController extends Controller {

	protected function setParameterDefaults() {
		$this->setGetDefault('login', NULL);
		$this->setPostDefault('username', NULL);
		$this->setPostDefault('password', NULL);
	}

	public function run() {
		if ($_GET['login'] === NULL) return;
		switch ($_GET['login']) {
			default: return;
			case 'login':
				UserSession::get()->login($_POST['username'], $_POST['password']);
				break;
			case 'logout':
				UserSession::get()->logout();
				break;
		}
	}	
}

?>
