<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_poll_results')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = '<h1>Poll Results</h1>';
$question_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
	WHERE question_id = '.addslashes($_GET['id']).' LIMIT 1';
$question_handle = $db->sql_query($question_query);
if ($db->error[$question_handle] === 0) {
	if ($db->sql_num_rows($question_handle) == 0) {
		$content .= 'The selected poll could not be found.';
	} else {
		$question = $db->sql_fetch_assoc($question_handle);
		$content .= '<h2>'.$question['question'].'</h2>
			<em>('.$question['short_name'].')</em><br /><br /><br />';
		unset($question);
		$answer_query = 'SELECT answer_id,answer FROM ' . POLL_ANSWER_TABLE . '
			WHERE question_id = '.addslashes($_GET['id']).' ORDER BY answer_id ASC';
		$answer_handle = $db->sql_query($answer_query);
		if ($db->error[$answer_handle] === 0) {
			if ($db->sql_num_rows($answer_handle) == 0) {
				$content .= 'There are no possible answers to this poll question.<br />
				<a href="admin.php?module=poll_manager&action=del&id='.addslashes($_GET['id']).'">Delete question?</a>';
			} else {
				$i = 1;
				$content .= '<table>';
				while ($i <= $db->sql_num_rows($answer_handle)) {
					$answer = $db->sql_fetch_assoc($answer_handle);
					$responses_query = 'SELECT * FROM ' . POLL_RESPONSE_TABLE . '
						WHERE answer_id = '.$answer['answer_id'];
					$response_handle = $db->sql_query($responses_query);
					// TODO: Add graphical results display.
					if ($db->error[$response_handle] === 1) {
						$num_rows = 0;
						$content .= 'Could not read responses from database.';
					} else {
						$num_rows = $db->sql_num_rows($response_handle);
					}
					$content .= '<tr><td>'.$answer['answer'].'</td><td>'.$num_rows.'</td></tr>';
					unset($num_rows);
					unset($response_handle);
					$i++;
				}
				$content .= '</table>';
				$content .= '<a href="admin.php?module=poll_manager&action=del&id='.(int)$_GET['id'].'">Delete question?</a>';
			}
		} else {
			$content .= 'Could not search for the possible answers for the requested poll question.';
		}
	}
} else {
	$content .= 'Could not search for the requested poll question.';
}
?>