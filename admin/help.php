<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
echo '<h1>Help</h1>';
$page = FormUtil::get('page', FILTER_DEFAULT, null, 'table_of_contents');
/**
 * Include the current help file
 */
echo require ROOT.'admin/help_pages/'.$page.'.php';
