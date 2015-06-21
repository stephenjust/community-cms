<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */
namespace CommunityCMS;
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * err_page - Generate an error page based on the error code. Error codes
 * are listed in './docs/errorcodes.txt'
 * @param int $code Error code
 */
function err_page($code = 0) 
{

    // The following block of code determines the error message to be displayed.
    switch($code) {
    default:
        $errormesg = "An unknown error has occured. Please file a bug report on the Community CMS SourceForge page describing what you were doing when the error occured so that we may fix the problem or create a more detailed error message.";
        break;
    case 10:
        $errormesg = 'Please update your Community CMS installation; the database is not the correct version.';
        break;
    case 11:
        $errormesg = "This Community CMS powered site is currently disabled.";
        break;
    case 12:
        $errormesg = "This Community CMS powered site is currently disabled.<br />";
        $errormesg .= Page::displayLoginBox();
        break;
    case 15:
        $errormesg = "Your config file is out of date. Please run the installer.";
        break;
    case 0001:
        $errormesg = "0001: A config file not found error has occured. If the problem still exists after five minutes, please attempt to contact the system administrator.";
        break;
    case 1001:
        $errormesg = "1001: A database connection error has occured. If the problem still exists after five minutes, please attempt to contact the system administrator.";
        break;
    case 1002:
        $errormesg = "1002: A database not found error has occured. If the problem still exists after five minutes, please attempt to contact the system administrator.";
        break;
    case 2001:
        $errormesg = "2001: A file not found error has occured. If the problem still exists after five minutes, please contact your system administrator.";
        break;
    case 3001:
        $errormesg = '3001: You forgot to specify either your username or your password. <a href=\'index.php\'>Go back.</a>';
        break;
    case 3002:
        $errormesg = '3002: Your session has timed out. <a href=\'index.php\'>Go back.</a>';
        break;
    case 3003:
        $errormesg = '3003: Either your username or your password was incorrect. <a href=\'index.php\'>Go back.</a>';
        break;
    case 3004:
        header('HTTP/1.1 403 Forbidden'); // Should sufficiently prevent search engine discovery.
        $errormesg = '3004: You do not have sufficient priveleges to view this page. <a href=\'index.php\'>Go back.</a>';
        break;
    }

    $template_path = './templates/default/';
    $template_file = $template_path."error.html";
    $handle = fopen($template_file, "r");
    $template = fread($handle, filesize($template_file));
    fclose($handle);
    $page_title = 'Community CMS - An Error Has Occured';
    $css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
    $image_path = $template_path.'images/';
    $content = $errormesg;
    $template = str_replace('<!-- $PAGE_TITLE$ -->', $page_title, $template);
    $template = str_replace('<!-- $CSS_INCLUDE$ -->', $css_include, $template);
    $template = str_replace('<!-- $CONTENT$ -->', $content, $template);
    $template = str_replace('<!-- $IMAGE_PATH$ -->', $image_path, $template);
    $template = str_replace('<!-- $FOOTER$ -->', '', $template);
    die($template);
}

// Add special \Exception classes
class AdminException extends \Exception
{
}
class GalleryException extends \Exception
{
}
class PageException extends \Exception
{
}
class PollException extends \Exception
{
}
