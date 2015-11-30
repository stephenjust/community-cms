<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}
if(!preg_match('#^[\/\\\\\.]+$#',ROOT)) {
    die ('Invalid ROOT value.');
}
require_once(ROOT.'functions/error.php');
require_once(ROOT.'functions/main.php');
require_once(ROOT.'includes/constants.php');
require_once(ROOT.'functions/article.php');
require_once(ROOT.'functions/blocks.php');
require_once(ROOT.'functions/files.php');
require_once(ROOT.'functions/forms.php');
