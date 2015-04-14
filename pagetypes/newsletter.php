<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

$tpl = new \Smarty();
try {
    $newsletters = Newsletter::getByPage(Page::$id);
    $grouped_newsletters = array();
    foreach ($newsletters as $newsletter) {
        $grouped_newsletters[$newsletter->getYear()][] = $newsletter;
    }
    $tpl->assign("entries", $grouped_newsletters);
    return $tpl->fetch("newsletters.tpl");
} catch (\Exception $ex) {
    return '<span class="errormessage">'.$e->getMessage().'</span>';
}
