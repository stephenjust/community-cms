<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2014 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

class ContactsPage extends Page {
	public function getContent() {
		$clTpl = new Smarty();
		$clTpl->assign('contacts', Contact::getList($this->id));
		switch (get_config('contacts_display_mode')) {
			default:
				return $clTpl->fetch('contactList.tpl');
			case 'compact':
				return $clTpl->fetch('contactListCompact.tpl');
		}
	}
}
