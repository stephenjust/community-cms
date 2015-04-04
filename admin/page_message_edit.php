<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2014 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

require_once ROOT.'includes/acl/acl.php';
require_once ROOT.'includes/PageMessage.class.php';
acl::get()->require_permission('adm_page_message_edit');

if ($_GET['action'] == 'edit') {
    try {
        $pm = new PageMessage($_POST['id']);
        $pm->edit($_POST['page_id'], $_POST['update_content']);
        echo 'Successfully edited page message.<br />';
        echo HTML::link('admin.php?module=page_message&page='.$_POST['page_id'], 'Back');
    }
    catch (Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
} else {
    if (!isset($_GET['id']) || $_GET['id'] == '') {
        $_GET['id'] = 0;
    }
    $page_message = new PageMessage($_GET['id']);
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
