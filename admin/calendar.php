<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	$date = date('Y-m-d H:i:s');
if ($_GET['action'] == 'deldate') {
  $del_query = "DELETE FROM ".$CONFIG['db_prefix']."calendar WHERE id = ".$_POST['date_del'];
  if(mysql_query($del_query)) $message = 'Successfully deleted item.';
}

$content = $message.'<form method="POST" action="?module=calendar&action=deldate">
<h1>Delete Date</h1>
<table style="border: 1px solid #000000;">
<tr><td>&nbsp;</td><td>Date:</td><td>Heading:</td></tr>
<tr><td>';
 	$cal = get_row_from_db("calendar");
 	$i = 1;
	while ($i <= $cal['num_rows']) {
		$content .= '<input type="radio" name="date_del" value="'.$cal[$i]['id'].'" /></td><td>Y'.$cal[$i]['year'].'/M'.$cal[$i]['month'].'/D'.$cal[$i]['day'].'</td><td>'.$cal[$i]['header'].'</tr><tr><td>';
		$i++;
	}

$content .= '</td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Delete" /></td></tr>
</table>
</form>';
?>