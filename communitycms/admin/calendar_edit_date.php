<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_calendar_edit_date')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

global $debug;
/**
 * Include functions necessary for calendar operations
 */
include('./functions/calendar.php');

$content = NULL;
switch ($_GET['action']) {
	case 'edit':
		try {
			// Format date for insertion...
			$event_date = (isset($_POST['date'])) ? $_POST['date'] : date('d/m/Y');
			if (!preg_match('#^[0-1]?[0-9]/[0-3]?[0-9]/[1-2][0-9]{3}$#i',$event_date))
				throw new Exception('Invalid date. Must be formatted DD/MM/YYYY');
			$event_date_parts = explode('/',$event_date);
			$year = $event_date_parts[2];
			$month = $event_date_parts[0];
			$day = $event_date_parts[1];
			$start_time = parse_time($_POST['stime']);
			$end_time = parse_time($_POST['etime']);
			if (!$start_time || !$end_time || $start_time > $end_time)
				throw new Exception('You did not fill out one or more of the times properly. Please fix the problem and resubmit.');
			// Generate new start/end string
			$start = $year.'-'.$month.'-'.$day.' '.$start_time;
			$end = $year.'-'.$month.'-'.$day.' '.$end_time;
			$hide = (isset($_POST['hide'])) ? (boolean)$_POST['hide'] : false;
			event_edit($_POST['id'], $_POST['title'],
					$_POST['content'], $_POST['author'],
					$start, $end, $_POST['category'],
					$_POST['location'], $_POST['image'], $hide);
			$content = 'Successfully edited date information.<br />';
			$content .= '<a href="?module=calendar&amp;month='.$month.'&amp;year='.$year.'">Back to Event List</a>';
		}
		catch (Exception $e) {
			$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

// ----------------------------------------------------------------------------

	default:
		try {
			$event = event_get($_GET['id']);
			$form = new form;
			$form->set_method('post');
			$form->set_target('admin.php?module=calendar_edit_date&amp;action=edit');
			$form->add_hidden('author',htmlspecialchars($_SESSION['name']));
			$form->add_hidden('id',$event['id']);
			$form->add_textbox('title', '*Heading:', $event['header']);
			
			// Get category list
			$category_list_query = 'SELECT `cat_id`,`label`
				FROM `'.CALENDAR_CATEGORY_TABLE.'`
				ORDER BY `cat_id` ASC';
			$category_list_handle = $db->sql_query($category_list_query);
			if ($db->error[$category_list_handle])
				throw new Exception('Failed to read category list.');
			$category_names = array();
			$category_ids = array();
			for ($b = 1; $b <= $db->sql_num_rows($category_list_handle); $b++) {
				$category_list = $db->sql_fetch_assoc($category_list_handle);
				$category_names[] = $category_list['label'];
				$category_ids[] = $category_list['cat_id'];
			}
			$form->add_select('category', 'Category:',
					$category_ids, $category_names, $event['category']);
			$start = strtotime($event['start']);
			$end = strtotime($event['end']);
			$form->add_textbox('stime', '*Start Time:',
					date(get_config('time_format'),$start));
			$form->add_textbox('etime', '*End Time:',
					date(get_config('time_format'),$end));
			$form->add_date_cal('date', '*Date:', date('m/d/Y',$start));
			$form->add_textarea('content','Description:',$event['description'],'rows="25"');
			$form->add_textbox('location','Location:',$event['location']);
			$form->add_icon_list('image', 'Image:', 'newsicons', $event['image']);
			$form->add_checkbox('hide','Hidden:',$event['hidden']);
			$form->add_submit('submit','Save Event');
			$content = '<h1>Edit Calendar Date</h1>';
			$content .= $form;
		}
		catch (Exception $e) {
			$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}

		break;
}
?>