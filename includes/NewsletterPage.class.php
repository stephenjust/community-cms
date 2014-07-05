<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class NewsletterPage extends Page {
	public function getContent() {
		try {
			$return = null;
			$newsletters = Newsletter::getByPage($this->id);
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
	}
}
