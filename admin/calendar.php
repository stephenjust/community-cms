<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_calendar');

// Save form information from previously created entry
$_POST['title'] = (isset($_POST['title'])) ? $_POST['title'] : null;
$category = (isset($_POST['category'])) ? $_POST['category'] : null;
$_POST['category_check'] = (isset($_POST['category_check'])) ? checkbox($_POST['category_check']) : 0;
$_POST['stime'] = (isset($_POST['stime'])) ? $_POST['stime'] : null;
$_POST['etime'] = (isset($_POST['etime'])) ? $_POST['etime'] : null;
$_POST['date'] = (isset($_POST['date'])) ? $_POST['date'] : null;
$_POST['content'] = (isset($_POST['content'])) ? $_POST['content'] : null;
$_POST['location'] = (isset($_POST['location'])) ? $_POST['location'] : null;
$_POST['location_check'] = (isset($_POST['location_check'])) ? checkbox($_POST['location_check']) : 0;
$hide = (isset($_POST['hide'])) ? checkbox($_POST['hide']) : 0;
$image = (isset($_POST['image'])) ? $_POST['image'] : null;

switch ($_GET['action']) {
default:

    break;

case 'new':
    try {
        CalEvent::create(
            $_POST['title'],
            $_POST['content'],
            $_POST['author'],
            $_POST['stime'],
            $_POST['etime'],
            $_POST['date'],
            $_POST['category'],
            $_POST['category_check'],
            $_POST['location'],
            $_POST['location_check'],
            $image,
            $hide
        );
        echo 'Successfully created event.<br />';
    }
    catch (CalEventException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }

    // Parse the event date so that 'manage' tab can default to the
    // correct place
    $event_date_parts = explode('/', $_POST['date']);
    $year = isset($event_date_parts[2]) ? $event_date_parts[2] : date('Y');
    $month = isset($event_date_parts[0]) ? $event_date_parts[0] : date('m');

    if (!isset($_POST['month'])) {
        $_POST['month'] = $month;
    }
    if (!isset($_POST['year'])) {
        $_POST['year'] = $year;
    }
    break;

// ----------------------------------------------------------------------------

case 'delete':
    try {
        $ev = new CalEvent($_GET['date_del']);
        $ev->delete();
        echo 'Successfully deleted date entry.<br />';
    }
    catch (CalEventException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'delete_old_entries':
    try {
        $range = mktime(23, 59, 59, 12, 31, date('Y') - 3);
        CalEvent::deleteRange(0, $range);
        echo 'Successfully deleted old calendar entries.<br />';
    } catch (CalEventException $ex) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'create_category':
    $cat_name = $_POST['category_name'];
    $cat_icon = (isset($_POST['colour'])) ? $_POST['colour'] : null;
    try {
        CalCategory::create($cat_name, $cat_icon);
        echo 'Created category '.HTML::schars($cat_name).'.';
    } catch (CalCategoryException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'delete_category':
    if (!isset($_POST['delete_category_id'])) {
        echo 'No category selected to delete.<br />'."\n";
        break;
    }
    try {
        $cat = new CalCategory($_POST['delete_category_id']);
        $cat->delete();
        echo 'Successfully deleted category entry.<br />';
    } catch (CalCategoryException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'save_settings':
    if (acl::get()->check_permission('calendar_settings')) {
        $new_fields['default_view'] = addslashes($_POST['default_view']);
        $new_fields['month_show_stime'] = (isset($_POST['month_show_stime'])) ? checkbox($_POST['month_show_stime']) : 0;
        $new_fields['month_show_cat_icons'] = (isset($_POST['month_show_cat_icons'])) ? checkbox($_POST['month_show_cat_icons']) : 0;
        $new_fields['save_locations'] = (isset($_POST['save_locations'])) ? checkbox($_POST['save_locations']) : 0;
        $new_fields['month_day_format'] = (int)$_POST['month_day_format'];
        $new_fields['month_time_sep'] = $_POST['month_time_sep'];
        $new_fields['default_location'] = $_POST['default_location'];
        $new_fields['cal_show_author'] = (isset($_POST['cal_show_author'])) ? checkbox($_POST['cal_show_author']) : 0;
        try {
            SysConfig::get()->setValue('calendar_default_view', $new_fields['default_view']);
            SysConfig::get()->setValue('calendar_month_show_stime', $new_fields['month_show_stime']);
            SysConfig::get()->setValue('calendar_month_show_cat_icons', $new_fields['month_show_cat_icons']);
            SysConfig::get()->setValue('calendar_month_day_format', $new_fields['month_day_format']);
            SysConfig::get()->setValue('calendar_save_locations', $new_fields['save_locations']);
            SysConfig::get()->setValue('calendar_month_time_sep', $new_fields['month_time_sep']);
            SysConfig::get()->setValue('calendar_default_location', $new_fields['default_location']);
            SysConfig::get()->setValue('calendar_show_author', $new_fields['cal_show_author']);
            echo 'Updated calendar settings.<br />'."\n";
            Log::addMessage('Updated calendar settings');
        } catch (\Exception $ex) {
            Debug::get()->addMessage("Exception: " . $ex->getMessage(), true);
            echo 'Failed to save settings.<br />'."\n";
        }
    }
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
$tab_layout = new Tabs;
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
$start = $_POST['year'].'-'.$_POST['month'].'-01 00:00:00';
$end = $_POST['year'].'-'.$_POST['month'].'-'.cal_days_in_month(CAL_GREGORIAN, $_POST['month'], $_POST['year']).' 23:59:59';
$date_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
	WHERE `start` >= \''.$start.'\'
	AND `start` <= \''.$end.'\'
	ORDER BY `start` ASC,`end` DESC';
$date_handle = $db->sql_query($date_query);
if ($db->sql_num_rows($date_handle) == 0) {
    $tab_content['manage'] .= '<tr><td colspan="6" class="row1">There are no dates in this month.</td></tr>';
    $rowcount = 2;
}
for ($i = 1; $i <= $db->sql_num_rows($date_handle); $i++) {
    $cal = $db->sql_fetch_assoc($date_handle);

    // Format start time and end time
    $starttime = strtotime($cal['start']);
    $endtime = strtotime($cal['end']);

    $tab_content['manage'] .= '<tr class="row'.$rowcount.'">
		<td>'.date('M d, Y', $starttime).'</td>
		<td>'.date(SysConfig::get()->getValue('time_format'), $starttime).'</td>
		<td>'.date(SysConfig::get()->getValue('time_format'), $endtime).'</td>
		<td>'.$cal['header'].'</td>
		<td>'.HTML::link(
    sprintf('admin.php?module=calendar_edit_date&id=%d', $cal['id']),
    HTML::templateImage('edit.png', 'Edit', null, 'width: 16px; height: 16px; border: 0;')
).'</td>
		<td>'.HTML::link(
    sprintf(
        "javascript:confirm_delete('admin.php?module=calendar&action=delete&date_del=%d&month=%d&year=%d')",
        $cal['id'], date('m', strtotime($cal['start'])), date('Y', strtotime($cal['start']))
    ),
    HTML::templateImage('delete.png', 'Delete', null, 'width: 16px; height: 16px; border: 0;')
).
    '</td></tr>';
    if ($rowcount == 1) {
        $rowcount = 2;
    } else {
        $rowcount = 1;
    }
}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Events', $tab_content['manage']);

// ----------------------------------------------------------------------------

$form_create = new Form;
$form_create->set_target('admin.php?module=calendar&action=new');
$form_create->set_method('post');
$form_create->add_hidden('author', $_SESSION['name']);
$form_create->add_textbox('title', 'Heading*', $_POST['title']);
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
    $new_location = SysConfig::get()->getValue('calendar_default_location');
} else {
    $new_location = addslashes($_POST['location']);
}
$form_create->add_select('category', 'Category', $category_ids, $category_names, $category, null, 'Hide', $_POST['category_check']);
$form_create->add_textbox('stime', 'Start Time*', $_POST['stime'], 'onChange="validate_form_field(\'calendar\',\'time\',\'_stime\')"');
$form_create->add_textbox('etime', 'End Time*', $_POST['etime'], 'onChange="validate_form_field(\'calendar\',\'time\',\'_etime\')"');
$form_create->add_date_cal('date', 'Date', $_POST['date'], 'onChange="validate_form_field(\'calendar\',\'date\',\'_date\')"');
$form_create->add_textarea('content', 'Description', $_POST['content']);
$form_create->add_textbox('location', 'Location', $new_location, null, 'Hide', $_POST['location_check']);
$form_create->add_icon_list('image', 'Image', 'newsicons', $image);
$form_create->add_checkbox('hide', 'Hidden', $hide);
$form_create->add_submit('submit', 'Create Event');
$tab_content['create'] = $form_create;
$tab_layout->add_tab('Create Event', $tab_content['create']);

// ----------------------------------------------------------------------------

if (acl::get()->check_permission('calendar_settings')) {
    $tab_content['settings'] = '<h1>Calendar Settings</h1>';
    $settings_form = new Form;
    $settings_form->set_method('post');
    $settings_form->set_target('?module=calendar&action=save_settings');
    $settings_form->add_select('default_view', 'Default View', array('month','day'), array('Current Month','Current Day'), SysConfig::get()->getValue('calendar_default_view'));
    $settings_form->add_checkbox('month_show_stime', 'Show Start Time on Month Calendar', SysConfig::get()->getValue('calendar_month_show_stime'));
    $settings_form->add_checkbox('month_show_cat_icons', 'Show Category Icons on Month Calendar', SysConfig::get()->getValue('calendar_month_show_cat_icons'));
    $settings_form->add_select('month_time_sep', 'Start Time Separator', array(' ','-',' - '), array('1:00pm Event','1:00pm-Event','1:00pm - Event'), SysConfig::get()->getValue('calendar_month_time_sep'));
    $settings_form->add_select('month_day_format', 'Label Days on Month Calendar as', array(1,2), array('Full Name (eg. Thursday)','Abbreviation (eg. Thurs)'), SysConfig::get()->getValue('calendar_month_day_format'));
    $settings_form->add_checkbox('save_locations', 'Save Location Entries', SysConfig::get()->getValue('calendar_save_locations'));
    $settings_form->add_textbox('default_location', 'Default Event Location', SysConfig::get()->getValue('calendar_default_location'), '');
    $settings_form->add_checkbox('cal_show_author', 'Show Event Author', SysConfig::get()->getValue('calendar_show_author'));
    $settings_form->add_submit('submit', 'Save Changes');
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
			width="10px" height="10px" alt="'.$cat['colour'].'" /> '.HTML::schars($cat['label']).'<br />';
    }

    $tab_content['settings'] .= '</td></tr>
	<tr><td width="150" class="row1">&nbsp;</td><td class="row1">
	<input type="submit" value="Delete" /></td></tr>
	</table>
	</form>';

    // ----------------------------------------------------------------------------

    $tab_content['settings'] .= '<h1>Bulk Delete</h1>'."\n";
    $current_year = date('Y');
    $old_year = $current_year - 3;
    $old_timestamp = mktime(23, 59, 59, 12, 31, $old_year);
    $num_old_entries = CalEvent::count(0, $old_timestamp);
    if ($num_old_entries == 0) {
        $button_label = "No old entries ($old_year and previous)";
    } else {
        $button_label = "Delete $num_old_entries old entries ($old_year and previous)";
    }
    $button_disabled = ($num_old_entries === 0) ? 'disabled' : null;
    $tab_content['settings'] .= '<form method="POST" action="?module=calendar&amp;action=delete_old_entries">
	<input type="submit" value="'.$button_label.'" '.$button_disabled.' />
	</form>';
    $tab_layout->add_tab('Settings', $tab_content['settings']);
}

echo $tab_layout;
