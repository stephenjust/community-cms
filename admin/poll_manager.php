<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
		$message = NULL;
	if ($_GET['action'] == 'del') {
		$message .= 'Are you sure you want to really delete this poll, all related poll answer choices, and respones?<br />';
		$message .= '<form method="post" action="admin.php?module=poll_manager&action=really_delete">
<input type="hidden" name="question_id" value="'.addslashes($_GET['id']).'" />
<input type="submit" value="Delete Poll" /></form>';
		} elseif ($_GET['action'] == 'really_delete') {
		$delete_responses_query = 'DELETE FROM '.$CONFIG['db_prefix'].'poll_responses WHERE question_id = '.$_POST['question_id'];
		$delete_answers_query = 'DELETE FROM '.$CONFIG['db_prefix'].'poll_answers WHERE question_id = '.$_POST['question_id'];
		$delete_question_query = 'DELETE FROM '.$CONFIG['db_prefix'].'poll_questions WHERE question_id = '.$_POST['question_id'].' LIMIT 1';
		$delete_responses_handle = $db->query($delete_responses_query);
		if($delete_responses_handle) {
			$num_deleted_respones = mysqli_affected_rows($db);
			$delete_answers_handle = $db->query($delete_answers_query);
			if($delete_answers_handle) {
				$num_deleted_answers = mysqli_affected_rows($db);
				$delete_question_handle = $db->query($delete_question_query);
				if($delete_question_handle) {
					$message .= 'Deleted '.$num_deleted_respones.' poll respones, '.$num_deleted_answers.' poll answer choices, and the poll question.<br />';
					}
				}
			}
		}
	$content = $message;
	$content .= '<h1>Poll Manager</h1>
<table style="border: 1px solid #000000;">
<tr><td>ID</td><td width="350">Question:</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	// Get page list in the order defined in the database. First is 0.
	$question_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions ORDER BY question_id ASC';
	$question_list_handle = $db->query($question_list_query);
 	$i = 1;
	while ($i <= $question_list_handle->num_rows) {
		$question_list = $question_list_handle->fetch_assoc();
		$content .= '<tr>
<td>'.$question_list['question_id'].'</td>
<td class="adm_page_list_item">'.stripslashes($question_list['question']).'</td>
<td><a href="?module=poll_manager&action=del&id='.$question_list['question_id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=poll_edit&id='.$question_list['question_id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>';
		$i++;
	}
$content .= '</table>';
?>