<?php
// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}
	global $CONFIG;
	global $db;
	global $site_info;
    global $page_info;
	$content = NULL;
	$current_contact = NULL;
	$j = 1;
	$contact_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users WHERE hide = 0 ORDER BY realname ASC';
	$contact_list_handle = $db->query($contact_list_query);
	$contact_list_num_rows = $contact_list_handle->num_rows;
	if(!isset($_GET['message'])) {
		$_GET['message'] = '';
		}
	if(!isset($_GET['action'])) {
		$_GET['action'] = '';
		}
	if($_GET['message'] != '') {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache"); // HTTP/1.0
		$content .= '<form method="POST" action="index.php?id='.$page_info['id'].'&action=send">
<input type="hidden" name="recipient" value="'.(int)$_GET['message'].'" />
Message to user:<br />
<textarea name="message" rows="5" cols="50"></textarea><br />
<input type="submit" value="Send Message" />
</form><form method="POST" action="index.php?id='.$page_info['id'].'"><input type="submit" value="Cancel" /></form>';
	}
	if($_GET['action'] == 'send') {
		$message = addslashes($_POST['message']);
		if(strlen($message) <= 10) {
			$content .= 'Your message was too short.';
			} else {
			$recipient = (int)$_POST['recipient'];
			$message_query = 'INSERT INTO '.$CONFIG['db_prefix']."messages (recipient,message) VALUES ($recipient,'$message')";
			$message_handle = $db->query($message_query);
			if(!$message_handle) {
				$content .= 'An error occured. Falied to send message.';
				} else {
				$content .= 'Message sent.';
				}
			}
		}
	if($contact_list_num_rows == 0) {
		$content .= 'There are no users with visible contact information.';
		} else {
		while ($contact_list_num_rows >= $j) {
			$template_contact = new template;
			$template_contact->load_file('contactlist');
			$contact_info = $contact_list_handle->fetch_assoc();
			$contact_email = NULL;
			if(eregi(',',$contact_info['realname'])) {
				$firstlast = explode(', ',$contact_info['realname']);
				$realname_firstlast = $firstlast[1].' '.$firstlast[0];
				} else {
				$realname_firstlast = $contact_info['realname'];
				}
			if($contact_info['message'] == 1) {
				$realname = '<a href="index.php?id='.$page_info['id'].'&message='.$contact_info['id'].'">'.$realname_firstlast.'</a>';
				} else {
				$realname = $realname_firstlast;
				}
			if($contact_info['email_hide'] == 1) {
				$contact_email = 'Hidden';
				} else {
				$contact_email = $contact_info['email'];
				}
			if($contact_info['phone_hide'] == 1) {
				$contact_telephone = 'Hidden';
				} else {
				$contact_telephone = $contact_info['phone'];
				}
			if($contact_info['address_hide'] == 1) {
				$contact_address = 'Hidden';
				} else {
				$contact_address = $contact_info['address'];
				}
			$template_contact->contact_name = stripslashes($realname);
			$template_contact->contact_title = stripslashes($contact_info['title']);
			$template_contact->contact_email = stripslashes($contact_email);
			$template_contact->contact_telephone = stripslashes($contact_telephone);
			$template_contact->contact_address = stripslashes($contact_address);
			if($contact_email == 'Hidden') {
				$template_contact->replace_range('contact_email','');
				} else {
				$template_contact->contact_email_start = '';
				$template_contact->contact_email_end = '';
				}
			if($contact_telephone == 'Hidden') {
				$template_contact->replace_range('contact_telephone','');
				} else {
				$template_contact->contact_telephone_start = '';
				$template_contact->contact_telephone_end = '';
				}
			if($contact_address == 'Hidden') {
				$template_contact->replace_range('contact_address','');
				} else {
				$template_contact->contact_address_start = '';
				$template_contact->contact_address_end = '';
				}
			if($contact_info['title'] == '' || $contact_info['title'] == NULL) {
				$template_contact->replace_range('contact_title','');
				} else {
				$template_contact->contact_title_start = '';
				$template_contact->contact_title_end = '';
				}
			$content .= $template_contact;
			unset($template_contact);
			$j++;
			}
		}
	return $content;
?>