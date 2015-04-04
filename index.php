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
if (get_config('site_active') == 0) {
    err_page(12);
}

// Initialize some variables to keep PHP from complaining
$view = (isset($_GET['view'])) ? $_GET['view'] : null;
unset($_GET['view']);

// Figure out which page to fetch from the provided variables
if (!isset($_GET['id']) && !isset($_GET['page'])) {
    // No page provided - go to home page
    $page_id      = get_config('home');
    $page_text_id = null;
} else {
    if (isset($_GET['page'])) {
        $page_id      = null;
        $page_text_id = addslashes($_GET['page']);
    } else {
        // Don't cast (int) on $page_id because it could be a special page (text)
        $page_id      = $_GET['id'];
        $page_text_id = null;
    }
}
unset($_GET['page'], $_GET['id']);

// Load page information.
$page = new Page();
if ($page_id == null && $page_text_id != null) {
    Page::set_page($page_text_id, false);
} else {
    Page::set_page($page_id);
}
if (file_exists('./install')) {
    $debug->addMessage('The ./install directory still exists', true);
}

// Display the page.
Page::display_header();
Page::display_left();
Page::display_right();
Page::display_content();
if (DEBUG === 1) {
    Page::display_debug();
}
Page::display_footer();

clean_up();

// Page load timer
if (DEBUG === 1) {
    $totaltime = (microtime(true) - $starttime);
    printf("This page took %f seconds to load.", $totaltime);
}
