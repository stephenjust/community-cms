<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	$date = date('Y-m-d H:i:s');
	if ($_GET['action'] == 'delete') {
		$read_date_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar WHERE id = '.$_POST['date_del'];
		$read_date_info_handle = $db->query($read_date_info_query);
		if(!$read_date_info_handle) {
			$content .= 'Failed to read date information. Does it exist?<br />';
			} else {
	  	$del_query = "DELETE FROM ".$CONFIG['db_prefix']."calendar WHERE id = ".$_POST['date_del'];
	  	$del_handle = $db->query($del_query);
	  	$read_date_info = $read_date_info_handle->fetch_assoc();
	  	if(!$del_query) {
	  		$content .= 'Failed to delete item.<br />';
	  		} else {
	  		$content .= 'Successfully deleted item.<br />'.log_action('Deleted calendar date \''.$read_date_info['header'].'\'');
	  		}
	  	}
		}
	if($_POST['month'] > 12 || $_POST['month'] < 1) {
		$_POST['month'] = date('m');
		}
	if($_POST['year'] < 1 || $_POST['year'] > 9999) {
		$_POST['year'] = date('Y');
		}
	$content .= '<h1>Delete Date</h1>
<form method="post" action="?module=calendar"><select name="month">';
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	$monthcount = 1; 
	while ($monthcount <= 12) {
		if($_POST['month'] == $monthcount) {
			$content .= "<option value='".$monthcount."' selected >".$months[$monthcount-1]."</option>"; // Need [$monthcount-1] as arrays start at 0.
			$monthcount++;
			} else {
			$content .= "<option value='".$monthcount."'>".$months[$monthcount-1]."</option>";
			$monthcount++;
			}
		}
	$content .= '</select><input type="text" name="year" maxlength="4" size="4" value="'.$_POST['year'].'" /><input type="submit" value="Change" /></form>';
	$content .= '<form method="post" action="?module=calendar&action=delete">
<table class="admintable">
<tr><th>&nbsp;</th><th>Date:</th><th>Heading:</th></tr>';
	$rowcount = 1;
	$date_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'calendar WHERE year = '.$_POST['year'].' AND month = '.$_POST['month'].' ORDER BY day,starttime ASC';
	$date_handle = $db->query($date_query);
 	$i = 1;
 	if($date_handle->num_rows == 0) {
 		$content .= '<tr><td colspan="3" class="row1">There are no dates in this month.</td></tr>';
 		$rowcount = 2;
 		}
	while ($i <= $date_handle->num_rows) {
		$cal = $date_handle->fetch_assoc();
		$cal_time = mktime(0,0,0,$cal['month'],$cal['day'],$cal['year']);
		$content .= '<tr><td class="row'.$rowcount.'"><input type="radio" name="date_del" value="'.$cal['id'].'" /></td><td class="row'.$rowcount.'">'.date('M d, Y',$cal_time).'</td><td class="row'.$rowcount.'">'.stripslashes($cal['header']).'</tr>';
		$i++;
		if($rowcount == 1) {
			$rowcount = 2;
			} else {
			$rowcount = 1;
			}
		}
	$content .= '<tr><td class="row'.$rowcount.'">&nbsp;</td><td colspan="2" class="row'.$rowcount.'"><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>