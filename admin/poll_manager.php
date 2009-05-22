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
if ($_GET['action'] == 'del') {
	$content .= 'Are you sure you want to really delete this poll, all related poll answer choices, and respones?<br />';
	$content .= '<form method="post" action="admin.php?module=poll_manager&action=really_delete">
		<input type="hidden" name="question_id" value="'.addslashes($_GET['id']).'" />
		<input type="submit" value="Delete Poll" /></form>';
} elseif ($_GET['action'] == 'really_delete') {
	$delete_responses_query = 'DELETE FROM ' . POLL_RESPONSE_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_answers_query = 'DELETE FROM ' . POLL_ANSWER_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_question_query = 'DELETE FROM ' . POLL_QUESTION_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_responses_handle = $db->sql_query($delete_responses_query);
	if ($db->error[$delete_responses_handle] === 0) {
		$num_deleted_respones = $db->sql_affected_rows($delete_responses_handle);
		$delete_answers_handle = $db->sql_query($delete_answers_query);
		if ($db->error[$delete_answers_handle] === 0) {
			$num_deleted_answers = $db->sql_affected_rows($delete_answers_handle);
			$delete_question_handle = $db->sql_query($delete_question_query);
			if ($db->error[$delete_question_handle] === 0) {
				$content .= 'Deleted '.$num_deleted_respones.' poll respones, '.$num_deleted_answers.' poll answer choices, and the poll question.<br />'.
				log_action('Deleted poll question, answers and responses for poll ID '.$_POST['question_id']);
			}
		}
	}
}
$content .= '<h1>Poll Manager</h1>
<table class="admintable">
<tr><th>ID</th><th width="350">Question:</th><th colspan="2">&nbsp;</th></tr>';
// Get page list in the order defined in the database. First is 0.
$question_list_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
	ORDER BY question_id ASC';
$question_list_handle = $db->sql_query($question_list_query);
if ($db->sql_num_rows($question_list_handle) == 0) {
	$content .= '<tr class="row1">
		<td colspan="4">No polls exist.</td>
		</tr>';
}
$rowstyle = 'row1';
for ($i = 1; $i <= $db->sql_num_rows($question_list_handle); $i++) {
	$question_list = $db->sql_fetch_assoc($question_list_handle);
	$content .= '<tr class="'.$rowstyle.'">
		<td>'.$question_list['question_id'].'</td>
		<td>'.stripslashes($question_list['question']).'</td>
		<td><a href="?module=poll_manager&action=del&id='.$question_list['question_id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=poll_results&id='.$question_list['question_id'].'">Results</a></td>';
	if ($rowstyle == 'row1') {
		$rowstyle = 'row2';
	} else {
		$rowstyle = 'row1';
	}
} // FOR
$content .= '</table>';
?>