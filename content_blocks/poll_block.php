<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	global $site_info;
	$return = NULL;
	$block_attribute_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$block_id.' LIMIT 1';
	$block_attribute_handle = $db->query($block_attribute_query);
	$block = $block_attribute_handle->fetch_assoc();
	$block_attribute_temp = $block['attributes'];
	$block_attribute_temp = explode("\n",$block_attribute_temp);
	$block_attribute_count = count($block_attribute_temp);
	$i = 0;
	while($i < $block_attribute_count) {
		$attribute_temp = explode('=',$block_attribute_temp[$i]);
		$block_attribute[$attribute_temp[0]] = $attribute_temp[1];
		$i++;
		}
	$poll_questions_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_questions WHERE question_id = '.$block_attribute['question_id'].' ORDER BY question_id DESC';
	$poll_questions_handle = $db->query($poll_questions_query);
	if(!$poll_questions_handle) {
		$return .= 'Failed to retrieve list of poll questions.<br />'.mysqli_error($db);
		}
	if($poll_questions_handle->num_rows == 0) {
		$return .= 'There are no poll questions to view.<br />';
		} else {
		$poll_question = $poll_questions_handle->fetch_assoc();
		}
	$poll_template = load_template_file('mini_poll.html');
	$poll_template['contents'] = str_replace('<!-- $POLL_QUESTION$ -->',$poll_question['question'],$poll_template['contents']);
	$sep = array('<!-- $POLL_ANSWER_START$ -->','<!-- $POLL_ANSWER_END$ -->');
	$poll_template['contents'] = str_replace($sep,'<NEWLINE>',$poll_template['contents']);
	$poll_template = explode('<NEWLINE>',$poll_template['contents']);
	$question_num = 1;
	$poll_template_answers = NULL;
	$poll_answers_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'poll_answers WHERE question_id = '.$block_attribute['question_id'];
	$poll_answers_handle = $db->query($poll_answers_query);
	if($poll_answers_handle->num_rows == 0) {
		$poll_template_answers = 'There are no possible answers to the above question.';
		} else {
		$current_answer = 1;
		while($current_answer <= $poll_answers_handle->num_rows) {
			$poll_template_answer = $poll_template[1];
			$poll_answer = $poll_answers_handle->fetch_assoc();
			$poll_template_answer = str_replace('<!-- $POLL_ANSWER_TEXT$ -->',$poll_answer['answer'],$poll_template_answer);
			$poll_template_answer = str_replace('<!-- $POLL_ANSWER_ID$ -->',$poll_answer['answer_id'],$poll_template_answer);
			$poll_template_answers .= $poll_template_answer;
			$current_answer++;
			}
		}
	$return .= $poll_template[0].$poll_template_answers.$poll_template[2];
	$return = str_replace('<!-- $POLL_SHORT_NAME$ -->',$poll_question['short_name'],$return);
	$return = str_replace('<!-- $POLL_ID$ -->',$poll_question['question_id'],$return);
	return $return;
	?>