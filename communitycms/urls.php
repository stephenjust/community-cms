<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

require_once(ROOT.'includes/Router.class.php');
require_once(ROOT.'includes/controllers/AdminController.class.php');
require_once(ROOT.'includes/controllers/PageController.class.php');

$router = new Router();
$router->addRule("/", new PageController());
$router->addRule("/admin/", new AdminController());
$router->addRule("/admin/?(<module>\\w+)/", new AdminController());
$router->process();
