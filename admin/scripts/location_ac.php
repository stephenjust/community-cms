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

/**#@+
 * @ignore
 */
define('ROOT', '../../');
define('SECURITY', 1);
/**#@-*/

require_once ROOT.'vendor/autoload.php';
require '../../include.php';
require_once ROOT.'includes/content/CalLocation.class.php';

$term = FormUtil::get('term');
if ($term === null) {
    exit;
}

initialize('ajax');

$search = CalLocation::search($term, false);
$json_result = json_encode($search);
echo $json_result;
clean_up();
