<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	$content = $message;
	$content .= '<h1>Poll Results</h1>';
	$question_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions WHERE question_id = '.addslashes($_GET['id']).' LIMIT 1';
	$question_handle = $db->query($question_query);
	if($question_handle) {
		if($question_handle->num_rows == 0) {
			$content .= 'The selected poll could not be found.';
			} else {
			$question = $question_handle->fetch_assoc();
			$content .= '<h2>'.$question['question'].'</h2>
<em>('.$question['short_name'].')</em><br /><br /><br />';
			unset($question);
			$answer_query = 'SELECT answer_id,answer FROM '.$CONFIG['db_prefix'].'poll_answers WHERE question_id = '.addslashes($_GET['id']).' ORDER BY answer_id ASC';
			$answer_handle = $db->query($answer_query);
			if($answer_handle) {
				if($answer_handle->num_rows == 0) {
					$content .= 'There are no possible answers to this poll question.<br />
<a href="admin.php?module=poll_manager&action=del&id='.addslashes($_GET['id']).'">Delete question?</a>';
					} else {
					$i = 1;
					$content .= '<table>';
					while ($i <= $answer_handle->num_rows) {
						$answer = $answer_handle->fetch_assoc();
						$responses_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_responses WHERE answer_id = '.$answer['answer_id'].' LIMIT 1';
						$response_handle = $db->query($responses_query);
						if(!$response_handle) {
							$num_rows = 'Could not read responses from database.';
							} else {
							$num_rows = $response_handle->num_rows;
							}
						$content .= '<tr><td>'.$answer['answer'].'</td><td>'.$num_rows.'</td></tr>';
						unset($num_rows);
						unset($response_handle);
						$i++;
						}			
					$content .= '</table>';
					$content .= '<a href="admin.php?module=poll_manager&action=del&id='.addslashes($_GET['id']).'">Delete question?</a>';
					}
				} else {
				$content .= 'Could not search for the possible answers for the requested poll question.';
				}
			}
		} else {
		$content .= 'Could not search for the requested poll question.';
		}
?>