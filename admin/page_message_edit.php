<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2015 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_page_message_edit');

if (FormUtil::get('action') == 'edit') {
    try {
        $pm = new PageMessage(FormUtil::post('id'));
        $pm->edit(FormUtil::post('page_id'), FormUtil::post('update_content', FILTER_UNSAFE_RAW));
        echo 'Successfully edited page message.<br />';
        echo HTML::link('admin.php?module=page_message&page='.FormUtil::post('page_id'), 'Back');
    }
    catch (\Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
} else {
    $page_message = new PageMessage(FormUtil::get('id', FILTER_VALIDATE_INT));
    echo '<form method="POST" action="admin.php?module=page_message_edit&action=edit">
		<h1>Edit Page Message</h1>
		<table class="admintable">
		<input type="hidden" name="id" value="'.$page_message->getId().'" />
		<input type="hidden" name="page_id" value="'.$page_message->getPage().'" />
		<tr><td class="row1" valign="top">Content:</td>
		<td class="row1">
		<textarea name="update_content" rows="30">
		'.$page_message->getContent().'</textarea>
		</td></tr>
		<tr><td width="150" class="row2">&nbsp;</td><td class="row2">
		<input type="submit" value="Submit" /></td></tr>
		</table>';
}
