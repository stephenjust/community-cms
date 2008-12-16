<?php
// Security Check
	if (@SECURITY != 1) {
		die('You cannot access this page directly.');
		}
	global $CONFIG;
	global $db;
	global $site_info;
	$content = NULL;
	$current_contact = NULL;
	$j = 1;
	$contact_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users WHERE hide = 0 ORDER BY realname ASC';
	$contact_list_handle = $db->query($contact_list_query);
	$contact_list_num_rows = $contact_list_handle->num_rows;
	$contact_template_handle = load_template_file('contactlist.html');
	$contact_template = $contact_template_handle['contents'];
	$template_path = $contact_template_handle['template_path'];
	if(!isset($_GET['message'])) {
		$_GET['message'] = '';
		}
	if(!isset($_GET['action'])) {
		$_GET['action'] = '';
		}
	if($_GET['message'] != '') {
		$content .= '<form method="POST" action="index.php?id='.$_GET['id'].'&action=send">
<input type="hidden" name="recipient" value="'.$_GET['message'].'" />
Message to user:<br />
<textarea name="message" rows="5" cols="50"></textarea><br />
<input type="submit" value="Send Message" />
</form><form method="POST" action="index.php?id='.$_GET['id'].'"><input type="submit" value="Cancel" /></form>';
	}
	if($_GET['action'] == 'send') {
		$message = addslashes($_POST['message']);
		if(strlen($message) <= 10) {
			$content .= 'Your message was too short.';
			} else {
			$recipient = addslashes($_POST['recipient']);
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
			$contact_info = $contact_list_handle->fetch_assoc();
			$current_contact .= $contact_template;
			$contact_email = NULL;
			if($contact_info['message'] == 1) {
				$realname = '<a href="index.php?id='.$_GET['id'].'&message='.$contact_info['id'].'">'.$contact_info['realname'].'</a>';
				} else {
				$realname = $contact_info['realname'];
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
			if(eregi(',',$realname)) {
				$firstlast = explode(', ',$realname);
				$realname_firstlast = $firstlast[1].' '.$firstlast[0];
				} else {
				$realname_firstlast = $realname;
				}
			$current_contact = str_replace('<!-- $CONTACT_NAME$ -->',stripslashes($realname_firstlast),$current_contact);
			$current_contact = str_replace('<!-- $CONTACT_TITLE$ -->',stripslashes($contact_info['title']),$current_contact);
			$current_contact = str_replace('<!-- $CONTACT_EMAIL$ -->',stripslashes($contact_email),$current_contact);
			$current_contact = str_replace('<!-- $CONTACT_TELEPHONE$ -->',stripslashes($contact_telephone),$current_contact);
			$current_contact = str_replace('<!-- $CONTACT_ADDRESS$ -->',stripslashes($contact_address),$current_contact);
			if($contact_email == 'Hidden') {
				$start = NULL;
				$end = NULL;
				$replace_length = NULL;
				$start = strpos($current_contact,'<!-- $CONTACT_EMAIL_START$ -->');
				$end = strpos($current_contact,'<!-- $CONTACT_EMAIL_END$ -->');
				if($start && $end) {
					$replace_length = $end - $start + 28;
					$current_contact = substr_replace($current_contact,'',$start,$replace_length);
					}
				} else {
				$current_contact = str_replace('<!-- $CONTACT_EMAIL_START$ -->','',$current_contact);
				$current_contact = str_replace('<!-- $CONTACT_EMAIL_END$ -->','',$current_contact);
				}
			if($contact_telephone == 'Hidden') {
				$start = NULL;
				$end = NULL;
				$replace_length = NULL;
				$start = strpos($current_contact,'<!-- $CONTACT_TELEPHONE_START$ -->');
				$end = strpos($current_contact,'<!-- $CONTACT_TELEPHONE_END$ -->');
				if($start && $end) {
					$replace_length = $end - $start + 32;
					$current_contact = substr_replace($current_contact,'',$start,$replace_length);
					}
				} else {
				$current_contact = str_replace('<!-- $CONTACT_TELEPHONE_START$ -->','',$current_contact);
				$current_contact = str_replace('<!-- $CONTACT_TELEPHONE_END$ -->','',$current_contact);
				}
			if($contact_address == 'Hidden') {
				$start = NULL;
				$end = NULL;
				$replace_length = NULL;
				$start = strpos($current_contact,'<!-- $CONTACT_ADDRESS_START$ -->');
				$end = strpos($current_contact,'<!-- $CONTACT_ADDRESS_END$ -->');
				if($start && $end) {
					$replace_length = $end - $start + 30;
					$current_contact = substr_replace($current_contact,'',$start,$replace_length);
					}
				} else {
				$current_contact = str_replace('<!-- $CONTACT_ADDRESS_START$ -->','',$current_contact);
				$current_contact = str_replace('<!-- $CONTACT_ADDRESS_END$ -->','',$current_contact);
				}
			if($contact_info['title'] == '' || $contact_info['title'] == NULL) {
				$start = NULL;
				$end = NULL;
				$replace_length = NULL;
				$start = strpos($current_contact,'<!-- $CONTACT_TITLE_START$ -->');
				$end = strpos($current_contact,'<!-- $CONTACT_TITLE_END$ -->');
				if($start && $end) {
					$replace_length = $end - $start + 28;
					$current_contact = substr_replace($current_contact,'',$start,$replace_length);
					}
				} else {
				$current_contact = str_replace('<!-- $CONTACT_TITLE_START$ -->','',$current_contact);
				$current_contact = str_replace('<!-- $CONTACT_TITLE_END$ -->','',$current_contact);
				}
			$content .= $current_contact;
			$current_contact = NULL;
			$j++;
			}
		}
	return $content;
?>