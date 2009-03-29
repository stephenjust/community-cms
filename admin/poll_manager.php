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
					$message .= 'Deleted '.$num_deleted_respones.' poll respones, '.$num_deleted_answers.' poll answer choices, and the poll question.<br />'.
					log_action('Deleted poll question, answers and responses for poll ID '.$_POST['question_id']);
					}
				}
			}
		}
	$content = $message;
	$content .= '<h1>Poll Manager</h1>
<table class="admintable">
<tr><th>ID</th><th width="350">Question:</th><th colspan="2">&nbsp;</th></tr>';
	// Get page list in the order defined in the database. First is 0.
	$question_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions ORDER BY question_id ASC';
	$question_list_handle = $db->query($question_list_query);
	if($question_list_handle->num_rows == 0) {
		$content .= '<tr class="row1">
<td colspan="4">No polls exist.</td>
</tr>';
		}
	$rowstyle = 'row1';
	for ($i = 1; $i <= $question_list_handle->num_rows; $i++) {
		$question_list = $question_list_handle->fetch_assoc();
		$content .= '<tr class="'.$rowstyle.'">
<td>'.$question_list['question_id'].'</td>
<td>'.stripslashes($question_list['question']).'</td>
<td><a href="?module=poll_manager&action=del&id='.$question_list['question_id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=poll_results&id='.$question_list['question_id'].'">Results</a></td>';
		if($rowstyle == 'row1') {
			$rowstyle = 'row2';
			} else {
			$rowstyle = 'row1';
			}
		} // FOR
$content .= '</table>';
?>