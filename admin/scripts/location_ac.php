<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

header("Content-type: text/plain");

if (!isset($_GET['term'])) {
    exit;
}
/**#@+
 * @ignore
 */
define('ROOT', '../../');
define('SECURITY', 1);
/**#@-*/

require '../../config.php';
require '../../include.php';
require_once ROOT.'includes/content/CalLocation.class.php';

initialize('ajax');

$search = CalLocation::search($_GET['term']);
$json_result = json_encode($search);
echo $json_result;
clean_up();
?>