<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_calendar')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;
global $debug;

/**
 * Include functions necessary for calendar operations
 */
include (ROOT.'functions/calendar.php');

// ----------------------------------------------------------------------------

// Save form information from previously created entry
$_POST['title'] = (isset($_POST['title'])) ? $_POST['title'] : NULL;
$category = (isset($_POST['category'])) ? $_POST['category'] : NULL;
$_POST['stime'] = (isset($_POST['stime'])) ? $_POST['stime'] : NULL;
$_POST['etime'] = (isset($_POST['etime'])) ? $_POST['etime'] : NULL;
$_POST['date'] = (isset($_POST['date'])) ? $_POST['date'] : NULL;
$_POST['content'] = (isset($_POST['content'])) ? $_POST['content'] : NULL;
$_POST['location'] = (isset($_POST['location'])) ? $_POST['location'] : NULL;
$hide = (isset($_POST['hide'])) ? checkbox($_POST['hide']) : 0;
$image = (isset($_POST['image'])) ? $_POST['image'] : NULL;

switch ($_GET['action']) {
	default:

		break;

	case 'new':
		$create_return = event_create($_POST['title'],
				$_POST['content'],
				$_POST['author'],
				$_POST['stime'],
				$_POST['etime'],
				$_POST['date'],
				$_POST['category'],
				$_POST['location'],
				$image,
				$hide);

		// Parse the event date so that 'manage' tab can default to the
		// correct place
		$event_date_parts = explode('/',$_POST['date']);
		$year = isset($event_date_parts[2]) ? $event_date_parts[2] : date('Y');
		$month = isset($event_date_parts[0]) ? $event_date_parts[0] : date('m');

		if (!isset($_POST['month'])) {
			$_POST['month'] = $month;
		}
		if (!isset($_POST['year'])) {
			$_POST['year'] = $year;
		}

		// Parse error codes from function
		switch ($create_return) {
			default:
				$content .= '<span class="errormessage">Failed to create event.</span><br />';
				break;
			case 1:
				$content .= 'Successfully created event.<br />';
				break;
			case 2:
				$content .= '<span class="errormessage">You do not have the necessary permissions to create an event.</span><br />';
				break;
			case 3:
				$content .= '<span class="errormessage">Your event\'s date was formatted invalidly. It should be in the format dd/mm/yyyy.</span><br />';
				break;
			case 4:
				$content .= '<span class="errormessage">You forgot to fill out one or more fields.</span><br />';
				break;
			case 5:
				$content .= '<span class="errormessage">Invalid start or end time. Your event cannot end before it begins.</span><br />';
				break;
		}
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		if (event_delete($_GET['date_del'])) {
			$content .= 'Successfully deleted date entry.<br />'."\n";
		} else {
			$content .= 'Failed to delete date entry.<br />'."\n";
		}
		break;
	case 'delete_old_entries':
		$current_year = date('Y');
		$old_year = $current_year - 3;
		$delete_old_query = 'DELETE FROM `'.CALENDAR_TABLE.'`
			WHERE `year` <= '.$old_year;
		$delete_old_handle = $db->sql_query($delete_old_query);
		if ($db->error[$delete_old_handle] === 1) {
			$content .= 'Failed to delete old calendar entries.<br />'."\n";
		} else {
			log_action('Deleted old calendar entries ('.$old_year.' and previous)');
			$content .= 'Successfully deleted old calendar entries.<br />'."\n";
		}
		break;
	case 'create_category':
		$cat_name = $_POST['category_name'];
		$cat_icon = (isset($_POST['colour'])) ? $_POST['colour'] : NULL;
		if (event_cat_create($cat_name,$cat_icon)) {
			$content .= 'Successfully created event category.<br />'."\n";
		} else {
			$content .= '<span class="errormessage">Failed to create new event category.</span><br />'."\n";
		}
		break;
	case 'delete_category':
		if (!isset($_POST['delete_category_id'])) {
			$content .= 'No category selected to delete.<br />'."\n";
			break;
		}
		if (event_cat_delete($_POST['delete_category_id'])) {
			$content .= 'Successfully deleted category entry.<br />'."\n";
		} else {
			$content .= 'Failed to delete category entry.<br />'."\n";
		}
		break;
	case 'save_settings':
		if ($acl->check_permission('calendar_settings')) {
			$new_fields['default_view'] = addslashes($_POST['default_view']);
			$new_fields['month_show_stime'] = (isset($_POST['month_show_stime'])) ? checkbox($_POST['month_show_stime']) : 0;
			$new_fields['month_show_cat_icons'] = (isset($_POST['month_show_cat_icons'])) ? checkbox($_POST['month_show_cat_icons']) : 0;
			$new_fields['save_locations'] = (isset($_POST['save_locations'])) ? checkbox($_POST['save_locations']) : 0;
			$new_fields['month_day_format'] = (int)$_POST['month_day_format'];
			$new_fields['month_time_sep'] = $_POST['month_time_sep'];
			$new_fields['default_location'] = $_POST['default_location'];
			if (set_config('calendar_default_view',$new_fields['default_view']) &&
				set_config('calendar_month_show_stime',$new_fields['month_show_stime']) &&
				set_config('calendar_month_show_cat_icons',$new_fields['month_show_cat_icons']) &&
				set_config('calendar_month_day_format',$new_fields['month_day_format']) &&
				set_config('calendar_save_locations',$new_fields['save_locations']) &&
				set_config('calendar_month_time_sep',$new_fields['month_time_sep']) &&
				set_config('calendar_default_location',$new_fields['default_location']))
			{
				$content .= 'Updated calendar settings.<br />'."\n";
				log_action('Updated calendar settings');
			} else  {
				$content .= 'Failed to save settings.<br />'."\n";
			}
		}
		break;

// ----------------------------------------------------------------------------
	case 'new_source':
		$desc = addslashes($_POST['desc']);
		$url = addslashes(trim($_POST['url']));
		$new_src_query = 'INSERT INTO `' . CALENDAR_SOURCES_TABLE . '`
			(`desc`,`url`) VALUES (\''.$desc.'\',\''.$url.'\')';
		$new_src_handle = $db->sql_query($new_src_query);
		if ($db->error[$new_src_handle] === 1) {
			$content .= 'Failed to add calendar source.<br />'."\m";
		} else {
			log_action('Added calendar source \''.stripslashes($desc).'\'');
			$content .= 'Added calendar source.<br />'."\n";
		}
		break;
	case 'delete_source':
		// TODO
		break;
}

// ----------------------------------------------------------------------------

if (isset($_GET['month']) && !isset($_POST['month'])) {
	$_POST['month'] = $_GET['month'];
}
if (isset($_GET['year']) && !isset($_POST['year'])) {
	$_POST['year'] = $_GET['year'];
}

if (isset($_POST['month'])) {
	if ($_POST['month'] > 12 || $_POST['month'] < 1) {
		$_POST['month'] = date('m');
	}
} else {
	$_POST['month'] = date('m');
}
if (isset($_POST['year'])) {
	if ($_POST['year'] < 1 || $_POST['year'] > 9999) {
		$_POST['year'] = date('Y');
	}
} else {
	$_POST['year'] = date('Y');
}
$tab_layout = new tabs;
$tab_content['manage'] = '<form method="post" action="?module=calendar"><select name="month">';
$months = array('January','February','March','April','May','June','July',
	'August','September','October','November','December');
$monthcount = 1; 
while ($monthcount <= 12) {
	if ($_POST['month'] == $monthcount) {
		$tab_content['manage'] .= "<option value='".$monthcount."' selected >"
			.$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
		$monthcount++;
	} else {
		$tab_content['manage'] .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
		$monthcount++;
	}
}
$tab_content['manage'] .= '</select><input type="text" name="year" maxlength="4" size="4" value="'.$_POST['year'].'" /><input type="submit" value="Change" /></form>';
$tab_content['manage'] .= '<table class="admintable">
<tr><th>Date</th><th>Start Time</th><th>End Time</th><th>Heading</th><th colspan="2" width="40px"></th></tr>';
$rowcount = 1;
$date_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
	WHERE year = '.$_POST['year'].' AND month = '.$_POST['month'].'
	ORDER BY day,starttime ASC';
$date_handle = $db->sql_query($date_query);
if ($db->sql_num_rows($date_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="6" class="row1">There are no dates in this month.</td></tr>';
	$rowcount = 2;
}
for ($i = 1; $i <= $db->sql_num_rows($date_handle); $i++) {
	$cal = $db->sql_fetch_assoc($date_handle);

	// Format start time and end time
	$temp = explode(':',$cal['starttime']);
	$start_time_temp = mktime((int)$temp[0],(int)$temp[1]);
	$starttime = date(get_config('time_format'),$start_time_temp);
	$temp = explode(':',$cal['endtime']);
	$end_time_temp = mktime((int)$temp[0],(int)$temp[1]);
	$endtime = date(get_config('time_format'),$end_time_temp);
	unset($temp);
	unset($start_time_temp);
	unset($end_time_temp);

	$cal_time = mktime(0,0,0,$cal['month'],$cal['day'],$cal['year']);
	$tab_content['manage'] .= '<tr class="row'.$rowcount.'">
		<td>'.date('M d, Y',$cal_time).'</td>
		<td>'.$starttime.'</td>
		<td>'.$endtime.'</td>
		<td>'.$cal['header'].'</td>
		<td><a href="admin.php?module=calendar_edit_date&amp;id='.$cal['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px"
		height="16px" border="0px" /></a></td>
		<td><a href="javascript:confirm_delete(\'admin.php?module=calendar&amp;action=delete&amp;date_del='.$cal['id'].'&amp;month='.$cal['month'].'&amp;year='.$cal['year'].'\')">
		<img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px"
		height="16px" border="0px" /></a></td></tr>';
	if ($rowcount == 1) {
		$rowcount = 2;
	} else {
		$rowcount = 1;
	}
}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Events',$tab_content['manage']);

// ----------------------------------------------------------------------------

$form_create = new form;
$form_create->set_target('admin.php?module=calendar&amp;action=new');
$form_create->set_method('post');
$form_create->add_hidden('author',$_SESSION['name']);
$form_create->add_textbox('title','Heading*',$_POST['title']);
$category_list_query = 'SELECT cat_id,label FROM ' . CALENDAR_CATEGORY_TABLE . '
	ORDER BY cat_id ASC';
$category_list_handle = $db->sql_query($category_list_query);
if($db->error[$category_list_handle] === 1) {
    $category_ids = array('0');
    $category_names = array('Error');
}
for ($b = 1; $b <= $db->sql_num_rows($category_list_handle); $b++) {
    $category_list = $db->sql_fetch_assoc($category_list_handle);
    $category_ids[$b - 1] = $category_list['cat_id'];
    $category_names[$b - 1] = $category_list['label'];
}
if (!isset($_POST['location'])) {
	$new_location = get_config('calendar_default_location');
} else {
	$new_location = addslashes($_POST['location']);
}
$form_create->add_select('category','Category',$category_ids,$category_names,$category);
$form_create->add_textbox('stime','Start Time*',$_POST['stime'],'onChange="validate_form_field(\'calendar\',\'time\',\'_stime\')"');
$form_create->add_textbox('etime','End Time*',$_POST['etime'],'onChange="validate_form_field(\'calendar\',\'time\',\'_etime\')"');
$form_create->add_date_cal('date','Date',$_POST['date'],'onChange="validate_form_field(\'calendar\',\'date\',\'_date\')"');
$form_create->add_textarea('content','Description',$_POST['content']);
$form_create->add_textbox('location','Location',$new_location);
$form_create->add_icon_list('image','Image','newsicons',$image);
$form_create->add_checkbox('hide','Hidden',$hide);
$form_create->add_submit('submit','Create Event');
$tab_content['create'] = $form_create;
$tab_layout->add_tab('Create Event',$tab_content['create']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('calendar_settings')) {
	$tab_content['settings'] = '<h1>Calendar Settings</h1>';
	$settings_form = new form;
	$settings_form->set_method('post');
	$settings_form->set_target('?module=calendar&amp;action=save_settings');
	$settings_form->add_select('default_view','Default View',array('month','day'),array('Current Month','Current Day'),get_config('calendar_default_view'));
	$settings_form->add_checkbox('month_show_stime','Show Start Time on Month Calendar',get_config('calendar_month_show_stime'));
	$settings_form->add_checkbox('month_show_cat_icons','Show Category Icons on Month Calendar',get_config('calendar_month_show_cat_icons'));
	$settings_form->add_select('month_time_sep','Start Time Separator',array(' ','-',' - '),array('1:00pm Event','1:00pm-Event','1:00pm - Event'),get_config('calendar_month_time_sep'));
	$settings_form->add_select('month_day_format','Label Days on Month Calendar as',array(1,2),array('Full Name (eg. Thursday)','Abbreviation (eg. Thurs)'),get_config('calendar_month_day_format'));
	$settings_form->add_checkbox('save_locations','Save Location Entries',get_config('calendar_save_locations'));
	$settings_form->add_textbox('default_location','Default Event Location',get_config('calendar_default_location'),'');
	$settings_form->add_submit('submit','Save Changes');
	$tab_content['settings'] .= $settings_form;
	unset($settings_form);

	$tab_content['settings'] .= '<form method="post" action="?module=calendar&amp;action=create_category">
	<h1>Create New Category</h1>
	<table class="admintable">
	<tr><td width="150" class="row1">Name:</td><td class="row1"><input type=\'text\' name=\'category_name\' /></td></tr>
	<tr><td width="150" class="row2">Colour:</td><td class="row2">
	<input type="radio" name="colour" value="red" /><img src="./admin/templates/default/images/icon_red.png" width="10px" height="10px" alt="Red" />
	<input type="radio" name="colour" value="orange" /><img src="./admin/templates/default/images/icon_orange.png" width="10px" height="10px" alt="Orange" />
	<input type="radio" name="colour" value="yellow" /><img src="./admin/templates/default/images/icon_yellow.png" width="10px" height="10px" alt="Yellow" />
	<input type="radio" name="colour" value="green" /><img src="./admin/templates/default/images/icon_green.png" width="10px" height="10px" alt="Green" />
	<input type="radio" name="colour" value="cyan" /><img src="./admin/templates/default/images/icon_cyan.png" width="10px" height="10px" alt="Cyan" />
	<input type="radio" name="colour" value="blue" /><img src="./admin/templates/default/images/icon_blue.png" width="10px" height="10px" alt="Blue" /><br />
	<input type="radio" name="colour" value="purple" /><img src="./admin/templates/default/images/icon_purple.png" width="10px" height="10px" alt="Purple" />
	<input type="radio" name="colour" value="black" /><img src="./admin/templates/default/images/icon_black.png" width="10px" height="10px" alt="Black" />
	</td></tr>
	<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Create" /></td></tr>
	</table>
	</form>

	<form method="POST" action="?module=calendar&amp;action=delete_category">
	<h1>Delete Category</h1>
	<table class="admintable">
	<tr><td width="150" class="row1">Category:</td><td class="row1">&nbsp;</td></tr>
	<tr><td colspan="2" class="row2">';
	$category_query = 'SELECT * FROM ' . CALENDAR_CATEGORY_TABLE;
	$category_handle = $db->sql_query($category_query);
	for ($i = 1; $i <= $db->sql_num_rows($category_handle); $i++) {
		$cat = $db->sql_fetch_assoc($category_handle);
		$tab_content['settings'] .= '<input type="radio" name="delete_category_id" value="'.$cat['cat_id'].'" />
			<img src="./admin/templates/default/images/icon_'.$cat['colour'].'.png"
			width="10px" height="10px" alt="'.$cat['colour'].'" /> '.stripslashes($cat['label']).'<br />';
	}

	$tab_content['settings'] .= '</td></tr>
	<tr><td width="150" class="row1">&nbsp;</td><td class="row1">
	<input type="submit" value="Delete" /></td></tr>
	</table>
	</form>';

// ----------------------------------------------------------------------------

	$tab_content['settings'] .= '<h1>Delete Old Entries</h1>'."\n";
	$current_year = date('Y');
	$old_year = $current_year - 3;
	$num_old_query = 'SELECT `id` FROM `'.CALENDAR_TABLE.'` WHERE `year` <= '.$old_year;
	$num_old_handle = $db->sql_query($num_old_query);
	if ($db->error[$num_old_handle] === 1) {
		$button_label = 'Error';
		$button_disabled = 1;
	} else {
		$button_disabled = 0;
		if ($db->sql_num_rows($num_old_handle) == 0) {
			$button_disabled = 1;
			$button_label = 'No old entries ('.$old_year.' and previous)';
		} else {
			$button_label = 'Delete '.$db->sql_num_rows($num_old_handle).' old entries ('.$old_year.' and previous)';
		}
	}
	$button_disabled = ($button_disabled == 1) ? 'disabled' : NULL;
	$tab_content['settings'] .= '<form method="POST" action="?module=calendar&amp;action=delete_old_entries">
	<input type="submit" value="'.$button_label.'" '.$button_disabled.' />
	</form>';
	$tab_layout->add_tab('Settings',$tab_content['settings']);
}

// ----------------------------------------------------------------------------

if ($acl->check_permission('adm_calendar_import')) {
	$tab_content['import'] = NULL;

	// FIXME: Remove the warning below
	$tab_content['import'] .= 'This feature does not work yet. It is still in development.<br /><br />';

	$tab_content['import'] .= 'Using this tool, you can import calendar information from '.
		'Google Calendar and other iCal compatible online calendars.';

	$tab_content['import'] .= '<h1>Source List</h1>';
	$sources_query = 'SELECT * FROM `' . CALENDAR_SOURCES_TABLE . '`';
	$sources_handle = $db->sql_query($sources_query);
	if ($db->error[$sources_handle] === 1) {
		$tab_content['import'] .= 'Failed to read sources.<br />'."\n";
	} else {
		$num_sources = $db->sql_num_rows($sources_handle);
		if ($num_sources == 0) {
			$tab_content['import'] .= 'No sources available. Please add a new source.<br />'."\n";
		} else {
			$tab_content['import'] .= '<table class="admintable">'."\n";
			$tab_content['import'] .= '<tr><th>Source</th><th colspan="2"></th></th>'."\n";
			for ($i = 1; $i <= $num_sources; $i++) {
				$source = $db->sql_fetch_assoc($sources_handle);
				$tab_content['import'] .= '<tr><td>'.stripslashes($source['desc']).'</td>
					<td><a href="?module=calendar_import&amp;id='.$source['id'].'">Import</a></td><td><a href="?module=calendar&amp;action=delete_source&amp;id='.$source['id'].'">Delete</a></td></tr>'."\n";
			}
			$tab_content['import'] .= '</table>'."\n";
		}
	}

// ----------------------------------------------------------------------------

	$tab_content['import'] .= '<h1>Add Source</h1>';
	$add_src_form = new form;
	$add_src_form->set_method('post');
	$add_src_form->set_target('?module=calendar&amp;action=new_source');
	$add_src_form->add_textbox('desc','Description');
	$add_src_form->add_textbox('url','Location');
	$add_src_form->add_submit('submit','Add Source');
	$tab_content['import'] .= $add_src_form;

	$tab_layout->add_tab('Import Entries',$tab_content['import']);
}

$content .= $tab_layout;
?>