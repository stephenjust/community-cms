<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	$date = date('Y-m-d H:i:s');
	if ($_GET['action'] == 'new') {
		$title = addslashes($_POST['title']);
	  $image = $_POST['image'];
	  if(!isset($image)) $image = 'NULL';
	  $content = addslashes($_POST['content']);
	  $author = addslashes($_POST['author']);
		$start_time = $_POST['stime'];
		$end_time = $_POST['etime'];
		$year = $_POST['year'];
		$month = $_POST['month'];
		$day = $_POST['day'];
		$category = $_POST['category'];
	  $location = $_POST['location'];
		if($_POST['hide'] == "on") {
			$hide = '1'; 
			} else {
			$hide = '0';
			}
	  if($start_time == "" || $end_time == "" || $year == "" || $title == "") {
	  	$message .= 'One or more fields was not filled out. Please complete the fields marked with a star and resubmit.';
	  	} else {
			$stime = explode('-',$start_time);
			$etime = explode('-',$end_time);
			if (!eregi('^[0-2][0-9]\:[0-5][0-9]$',$start_time) || !eregi('^[0-2][0-9]\:[0-5][0-9]$',$end_time) || strlen($start_time) != 5 || strlen($end_time) != 5 || $start_time > $end_time ) {
				$message .= "You did not fill out one or more of the times properly. Please fix the problem and resubmit.";
				} else {
				$create_date_query = 'INSERT INTO '.$CONFIG['db_prefix'].'calendar (category,starttime,endtime,year,month,day,header,description,location,author,image,hidden) VALUES ("'.$category.'","'.$start_time.'","'.$end_time.'",
				"'.$year.'","'.$month.'","'.$day.'","'.$title.'","'.$content.'","'.$location.'","'.$author.'","'.$image.'",'.$hide.')';
				$create_date = $db->query($create_date_query);
				if(!$create_date) {
					$message .= 'Failed to create date information.<br />'.mysqli_error($db).$create_date_query;
					} else {
					$message .= 'Successfully created date information. '.log_action('New date entry on '.$day.'/'.$month.'/'.$year.' \''.$title.'\'');
					}
				}
			}
		}
$content = $message.'<form method="POST" action="?module=calendar_new_date&action=new">
<h1>Create New Calendar Date</h1>
<table class="admintable">
<input type="hidden" name="author" value="'.$_SESSION['name'].'" />
<tr><td width="150" class="row1">*Heading:</td><td class="row1"><input type="text" name="title" value="'.$_POST['title'].'" /></td></tr>
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
		$content .= '<option value="'.$category_list['cat_id'].'" />'.$category_list['label'].'</option>';
		$b++;
	}

$content .= '</select>
</td></tr>
<tr><td width="150" class="row1">*Start Time:</td><td class="row1"><input type="text" name="stime" value="'.$_POST['stime'].'" maxlength="5" /><div class="notice">Times are in 24 hour format. Insert the same time in both fields for an all day event. Times should be in hour:minute format. Please include leading zeroes (1 = 01)</div></td></tr>
<tr><td width="150" class="row2">*End Time:</td><td class="row2"><input type="text" name="etime" value="'.$_POST['etime'].'" maxlength="5" /></td></tr>
<tr><td width="150" class="row1">*Day:</td><td class="row1"><select name="day" value="'.$_POST['day'].'" >';
$daycount = 1; 
while ($daycount <= 31) {
$content .= "<option value='".$daycount."'>".$daycount."</option>";
$daycount++;
}
$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
$content .= '</select></td></tr>
<tr><td width="150" class="row2">*Month:</td><td class="row2"><select name="month" value="'.$_POST['month'].'" >';
$monthcount = 1; 
while ($monthcount <= 12) {
if(date('m') == $monthcount) {
$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
$monthcount++;
} else {
$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
$monthcount++;
}
}
$content .= '</select></td></tr>
<tr><td width="150" class="row1">*Year:</td><td class="row1"><input type="text" name="year" value="'.$_POST['year'].'" maxlength="4" /></td></tr>
<tr><td class="row2" valign="top">Description:</td>
<td class="row2"><textarea name="content" rows="30">'.stripslashes($_POST['content']).'</textarea></td></tr>
<tr><td width="150" class="row1">Location:</td><td class="row1"><input type="text" name="location" value="'.$_POST['location'].'" /></td></tr>
<tr><td width="150" valign="top" class="row2">Image:</td><td class="row2">'.file_list('newsicons',2).'</td></tr>
<tr><td width="150" class="row1">Hidden:</td><td class="row1"><input type="checkbox" name="hide" /></td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
</table>
</form>';
?>