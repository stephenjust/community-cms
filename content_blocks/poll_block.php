<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	$poll_block = new block;
	$poll_block->block_id = $block_info['id'];
	$poll_block->get_block_information();
	$return = NULL;
	$poll_questions_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions WHERE question_id = '.$poll_block->attribute['question_id'].' ORDER BY question_id DESC';
	$poll_questions_handle = $db->query($poll_questions_query);
	if(!$poll_questions_handle) {
		$return .= 'Failed to retrieve list of poll questions.<br />'.mysqli_error($db);
		}
	if($poll_questions_handle->num_rows == 0) {
		$return .= '<strong>ERROR:</strong> There is no poll associated with this block.<br />';
		} else {
		$poll_question = $poll_questions_handle->fetch_assoc();
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
		$poll_answers_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_answers WHERE question_id = '.$poll_block->attribute['question_id'].' ORDER BY answer_id ASC';
		$poll_answers_handle = $db->query($poll_answers_query);
		if($poll_answers_handle->num_rows == 0) {
			$poll_template_answers = 'There are no possible answers to the above question.';
			} else {
			$current_answer = 1;
			while($current_answer <= $poll_answers_handle->num_rows) {
				$template_current_answer = clone $template_poll_block_answer;
				$poll_answer = $poll_answers_handle->fetch_assoc();
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
	?>