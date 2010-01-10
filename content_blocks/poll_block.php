<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

global $acl;
$poll_block = new block;
$poll_block->block_id = $block_info['id'];
$poll_block->get_block_information();
$return = NULL;
$poll_questions_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
	WHERE question_id = '.$poll_block->attribute['question_id'].'
	ORDER BY question_id DESC';
$poll_questions_handle = $db->sql_query($poll_questions_query);
if ($db->error[$poll_questions_handle] === 1) {
	if ($acl->check_permission('show_fe_errors')) {
		$return .= 'Failed to retrieve list of poll questions.<br />';
	} else {
		return NULL;
	}
}
if ($db->sql_num_rows($poll_questions_handle) == 0) {
	if ($acl->check_permission('show_fe_errors')) {
		$return .= '<strong>ERROR:</strong> There is no poll associated with this block.<br />';
	} else {
		return NULL;
	}
} else {
	$poll_question = $db->sql_fetch_assoc($poll_questions_handle);
	$template_poll_block = new template;
	$template_poll_block->load_file('mini_poll');
	$template_poll_block->poll_question = $poll_question['question'];
	$template_poll_block->poll_id = $poll_question['question_id'];
	$template_poll_block->poll_short_name = $poll_question['short_name'];
	$template_poll_block_answer = new template;
	$template_poll_block_answer->path = $template_poll_block->path;
	$template_poll_block_answer->template = $template_poll_block->get_range('poll_answer');
	$question_num = 1;
	$poll_template_answers = NULL;
	$poll_answers_query = 'SELECT * FROM ' . POLL_ANSWER_TABLE . '
		WHERE question_id = '.$poll_block->attribute['question_id'].'
		ORDER BY answer_id ASC';
	$poll_answers_handle = $db->sql_query($poll_answers_query);
	if($db->sql_num_rows($poll_answers_handle) == 0) {
		if ($acl->check_permisson('show_fe_errors')) {
			$poll_template_answers = 'There are no possible answers to the above question.<br />';
		} else {
			return NULL;
		}
	} else {
		$current_answer = 1;
		while($current_answer <= $db->sql_num_rows($poll_answers_handle)) {
			$template_current_answer = clone $template_poll_block_answer;
			$poll_answer = $db->sql_fetch_assoc($poll_answers_handle);
			$template_current_answer->poll_answer_text = $poll_answer['answer'];
			$template_current_answer->poll_answer_id = $poll_answer['answer_id'];
			$poll_template_answers .= $template_current_answer;
			unset($template_current_answer);
			$current_answer++;
			}
		}
	$template_poll_block->replace_range('poll_answer',$poll_template_answers);
	$return .= $template_poll_block;
	}
return $return;
// TODO: Add graphical results display.
?>