<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
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
if(!include_once(ROOT.'functions/main.php')) {
    err_page(2001);
}
require(ROOT . 'includes/constants.php');
require(ROOT . 'includes/db/db.php');
require(ROOT . 'includes/template.php');
require(ROOT . 'functions/login.php');
require(ROOT . 'functions/article.php');
require(ROOT . 'includes/page.php');
require(ROOT . 'includes/gallery.php');
require(ROOT . 'includes/log_class.php');
if(!include_once(ROOT.'functions/page_class.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'classes.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/display.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/blocks.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/files.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/forms.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/poll.php')) {
    err_page(2001);
}
?>