<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

acl::get()->require_permission('adm_calendar');

switch (FormUtil::get('action')) {
default:

    break;

case 'new':
    try {
        CalEvent::create(
            FormUtil::post('title'),
            FormUtil::post('content', FILTER_UNSAFE_RAW),
            FormUtil::post('author'),
            FormUtil::post('stime'),
            FormUtil::post('etime'),
            FormUtil::post('date'),
            FormUtil::post('category'),
            FormUtil::postCheckbox('category_check'),
            FormUtil::post('location'),
            FormUtil::postCheckbox('location_check'),
            FormUtil::post('image'),
            FormUtil::postCheckbox('hide')
        );
        echo 'Successfully created event.<br />';
    }
    catch (CalEventException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }

    // Parse the event date so that 'manage' tab can default to the
    // correct place
    $event_date_parts = explode('/', FormUtil::post('date'));
    $year = isset($event_date_parts[2]) ? $event_date_parts[2] : date('Y');
    $month = isset($event_date_parts[0]) ? $event_date_parts[0] : date('m');

    break;

// ----------------------------------------------------------------------------

case 'delete':
    try {
        $ev = new CalEvent(FormUtil::get('date_del'));
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
case 'delete_range':
    try {
        $start_date = StringUtils::parseDate(FormUtil::post('start_date'));
        $end_date   = StringUtils::parseDate(FormUtil::post('end_date'));

        if (FormUtil::post('start_date') != "" && $start_date == 0) {
            throw new CalEventException('Your start date was formatted invalidly. It should be in the format mm/dd/yyyy.');
        }

        if ($end_date == 0) {
            throw new CalEventException('Your end date was formatted invalidly. It should be in the format mm/dd/yyyy.');
        }

        CalEvent::deleteRange($start_date, $end_date);
        echo 'Successfully deleted calendar entries.<br />';
    } catch (CalEventException $ex) {
        echo '<span class="errormessage">'.$ex->getMessage().'</span><br />';
    }
    break;
case 'create_category':
    $cat_name = FormUtil::post('category_name');
    $cat_icon = FormUtil::post('colour');
    try {
        CalCategory::create($cat_name, $cat_icon);
        echo 'Created category '.HTML::schars($cat_name).'.';
    } catch (CalCategoryException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'delete_category':
    try {
        $cat = new CalCategory(FormUtil::post('delete_category_id'));
        $cat->delete();
        echo 'Successfully deleted category entry.<br />';
    } catch (CalCategoryException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;
case 'save_settings':
    if (acl::get()->check_permission('calendar_settings')) {
        try {
            SysConfig::get()->setValue('calendar_default_view', FormUtil::post('default_view', FILTER_DEFAULT, ['month', 'day', 'event']));
            SysConfig::get()->setValue('calendar_month_show_stime', FormUtil::postCheckbox('month_show_stime'));
            SysConfig::get()->setValue('calendar_month_show_cat_icons', FormUtil::postCheckbox('month_show_cat_icons'));
            SysConfig::get()->setValue('calendar_month_day_format', FormUtil::post('month_day_format'));
            SysConfig::get()->setValue('calendar_save_locations', FormUtil::postCheckbox('save_locations'));
            SysConfig::get()->setValue('calendar_month_time_sep', FormUtil::post('month_time_sep'));
            SysConfig::get()->setValue('calendar_default_location', FormUtil::post('default_location'));
            SysConfig::get()->setValue('calendar_show_author', FormUtil::postCheckbox('cal_show_author'));
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

if (!isset($month)) {
    $month = FormUtil::get('month', FILTER_VALIDATE_INT, null,
        FormUtil::post('month', FILTER_VALIDATE_INT, null, date('m')));
}
if (!isset($year)) {
    $year = FormUtil::get('year', FILTER_VALIDATE_INT, null,
        FormUtil::post('year', FILTER_VALIDATE_INT, null, date('Y')));
}

$tab_layout = new Tabs;
$tab_content['manage'] = '<form method="post" action="?module=calendar"><select name="month">';
$months = array('January','February','March','April','May','June','July',
    'August','September','October','November','December');
$monthcount = 1; 
while ($monthcount <= 12) {
    if ($month == $monthcount) {
        $tab_content['manage'] .= "<option value='".$monthcount."' selected >"
        .$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
        $monthcount++;
    } else {
        $tab_content['manage'] .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
        $monthcount++;
    }
}
$tab_content['manage'] .= '</select><input type="text" name="year" maxlength="4" size="4" value="'.$year.'" /><input type="submit" value="Change" /></form>';

$start = gmmktime(0, 0, 0, $month, 1, $year);
$end = gmmktime(23, 59, 29, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year);
$events = CalEvent::getRange($start, $end);

$event_rows = array();
foreach ($events as $event) {
    $event_rows[] = [
        date('M d, Y', $event->getStart()),
        date(SysConfig::get()->getValue('time_format'), $event->getStart()),
        date(SysConfig::get()->getValue('time_format'), $event->getEnd()),
        $event->getTitle(),
        HTML::link(
            sprintf('admin.php?module=calendar_edit_date&id=%d', $event->getId()),
            HTML::templateImage('edit.png', 'Edit', null, 'width: 16px; height: 16px; border: 0;')
        ),
        HTML::link(
            sprintf(
                "javascript:confirm_delete('admin.php?module=calendar&action=delete&date_del=%d&month=%d&year=%d')",
                $event->getId(), date('m', $event->getStart()), date('Y', $event->getStart())
            ),
            HTML::templateImage('delete.png', 'Delete', null, 'width: 16px; height: 16px; border: 0;')
        )
    ];
}

$tab_content['manage'] .= TableComponent::create(["Date", "Start Time", "End Time", "Heading", "", ""], $event_rows);

$tab_layout->add_tab('Manage Events', $tab_content['manage']);

// ----------------------------------------------------------------------------

$form_create = new Form;
$form_create->set_target('admin.php?module=calendar&action=new');
$form_create->set_method('post');
$form_create->add_hidden('author', $_SESSION['name']);
$form_create->add_textbox('title', 'Heading*', FormUtil::post('title'));

$categories = CalCategory::getAll();
$category_ids = [];
$category_names = [];
foreach ($categories as $c) {
    $category_ids[] = $c->getId();
    $category_names[] = $c->getName();
}

$new_location = FormUtil::post('location', FILTER_DEFAULT, null, SysConfig::get()->getValue('calendar_default_location'));
$form_create->add_select('category', 'Category', $category_ids, $category_names, FormUtil::post('category'), null, 'Hide', FormUtil::postCheckbox('category_check'));
$form_create->add_textbox('stime', 'Start Time*', FormUtil::post('stime'), 'onChange="validate_form_field(\'calendar\',\'time\',\'_stime\')"');
$form_create->add_textbox('etime', 'End Time*', FormUtil::post('etime'), 'onChange="validate_form_field(\'calendar\',\'time\',\'_etime\')"');
$form_create->add_date_cal('date', 'Date', FormUtil::post('date'), 'onChange="validate_form_field(\'calendar\',\'date\',\'_date\')"');
$form_create->add_textarea('content', 'Description', FormUtil::post('content', FILTER_UNSAFE_RAW));
$form_create->add_textbox('location', 'Location', $new_location, null, 'Hide', FormUtil::postCheckbox('location_check'));
$form_create->add_icon_list('image', 'Image', 'newsicons', FormUtil::post('image'));
$form_create->add_checkbox('hide', 'Hidden', FormUtil::postCheckbox('hide'));
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

    $categories = CalCategory::getAll();
    foreach ($categories as $c) {
        $tab_content['settings'] .= '<input type="radio" name="delete_category_id" value="'.$c->getId().'" />
			<img src="./admin/templates/default/images/icon_'.$c->getIcon().'.png"
			width="10px" height="10px" alt="'.$c->getIcon().'" /> '.$c->getName().'<br />';
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
	</form><br />';

    // Delete events in arbitrary date range
    $form_del_range = new Form();
    $form_del_range->set_method("POST");
    $form_del_range->set_target("?module=calendar&amp;action=delete_range");
    $form_del_range->add_text("Leave Start Date empty to delete all entries prior to End Date.");
    $form_del_range->add_date_cal('start_date','Start Date', NULL,'onChange="validate_form_field(\'calendar\',\'start_date\',\'_start_date\')"');
    $form_del_range->add_date_cal('end_date','End Date', NULL,'onChange="validate_form_field(\'calendar\',\'end_date\',\'_end_date\')"');
    $form_del_range->add_submit('del_submit', "Delete Entries");
    $tab_content['settings'] .= $form_del_range;

    $tab_layout->add_tab('Settings', $tab_content['settings']);
}

echo $tab_layout;
