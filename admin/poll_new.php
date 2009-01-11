<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$message = NULL;
	$content = NULL;
	$date = date('Y-m-d H:i:s');
	if ($_GET['action'] == 'new') {
		$question = addslashes($_POST['question']);
		$short_name = addslashes($_POST['short_name']);
		$answers = addslashes($_POST['answers']);
		$answer_array = explode("\n",$answers);
		$num_answers = count($answer_array);
		if($num_answers < 2) {
			$message .= 'Not enough answer choices';
			} else {
			$i = 1;
			$new_question_query = 'INSERT INTO '.$CONFIG['db_prefix'].'poll_questions (question,short_name) VALUES ("'.$question.'","'.$short_name.'")';
			$new_question_handle = $db->query($new_question_query);
			if($new_question_handle) {
				$question_check_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions ORDER BY question_id DESC LIMIT 1';
				$question_check_handle = $db->query($question_check_query);
				$question_check = $question_check_handle->fetch_assoc();
				while ($i <= $num_answers) {
					$current_answer = $answer_array[$i - 1];
					if(strlen($current_answer) > 0) {
						$new_answer_query = 'INSERT INTO '.$CONFIG['db_prefix'].'poll_answers (question_id,answer,answer_order) VALUES ('.$question_check['question_id'].',"'.$current_answer.'",'.$i.')';
						$new_answer_handle = $db->query($new_answer_query);
						if(!$new_answer_handle) {
							$message .= 'Failed to create poll answer.<br />';
							}
						}
					$i++;
					}
				$message .= 'Created poll. '.log_action('Created poll question \''.$question.'\'');
				} else {
				$message .= 'Failed to create poll question.';
				}
			}
		}
$content = $message.'<form method="POST" action="?module=poll_new&action=new">
<h1>New Poll</h1>
<table class="admintable">
<input type="hidden" name="author" value="'.$_SESSION['name'].'" />
<tr><td width="150" class="row1">Question:</td><td class="row1"><input type="text" name="question" /></td></tr>
<tr><td class="row2">Identifier:</td><td class="row2"><input type="text" name="short_name" value="exampleValue" /></td></tr>
<tr><td width="150" class="row1">Answers:<br />(Put each answer on a separate line)</td><td class="row1">
<textarea name="answers" class="mceNoEditor" rows="6" cols="30"></textarea>
</td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
</table>
</form>';
?>