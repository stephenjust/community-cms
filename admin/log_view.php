<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

use CommunityCMS\Component\LogViewComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$view_all = false;

if (!acl::get()->check_permission('adm_log_view')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

switch ($_GET['action']) {
case 'delete':
    if (!acl::get()->check_permission('log_clear')) {
        echo '<span class="errormessage">You are not authorized to clear the log.</span><br />'."\n";
        break;
    }
    try {
        Log::clear();
        echo 'Cleared log messages.<br />'."\n";
    } catch (\Exception $ex) {
        echo '<span class="errormessage">Failed to clear log messages.</span><br />'."\n";
    }
    break;
case 'viewall':
    $view_all = true;
    break;
default:
    break;
}

// ----------------------------------------------------------------------------

$tab_content['view'] = null;
if ($view_all == true) {
    $limit = 1000;
} else {
    $limit = 50;
}
$log_component = new LogViewComponent();
$log_component->setMaxEntries($limit);
$tab_content['view'] = $log_component->render();
if ($view_all == false) {
    $tab_content['view'] .= '<form method="POST" action="?module=log_view&amp;action=viewall">'."\n".
    '<input type="submit" value="View All Logs" /></form>'."\n";
}
$tab_layout = new Tabs;
$tab_layout->add_tab('View Log Messages', $tab_content['view']);

if (acl::get()->check_permission('log_clear')) {
    $tab_content['delete'] = '<form method="post" action="admin.php?module=log_view&action=delete">
	<input type="submit" value="Clear Log Messages" />
	</form>';
    $tab_layout->add_tab('Delete Log Messages', $tab_content['delete']);
}

echo $tab_layout;
