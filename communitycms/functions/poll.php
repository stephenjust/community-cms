<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Add a user's vote to a poll
 * @global class $db
 * @global class $page
 * @param int $question ID of the question that was responded to
 * @param int $response ID of the answer choice chosen
 * @param string $ip IP of the user that voted
 * @return void
 */
function poll_vote($question,$response,$ip) {
    $question = (int)$question;
    $response = (int)$response;
    if (!eregi('^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$',$ip)) {
        return;
    }
    if ($question == 0 || $response == 0) {
        return;
    }
    $ip = ip2long($ip);
    global $db;
    global $page;
    $vote_query = 'INSERT INTO ' . POLL_RESPONSE_TABLE . '
        (question_id ,answer_id ,value ,ip_addr) VALUES ('.$question.',
        '.$response.', NULL, \''.$ip.'\')';
    $vote_handle = $db->sql_query($vote_query);
    if ($db->error[$vote_handle] === 1) {
        $page->notification .= 'Failed to record your vote.<br />';
    } else {
        $page->notification .= 'Thank you for voting.<br />';
    }
}

/**
 * poll_get_results - Fetch an array of poll results
 * @global db $db Database connection object
 * @param int $poll_id Poll ID (as in database)
 * @return array Array of poll information (including results)
 */
function poll_get_results($poll_id) {
	if (!is_int($poll_id)) {
		return false;
	}
	global $db;
	$poll_array = array();
	$poll_array['question'] = NULL;
	$poll_array['answers'] = array();
	$poll_array['responses'] = array();
	$question_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
		WHERE question_id = '.$poll_id.' LIMIT 1';
	$question_handle = $db->sql_query($question_query);
	if ($db->error[$question_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($question_handle) !== 1) {
		return false;
	}
	$poll_question = $db->sql_fetch_assoc($question_handle);
	$poll_array['question'] = $poll_question['question'];
	unset($poll_question);
	unset($question_handle);
	unset($question_query);
	$answer_query = 'SELECT answer_id,answer FROM ' . POLL_ANSWER_TABLE . '
		WHERE question_id = '.$poll_id.' ORDER BY answer_id ASC';
	$answer_handle = $db->sql_query($answer_query);
	if ($db->error[$answer_handle] === 1) {
		return false;
	}
	// If there's no possible answers to the poll, just return it and don't
	// bother looking for responses.
	if ($db->sql_num_rows($answer_handle) === 0) {
		$poll_array['num_answers'] = 0;
		$poll_array['num_responses'] = 0;
		return $poll_array;
	}
	$poll_array['num_answers'] = $db->sql_num_rows($answer_handle);
	for ($i = 0; $i < $db->sql_num_rows($answer_handle); $i++) {
		$answers = $db->sql_fetch_assoc($answer_handle);
		$poll_array['answers'][$i] = array();
		$poll_array['answers'][$i]['id'] = $answers['answer_id'];
		$poll_array['answers'][$i]['answer'] = $answers['answer'];
		$response_query = 'SELECT * FROM ' . POLL_RESPONSE_TABLE . '
			WHERE answer_id = '.$poll_array['answers'][$i]['id'];
		$response_handle = $db->sql_query($response_query);
		// If there's an error when looking for responses, just set the number
		// of responses to 0, and repeat the loop, looking for more results.
		if ($db->error[$response_handle] === 1) {
			$poll_array['answers'][$i]['count'] = 0;
			continue;
		}
		$poll_array['answers'][$i]['count'] = $db->sql_num_rows($response_handle);
		unset($response_handle);
		unset($response_query);
	}
	$poll_array['num_responses'] = $i + 1;
	return $poll_array;
}
?>
