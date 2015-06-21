<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1) {
    die('You cannot access this page directly.');
}

$clTpl = new Tpl();
$clTpl->assign('contacts', Contact::getList(Page::$id));
switch (SysConfig::get()->getValue('contacts_display_mode')) {
default:
    $content = $clTpl->fetch('contactList.tpl');
    break;
case 'compact':
    $content = $clTpl->fetch('contactListCompact.tpl');
    break;
}

return $content;
