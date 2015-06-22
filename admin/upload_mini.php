<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

/**#@+
 * @ignore
 */
define('SECURITY', 1);
define('ADMIN', 1);
define('ROOT', '../');
/**#@-*/
require ROOT.'vendor/autoload.php';
require ROOT.'include.php';
require ROOT.'functions/error.php';
initialize();
if (!acl::get()->check_permission('admin_access')) {
    die('You don\'t have the necessary permissions to use this page');
}
$template = new Template;
$template->loadAdminFile('dialog');
$template->root = ROOT;
$template->dialog_title = 'Upload File';
$content = null;
// Check if the form has been submitted.
if(isset($_GET['upload'])) {
    try {
        if (isset($_POST['thumbs'])) {
            $content .= File::upload($_POST['path'], true);
        } else {
            $content .= File::upload($_POST['path']);
        }
    }
    catch (\Exception $e) {
        $content .= '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
    }
}
// Display upload form and upload location selector.
try {
    if (isset($_GET['dir'])) {
        $extra_vars = array();
        if (isset($_GET['thumb'])) {
            $extra_vars['thumbs'] = 1;
        }
        $content .= file_upload_box(0, $_GET['dir'], $extra_vars);
    } else {
        $content .= file_upload_box(1);
    }
}
catch (\Exception $e) {
    $content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
}
$template->dialog_body = $content;
echo $template;
clean_up();
