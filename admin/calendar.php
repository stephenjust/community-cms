<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;

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
		$title = addslashes($_POST['title']);
		$item_content = addslashes(remove_comments($_POST['content']));
		$author = addslashes($_POST['author']);
		$start_time = $_POST['stime'];
		$end_time = $_POST['etime'];
		$category = $_POST['category'];
		$location = addslashes($_POST['location']);

		// Format date for insertion...
		$event_date = (isset($_POST['date'])) ? $_POST['date'] : date('d/m/Y');
		if (!preg_match('#^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$#',$event_date)) {
			$content .= 'Invalidly formatted date. Use MM/DD/YYYY format.<br />'."\n";
			break;
		}
		$event_date_parts = explode('/',$event_date);
		$year = $event_date_parts[2];
		$month = $event_date_parts[0];
		$day = $event_date_parts[1];

		if ($start_time == "" || $end_time == "" || $year == "" || $title == "") {
			$content .= 'One or more fields was not filled out. Please complete the fields marked with a star and resubmit.<br />'."\n";
			break;
		}
		$stime = explode('-',$start_time);
		$etime = explode('-',$end_time);
		$start_time = parse_time($start_time);
		$end_time = parse_time($end_time);
		if (!$start_time || !$end_time || $start_time > $end_time) {
			$content .= "You did not fill out one or more of the times properly. Please fix the problem and resubmit.";
		} else {
			$create_date_query = 'INSERT INTO ' . CALENDAR_TABLE . '
				(category,starttime,endtime,year,month,day,header,description,
				location,author,image,hidden)
				VALUES ("'.$category.'","'.$start_time.'","'.$end_time.'",
				'.$year.','.$month.','.$day.',"'.$title.'","'.$item_content.'",
				"'.$location.'","'.$author.'","'.$image.'",'.$hide.')';
			$create_date = $db->sql_query($create_date_query);
			if ($db->error[$create_date] === 1) {
				$content .= 'Failed to create date information.<br />';
			} else {
				$content .= 'Successfully created date information. '
					.log_action('New date entry on '.$day.'/'.$month.'/'
					.$year.' \''.stripslashes($title).'\'');
			}
		}
		break;

// ----------------------------------------------------------------------------

	case 'delete':
		if (delete_date($_POST['date_del'])) {
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
		$category_name = addslashes($_POST['category_name']);
		if ($category_name != "") {
			if (!isset($_POST['colour'])) {
				$content .= 'No colour was selected for your new category. Category not created.<br />'."\n";
				break;
			}
			$create_category_query = 'INSERT INTO ' . CALENDAR_CATEGORY_TABLE . '
				(label,colour) VALUES (\''.$category_name.'\',\''.$_POST['colour'].'\')';
			$create_category = $db->sql_query($create_category_query);
			if($db->error[$create_category] === 1) {
				$content .= 'Failed to create category \''.$category_name.'\' ';
			} else {
				$content .= 'Successfully created category. '.log_action('New category \''.$category_name.'\'');
			}
		} else {
			$content .= 'You did not provide a name for your new category.';
		}
		break;
	case 'delete_category':
		if (!isset($_POST['delete_category_id'])) {
			$content .= 'No category selected to delete.<br />'."\n";
			break;
		}
		if (delete_category($_POST['delete_category_id'])) {
			$content .= 'Successfully deleted category entry.<br />'."\n";
		} else {
			$content .= 'Failed to delete category entry.<br />'."\n";
		}
		break;
	case 'save_settings':
		$new_fields['default_view'] = addslashes($_POST['default_view']);
		$new_fields['month_show_stime'] = (isset($_POST['month_show_stime'])) ? checkbox($_POST['month_show_stime']) : 0;
		$new_fields['month_show_cat_icons'] = (isset($_POST['month_show_cat_icons'])) ? checkbox($_POST['month_show_cat_icons']) : 0;
		$new_fields['month_day_format'] = (int)$_POST['month_day_format'];
		$save_settings_query = 'UPDATE `' . CALENDAR_SETTINGS_TABLE . "` SET
			`default_view` = '{$new_fields['default_view']}',
			`month_show_stime` = {$new_fields['month_show_stime']},
			`month_show_cat_icons` = {$new_fields['month_show_cat_icons']},
			`month_day_format` = {$new_fields['month_day_format']}";
		unset($new_fields);
		$save_settings_handle = $db->sql_query($save_settings_query);
		unset($save_settings_query);
		if ($db->error[$save_settings_handle] === 1) {
			$content .= 'Failed to save settings.<br />'."\n";
			break;
		}
		$content .= 'Updated calendar settings.<br />'."\n";
		log_action('Updated calendar settings');
		unset($save_settings_handle);
		break;
}

// ----------------------------------------------------------------------------

if (isset($_POST['month'])) {
	if ($_POST['month'] > 12 || $_POST['month'] < 1) {
		$_POST['month'] = date('m');
		}
	} else {
	$_POST['month'] = date('m');
	}
if (isset($_POST['year'])) {
	if($_POST['year'] < 1 || $_POST['year'] > 9999) {
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
$tab_content['manage'] .= '<form method="post" action="?module=calendar&action=delete">
<table class="admintable">
<tr><th>&nbsp;</th><th>Date:</th><th>Heading:</th><th></th></tr>';
$rowcount = 1;
$date_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
	WHERE year = '.$_POST['year'].' AND month = '.$_POST['month'].'
	ORDER BY day,starttime ASC';
$date_handle = $db->sql_query($date_query);
if ($db->sql_num_rows($date_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="4" class="row1">There are no dates in this month.</td></tr>';
	$rowcount = 2;
}
for ($i = 1; $i <= $db->sql_num_rows($date_handle); $i++) {
	$cal = $db->sql_fetch_assoc($date_handle);
	$cal_time = mktime(0,0,0,$cal['month'],$cal['day'],$cal['year']);
	$tab_content['manage'] .= '<tr><td class="row'.$rowcount.'">
		<input type="radio" name="date_del" value="'.$cal['id'].'" /></td>
		<td class="row'.$rowcount.'">'.date('M d, Y',$cal_time).'</td>
		<td class="row'.$rowcount.'">'.stripslashes($cal['header']).'</td>
		<td class="row'.$rowcount.'"><a href="admin.php?module=calendar_edit_date&id='.$cal['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px"
		height="16px" border="0px" /></a></td></tr>';
	if ($rowcount == 1) {
		$rowcount = 2;
	} else {
		$rowcount = 1;
	}
}
$tab_content['manage'] .= '<tr><td class="row'.$rowcount.'">&nbsp;</td>
	<td colspan="3" class="row'.$rowcount.'"><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
$tab_layout->add_tab('Manage Events',$tab_content['manage']);

// ----------------------------------------------------------------------------

$form_create = new form;
$form_create->set_target('admin.php?module=calendar&amp;action=new');
$form_create->set_method('post');
$form_create->add_hidden('author',stripslashes($_SESSION['name']));
$form_create->add_textbox('title','Heading*',stripslashes($_POST['title']));
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
$form_create->add_select('category','Category',$category_ids,$category_names,$category);
$form_create->add_textbox('stime','Start Time*',$_POST['stime']);
$form_create->add_textbox('etime','End Time*',$_POST['etime']);
$form_create->add_date_cal('date','Date',stripslashes($_POST['date']));
$form_create->add_textarea('content','Description',stripslashes($_POST['content']));
$form_create->add_textbox('location','Location',stripslashes($_POST['location']));
$form_create->add_icon_list('image','Image','newsicons',$image);
$form_create->add_checkbox('hide','Hidden',$hide);
$form_create->add_submit('submit','Create Event');
$tab_content['create'] = $form_create;
$tab_layout->add_tab('Create Event',$tab_content['create']);

// ----------------------------------------------------------------------------

$tab_content['settings'] = '<h1>Calendar Settings</h1>';
$current_settings = calendar_settings();
$settings_form = new form;
$settings_form->set_method('post');
$settings_form->set_target('?module=calendar&amp;action=save_settings');
$settings_form->add_select('default_view','Default View',array('month','day'),array('Current Month','Current Day'),$current_settings['default_view']);
$settings_form->add_checkbox('month_show_stime','Show Start Time on Month Calendar',$current_settings['month_show_stime']);
$settings_form->add_checkbox('month_show_cat_icons','Show Category Icons on Month Calendar',$current_settings['month_show_cat_icons']);
$settings_form->add_select('month_day_format','Label Days on Month Calendar as',array(1,2),array('Full Name (eg. Thursday)','Abbreviation (eg. Thurs)'),$current_settings['month_day_format']);
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
$content .= $tab_layout;
?>