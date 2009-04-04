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
$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=poll_new&amp;action=new');
$form->set_method('post');
$form->add_hidden('author',$_SESSION['author']);
$form->add_textbox('question', 'Question');
$form->add_textbox('short_name','Unique Identifier');
$form->add_textarea('answers', 'Answers (One per line)', NULL, 'class="mceNoEditor"');
$form->add_submit('submit','Create Poll');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Poll',$tab_content['create']);
$content = $message.$tab_layout;
?>