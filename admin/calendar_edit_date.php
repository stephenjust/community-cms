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

global $acl;

if (!$acl->check_permission('adm_calendar_edit_date')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

/**
 * Include functions necessary for calendar operations
 */
require_once ROOT.'includes/content/CalEvent.class.php';

switch ($_GET['action']) {
case 'edit':
    try {
        // Format date for insertion...
        if (!isset($_POST['category_check'])) { $_POST['category_check'] = null; 
        }
        if (!isset($_POST['location_check'])) { $_POST['location_check'] = null; 
        }
        $event_date = (isset($_POST['date'])) ? $_POST['date'] : date('d/m/Y');
        if (!preg_match('#^[0-1]?[0-9]/[0-3]?[0-9]/[1-2][0-9]{3}$#i', $event_date)) {
            throw new \Exception('Invalid date. Must be formatted DD/MM/YYYY'); 
        }
        $event_date_parts = explode('/', $event_date);
        $year = $event_date_parts[2];
        $month = $event_date_parts[0];
        $day = $event_date_parts[1];
        $start_time = parse_time($_POST['stime']);
        $end_time = parse_time($_POST['etime']);
        $cat_hide = checkbox($_POST['category_check']);
        $loc_hide = checkbox($_POST['location_check']);
        if (!$start_time || !$end_time || $start_time > $end_time) {
            throw new \Exception('You did not fill out one or more of the times properly. Please fix the problem and resubmit.'); 
        }
        // Generate new start/end string
        $start = $year.'-'.$month.'-'.$day.' '.$start_time;
        $end = $year.'-'.$month.'-'.$day.' '.$end_time;
        $hide = (isset($_POST['hide'])) ? (boolean)$_POST['hide'] : false;
        $ev = new CalEvent($_POST['id']);
        $ev->edit(
            $_POST['title'],
            $_POST['content'], $_POST['author'],
            $start, $end, $_POST['category'], $cat_hide,
            $_POST['location'], $loc_hide, $_POST['image'], $hide
        );
        echo 'Successfully edited date information.<br />';
        echo '<a href="?module=calendar&amp;month='.$month.'&amp;year='.$year.'">Back to Event List</a>';
    }
    catch (CalEventException $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }
    break;

// ----------------------------------------------------------------------------

default:
    try {
        $ev = new CalEvent($_GET['id']);
        $form = new form;
        $form->set_method('post');
        $form->set_target('admin.php?module=calendar_edit_date&action=edit');
        $form->add_hidden('author', HTML::schars($_SESSION['name']));
        $form->add_hidden('id', $ev->getId());
        $form->add_textbox('title', '*Heading:', $ev->getTitle());
            
        $cat_list = CalEvent::getCategoryList();
        $category_names = array();
        $category_ids = array();
        foreach ($cat_list AS $cat) {
            $category_names[] = $cat['label'];
            $category_ids[] = $cat['id'];
        }
        $form->add_select(
            'category', 'Category:',
            $category_ids, $category_names, $ev->getCategoryID(), null, 'Hide', $ev->getCategoryHide()
        );

        $form->add_textbox(
            'stime', '*Start Time:',
            date(get_config('time_format'), $ev->getStart()), 'onChange="validate_form_field(\'calendar\',\'time\',\'_stime\')"'
        );
        $form->add_textbox(
            'etime', '*End Time:',
            date(get_config('time_format'), $ev->getEnd()), 'onChange="validate_form_field(\'calendar\',\'time\',\'_etime\')"'
        );
        $form->add_date_cal('date', '*Date:', date('m/d/Y', $ev->getStart()), 'onChange="validate_form_field(\'calendar\',\'date\',\'_date\')"');
        $form->add_textarea('content', 'Description:', $ev->getDescription(), 'rows="25"');
        $form->add_textbox('location', 'Location:', $ev->getLocation(), null, 'Hide', $ev->getLocationHide());
        $form->add_icon_list('image', 'Image:', 'newsicons', $ev->getImage());
        $form->add_checkbox('hide', 'Hidden:', $ev->getHidden());
        $form->add_submit('submit', 'Save Event');
        echo '<h1>Edit Calendar Date</h1>';
        echo $form;
    }
    catch (Exception $e) {
        echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
    }

    break;
}
?>