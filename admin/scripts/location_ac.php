<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2015 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;
header("Content-type: text/plain");

$term = FormUtil::get('term');
if ($term === null) {
    exit;
}
/**#@+
 * @ignore
 */
define('ROOT', '../../');
define('SECURITY', 1);
/**#@-*/

require_once ROOT.'vendor/autoload.php';
require '../../include.php';
require_once ROOT.'includes/content/CalLocation.class.php';

initialize('ajax');

$search = CalLocation::search($term);
$json_result = json_encode($search);
echo $json_result;
clean_up();
