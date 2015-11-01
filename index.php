<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

// The not-so-secure security check.
define('SECURITY', 1);
define('ROOT', './');

require_once 'vendor/autoload.php';

require_once ROOT.'include.php';

// Page load timer
if (DEBUG === 1) {
    $starttime = microtime(true);
}

initialize();

// Check if site is active
if (SysConfig::get()->getValue('site_active') == 0) {
    err_page(12);
}

$page_id = FormUtil::get('id', FILTER_DEFAULT, null, SysConfig::get()->getValue('home'));
$page_text_id = FormUtil::get('page');

// Load page information.
$page = new Page();
if ($page_text_id != null) {
    Page::setPage($page_text_id, false);
} else {
    Page::setPage($page_id);
}

if (file_exists('./install')) {
    Debug::get()->addMessage('The ./install directory still exists', true);
}

// Display the page.
Page::displayHeader();
Page::displayLeft();
Page::displayRight();
Page::displayContent();
if (DEBUG === 1) {
    Page::displayDebug();
}
Page::displayFooter();

clean_up();

// Page load timer
if (DEBUG === 1) {
    $totaltime = (microtime(true) - $starttime);
    printf("This page took %f seconds to load.", $totaltime);
}
