<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	$date = date('Y-m-d H:i:s');
	if ($_GET['action'] == 'edit') {
		$title = addslashes($_POST['title']);
	  $image = $_POST['image'];
	  if(!isset($image)) $image = 'NULL';
	  $ar_content = addslashes($_POST['content']);
	  $id = $_POST['id'];
	  $author = addslashes($_POST['author']);
		$start_time = $_POST['stime'];
		$end_time = $_POST['etime'];
		$year = $_POST['year'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$category = $_POST['category'];
	  $location = $_POST['location'];
	  $hide = checkbox($_POST['hide']);
	  if($start_time == "" || $end_time == "" || $year == "" || $title == "") {
	  	$content = 'One or more fields was not filled out. Please complete the fields marked with a star and resubmit.';
	  	} else {
			$stime = explode('-',$start_time);
			$etime = explode('-',$end_time);
			if (!eregi('^[0-2][0-9]\:[0-5][0-9]$',$start_time) || !eregi('^[0-2][0-9]\:[0-5][0-9]$',$end_time) || strlen($start_time) != 5 || strlen($end_time) != 5 || $start_time > $end_time ) {
				$content = "You did not fill out one or more of the times properly. Please fix the problem and resubmit.";
				} else {
				$edit_date_query = 'UPDATE '.$CONFIG['db_prefix']."calendar SET 
category='$category',starttime='$start_time',endtime='$end_time',year='$year',month='$month',
day='$day',header='$title',description='$ar_content',location='$location',author='$author',
image='$image',hidden='$hide' WHERE id = $id LIMIT 1";
				$edit_date = $db->query($edit_date_query);
				if(!$edit_date) {
					$content = 'Failed to edit date information.<br />'.mysqli_error($db).$edit_date_query;
					} else {
					$content = 'Successfully edited date information. '.log_action('Edited date entry on '.$day.'/'.$month.'/'.$year.' \''.$title.'\'');
					}
				}
			}
		} else {
			$get_date_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar WHERE id = '.stripslashes($_GET['id']).' LIMIT 1';
			$get_date_handle = $db->query($get_date_query);
			if($get_date_handle->num_rows == 0) {
				$content = 'Could not find the requested calendar entry.';
				} else {
				$date = $get_date_handle->fetch_assoc();
	$content = $message.'<form method="POST" action="?module=calendar_edit_date&action=edit">
<h1>Edit Calendar Date</h1>
<table class="admintable">
<input type="hidden" name="author" value="'.stripslashes($_SESSION['name']).'" />
<input type="hidden" name="id" value="'.stripslashes($_GET['id']).'" />
<tr><td width="150" class="row1">*Heading:</td><td class="row1"><input type="text" name="title" value="'.stripslashes($date['header']).'" /></td></tr>
<tr><td width="150" class="row2">Category:</td><td class="row2">
<select name="category">';
 	$category_list_query = 'SELECT cat_id,label FROM '.$CONFIG['db_prefix'].'calendar_categories ORDER BY cat_id ASC';
 	$category_list_handle = $db->query($category_list_query);
 	if(!$category_list_handle) {
 		$content .= '<option value="error" >'.mysqli_error($db).'</option>';
 		}
 	$b = 1;
	while ($b <= $category_list_handle->num_rows) {
		$category_list = $category_list_handle->fetch_assoc();
		if($date['category'] == $category_list['cat_id']) {
			$content .= '<option value="'.$category_list['cat_id'].'" selected />'.$category_list['label'].'</option>';
			} else {
			$content .= '<option value="'.$category_list['cat_id'].'" />'.$category_list['label'].'</option>';
			}
		$b++;
	}
$temp = explode(':',$date['starttime']);
$starttime = $temp[0].':'.$temp[1];
$temp = explode(':',$date['endtime']);
$endtime = $temp[0].':'.$temp[1];
unset($temp);
$content .= '</select>
</td></tr>
<tr><td width="150" class="row1">*Start Time:</td><td class="row1"><input type="text" name="stime" value="'.$starttime.'" maxlength="5" /><div class="notice">Times are in 24 hour format. Insert the same time in both fields for an all day event. Times should be in hour:minute format. Please include leading zeroes (1 = 01)</div></td></tr>
<tr><td width="150" class="row2">*End Time:</td><td class="row2"><input type="text" name="etime" value="'.$endtime.'" maxlength="5" /></td></tr>
<tr><td width="150" class="row1">*Day:</td><td class="row1"><select name="day">';
for ($daycount = 1; $daycount <= 31; $daycount++) {
	if($daycount == $date['day']) {
		$content .= "<option value='".$daycount."' selected>".$daycount."</option>";
		} else {
		$content .= "<option value='".$daycount."'>".$daycount."</option>";
		}
	}
$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
$content .= '</select></td></tr>
<tr><td width="150" class="row2">*Month:</td><td class="row2"><select name="month" value="'.$date['month'].'" >';
$monthcount = 1; 
while ($monthcount <= 12) {
if($date['month'] == $monthcount) {
$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
$monthcount++;
} else {
$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
$monthcount++;
}
}
if($date['hidden'] == 1) {
	$hidden = 'checked';
	} else {
	$hidden = NULL;
	}
$content .= '</select></td></tr>
<tr><td width="150" class="row1">*Year:</td><td class="row1"><input type="text" name="year" value="'.$date['year'].'" maxlength="4" /></td></tr>
<tr><td class="row2" valign="top">Description:</td>
<td class="row2"><textarea name="content" rows="30">'.stripslashes($date['description']).'</textarea></td></tr>
<tr><td width="150" class="row1">Location:</td><td class="row1"><input type="text" name="location" value="'.stripslashes($date['location']).'" /></td></tr>
<tr><td width="150" valign="top" class="row2">Image:</td><td class="row2">'.file_list('newsicons',2,$date['image']).'</td></tr>
<tr><td width="150" class="row1">Hidden:</td><td class="row1"><input type="checkbox" name="hide" '.$hidden.' /></td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
</table>
</form>';
}
}
?>