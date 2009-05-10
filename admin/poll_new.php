<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
if ($_GET['action'] == 'new') {
	$question = addslashes($_POST['question']);
	$short_name = addslashes($_POST['short_name']);
	$answers = addslashes($_POST['answers']);
	$answer_array = explode("\n",$answers);
	$num_answers = count($answer_array);
	if ($num_answers < 2) {
		$message .= 'Not enough answer choices';
	} else {
		$i = 1;
		$new_question_query = 'INSERT INTO ' . POLL_QUESTION_TABLE . '
			(question,short_name) VALUES ("'.$question.'","'.$short_name.'")';
		$new_question_handle = $db->sql_query($new_question_query);
		if ($db->error[$new_question_handle] === 0) {
			$question_check_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
				ORDER BY question_id DESC LIMIT 1';
			$question_check_handle = $db->sql_query($question_check_query);
			$question_check = $db->sql_fetch_assoc($question_check_handle);
			while ($i <= $num_answers) {
				$current_answer = $answer_array[$i - 1];
				if (strlen($current_answer) > 0) {
					$new_answer_query = 'INSERT INTO ' . POLL_ANSWER_TABLE . '
						(question_id,answer,answer_order) VALUES ('.$question_check['question_id'].',"'.$current_answer.'",'.$i.')';
					$new_answer_handle = $db->sql_query($new_answer_query);
					if ($db->error[$new_answer_handle] === 1) {
						$content .= 'Failed to create poll answer.<br />';
					}
				}
				$i++;
			}
			$content .= 'Created poll. '.log_action('Created poll question \''.$question.'\'');
		} else {
			$content .= 'Failed to create poll question.';
		}
	}
}
$tab_layout = new tabs;
$form = new form;
$form->set_target('admin.php?module=poll_new&amp;action=new');
$form->set_method('post');
$form->add_hidden('author',$_SESSION['name']);
$form->add_textbox('question', 'Question');
$form->add_textbox('short_name','Unique Identifier');
$form->add_textarea('answers', 'Answers (One per line)', NULL, 'class="mceNoEditor"');
$form->add_submit('submit','Create Poll');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Poll',$tab_content['create']);
$content .= $tab_layout;
?>