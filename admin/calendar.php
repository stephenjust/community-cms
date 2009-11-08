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

$_POST['title'] = (isset($_POST['title'])) ? $_POST['title'] : NULL;
$category = (isset($_POST['category'])) ? $_POST['category'] : NULL;
$_POST['stime'] = (isset($_POST['stime'])) ? $_POST['stime'] : NULL;
$_POST['etime'] = (isset($_POST['etime'])) ? $_POST['etime'] : NULL;
$day = (isset($_POST['day'])) ? (int)$_POST['day'] : NULL;
$month = (isset($_POST['month'])) ? (int)$_POST['month'] : NULL;
$year = (isset($_POST['year'])) ? (int)$_POST['year'] : NULL;
$_POST['content'] = (isset($_POST['content'])) ? $_POST['content'] : NULL;
$_POST['location'] = (isset($_POST['location'])) ? $_POST['location'] : NULL;
$hide = (isset($_POST['hide'])) ? checkbox($_POST['hide']) : 0;
$image = (isset($_POST['image'])) ? $_POST['image'] : NULL;
if ($_GET['action'] == 'new') {
	$title = addslashes($_POST['title']);
	$item_content = addslashes($_POST['content']);
	$author = addslashes($_POST['author']);
	$start_time = $_POST['stime'];
	$end_time = $_POST['etime'];
	$category = $_POST['category'];
	$location = addslashes($_POST['location']);
	if ($start_time == "" || $end_time == "" || $year == "" || $title == "") {
		$content .= 'One or more fields was not filled out. Please complete the fields marked with a star and resubmit.';
	} else {
		$stime = explode('-',$start_time);
		$etime = explode('-',$end_time);
		if (!eregi('^[0-2][0-9]\:[0-5][0-9]$',$start_time) || !eregi('^[0-2][0-9]\:[0-5][0-9]$',$end_time) || strlen($start_time) != 5 || strlen($end_time) != 5 || $start_time > $end_time ) {
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
					.$year.' \''.$title.'\'');
			}
		}
	}
}

// ----------------------------------------------------------------------------

switch ($_GET['action']) {
	default:

		break;
	case 'delete':
		if (delete_date($_POST['date_del'])) {
			$content .= 'Successfully deleted date entry.<br />'."\n";
		} else {
			$content .= 'Failed to delete date entry.<br />'."\n";
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
$form_create->add_textbox('stime','Start Time*',$_POST['stime'],'maxlength="5"');
$form_create->add_textbox('etime','End Time*',$_POST['etime'],'maxlength="5"');
$form_create->add_text('Times are in 24 hour format. Insert the same time in both
    fields for an all day event. Times should be in hour:minute format. Please
    include leading zeroes (1 = 01)');
$form_create->add_select('day','Day*',array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,
    16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,21),array(1,2,3,4,5,6,7,8,9,10,
    11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31),$day);
$form_create->add_select('month','Month*',array(1,2,3,4,5,6,7,8,9,10,11,12),
    array('January','February','March','April','May','June','July','August',
    'September','October','November','December'),$_POST['month']);
$form_create->add_textbox('year','Year*',$_POST['year'],'maxlength="4"');
$form_create->add_textarea('content','Description',stripslashes($_POST['content']));
$form_create->add_textbox('location','Location',stripslashes($_POST['location']));
$form_create->add_icon_list('image','Image','newsicons',$image);
$form_create->add_checkbox('hide','Hidden',$hide);
$form_create->add_submit('submit','Create Event');
$tab_content['create'] = $form_create;
$tab_layout->add_tab('Create Event',$tab_content['create']);

// ----------------------------------------------------------------------------

$tab_content['settings'] = '<form method="POST" action="?module=calendar&action=create_category">
<h1>Create New Category</h1>
<table class="admintable">
<tr><td width="150" class="row1">Name:</td><td class="row1"><input type=\'text\' name=\'category_name\' /></td></tr>
<tr><td width="150" class="row2">Colour:</td><td class="row2">
<input type="radio" name="colour" value="red" /><img src="./admin/templates/default/images/icon_red.png" width="16px" height="16px" alt="Red" />
<input type="radio" name="colour" value="green" /><img src="./admin/templates/default/images/icon_green.png" width="16px" height="16px" alt="Green" />
<input type="radio" name="colour" value="blue" /><img src="./admin/templates/default/images/icon_blue.png" width="16px" height="16px" alt="Blue" /><br />
<input type="radio" name="colour" value="purple" /><img src="./admin/templates/default/images/icon_purple.png" width="16px" height="16px" alt="Purple" />
<input type="radio" name="colour" value="cyan" /><img src="./admin/templates/default/images/icon_cyan.png" width="16px" height="16px" alt="Cyan" />
<input type="radio" name="colour" value="yellow" /><img src="./admin/templates/default/images/icon_yellow.png" width="16px" height="16px" alt="Yellow" />
</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type="submit" value="Create" /></td></tr>
</table>
</form>

<form method="POST" action="?module=calendar&action=delete_category">
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
		width="16px" height="16px" alt="'.$cat['colour'].'" />'.stripslashes($cat['label']).'<br />';
}

$tab_content['settings'] .= '</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1">
<input type="submit" value="Delete" /></td></tr>
</table>
</form>';
$tab_layout->add_tab('Settings',$tab_content['settings']);
$content .= $tab_layout;
?>