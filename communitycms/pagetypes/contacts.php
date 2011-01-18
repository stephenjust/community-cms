<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die('You cannot access this page directly.');
}
global $db;
global $page;
$content = NULL;
$current_contact = NULL;
$contact_list_query = 'SELECT `contacts`.*
	FROM `'.CONTACTS_TABLE.'` `contacts`, `'.CONTENT_TABLE.'` `content`
	WHERE `content`.`page_id` = '.$page->id.'
	ORDER BY `content`.`order` ASC';
$contact_list_handle = $db->sql_query($contact_list_query);
$contact_list_num_rows = $db->sql_num_rows($contact_list_handle);
if (!isset($_GET['message'])) {
	$_GET['message'] = '';
}
if (!isset($_GET['action'])) {
	$_GET['action'] = '';
}
if($_GET['message'] != '') {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	$content .= '<form method="POST" action="index.php?'.$page->url_reference.'&action=send">
<input type="hidden" name="recipient" value="'.(int)$_GET['message'].'" />
Message to user:<br />
<textarea name="message" rows="5" cols="50"></textarea><br />
<input type="submit" value="Send Message" />
</form><form method="POST" action="index.php?'.$page->url_reference.'"><input type="submit" value="Cancel" /></form>';
}
if($_GET['action'] == 'send') {
	$message = addslashes($_POST['message']);
	if (strlen($message) <= 10) {
		$content .= 'Your message was too short.';
	} else {
		$recipient = (int)$_POST['recipient'];
		$message_query = 'INSERT INTO ' . MESSAGE_TABLE . "
			(recipient,message) VALUES ($recipient,'$message')";
		$message_handle = $db->sql_query($message_query);
		if ($db->error[$message_handle] === 1) {
			$content .= 'An error occured. Falied to send message.';
		} else {
			$content .= 'Message sent.';
		}
	}
}
if ($contact_list_num_rows == 0) {
	$content .= 'There are no contacts.';
	return $content;
}
$contact_template = new template;
switch (get_config('contacts_display_mode')) {
	default:
		$contact_template->load_file('contactlist');
		break;
	case 'compact':
		$contact_template->load_file('contactlist-compact');
		break;
}
$contact_template_body = $contact_template->split('contact_entry_start');
$contact_template_body->contact_entry_start = NULL;
$content .= (string)$contact_template;
unset($contact_template);

$contact_template_foot = $contact_template_body->split('contact_entry_end');
$contact_template_foot->contact_entry_end = NULL;

for ($i = 1; $contact_list_num_rows >= $i; $i++) {
	$contact_info = $db->sql_fetch_assoc($contact_list_handle);

	// Prepare contact information
	if ($contact_info['user_id'] != 0) {
		$realname = '<a href="index.php?'.$page->url_reference.'&message='.$contact_info['user_id'].'">'.stripslashes($contact_info['name']).'</a>';
	} else {
		$realname = stripslashes($contact_info['name']);
	}
	$contact_title = stripslashes($contact_info['title']);
	if (strlen($contact_info['email']) == 0) {
		$contact_email = NULL;
	} else {
		$contact_email = $contact_info['email'];
	}
	if ($contact_info['phone'] == NULL) {
		$contact_telephone = NULL;
	} else {
		$contact_telephone = format_tel($contact_info['phone']);
	}
	if (strlen($contact_info['address']) == 0) {
		$contact_address = NULL;
	} else {
		$contact_address = $contact_info['address'];
	}

	$template_contact = clone $contact_template_body;
	$template_contact->contact_name = $realname;
	$template_contact->contact_title = $contact_title;
	$template_contact->contact_email = stripslashes($contact_email);
	$template_contact->contact_telephone = stripslashes($contact_telephone);
	$template_contact->contact_address = stripslashes($contact_address);
	if ($contact_email == NULL) {
		$template_contact->replace_range('contact_email','');
	} else {
		$template_contact->contact_email_start = '';
		$template_contact->contact_email_end = '';
	}
	if ($contact_telephone == NULL) {
		$template_contact->replace_range('contact_telephone','');
	} else {
		$template_contact->contact_telephone_start = '';
		$template_contact->contact_telephone_end = '';
	}
	if ($contact_address == NULL) {
		$template_contact->replace_range('contact_address','');
	} else {
		$template_contact->contact_address_start = '';
		$template_contact->contact_address_end = '';
	}
	if ($contact_info['title'] == '' || $contact_info['title'] == NULL) {
		$template_contact->replace_range('contact_title','');
	} else {
		$template_contact->contact_title_start = '';
		$template_contact->contact_title_end = '';
	}
	$content .= (string)$template_contact;
	unset($template_contact);
}
$content .= (string)$contact_template_foot;
unset($contact_template_foot);

return $content;
?>