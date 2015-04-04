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

$return = null;

try {
    $newsletters = Newsletter::getByPage(Page::$id);
    if (count($newsletters) == 0) {
        $return .= "No newsletters to display";
    } else {
        $currentyear = $newsletters[0]->getYear();
        $return .= "<div class='newsletter'><span class='newsletter_year'>".$currentyear."</span><br />\n";
    }
    foreach ($newsletters AS $newsletter) {
        if ($currentyear != $newsletter->getYear()) {
            $currentyear = $newsletter->getYear();
            $return .= "<span class='newsletter_year'>".$currentyear."</span><br />\n";
        }
        if (!$newsletter->getHidden()) {
            $return .= HTML::link($newsletter->getPath(), $newsletter->getLabel())."<br />\n";
        } else {
            $return .= $newsletter->getLabel()."<br />\n";
        }
    }
}
catch (NewsletterException $e) {
    $return .= '<span class="errormessage">'.$e->getMessage().'</span>';
}
return $return."</div>";
?>