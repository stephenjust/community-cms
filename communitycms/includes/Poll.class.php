<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Handle poll feature
 *
 * @author Stephen
 */
class Poll {
	private $id;
	private $question;
	private $shortName;
	private $type;
	private $active;

	public function __construct($id) {
		global $db;

		if (!is_numeric($id))
			throw new PollException('An invalid poll was requested.');
		$query = 'SELECT `question`,`short_name`,`type`,`active`
			FROM `'.POLL_QUESTION_TABLE.'`
			WHERE `question_id` = '.$id.'
			LIMIT 1';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1)
			throw new PollException('Failed to load poll information.');
		if ($db->sql_num_rows($handle) === 0)
			throw new PollException('The requested poll does not exist.');
		$result = $db->sql_fetch_assoc($handle);
		
		$this->id = $id;
		$this->question = $result['question'];
		$this->shortName = $result['short_name'];
		$this->type = $result['type'];
		$this->active = $result['active'];
	}
	
	private function genererateUniqId() {
		// FIXME: stub
	}

	/**
	 * Add a user's vote to a poll
	 * @global class $db
	 * @param int $response ID of the answer choice chosen
	 * @param string $ip IP of the user that voted
	 * @return void
	 */
	function vote($response,$ip) {
		$response = (int)$response;
		if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/i',$ip))
			throw new PollException('Invalid IP address.');
		if ($response == 0)
			throw new PollException('Invalid response.');
		$ip = ip2long($ip);

		global $db;
		$query = 'INSERT INTO `'.POLL_RESPONSE_TABLE.'`
			(`question_id`,`answer_id`,`value`,`ip_addr`)
			VALUES
			('.$this->id.','.$response.', NULL, \''.$ip.'\')';
		$handle = $db->sql_query($query);
		if ($db->error[$handle] === 1) {
			throw new PollException('An error occurred while recording your vote.');
		}
	}

	/**
	 * poll_get_results - Fetch an array of poll results
	 * @global db $db Database connection object
	 * @param int $poll_id Poll ID (as in database)
	 * @return array Array of poll information (including results)
	 */
	function getResults() {
		global $db;
		$poll_array = array();
		$poll_array['question'] = NULL;
		$poll_array['answers'] = array();
		$poll_array['responses'] = array();
		$question_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
			WHERE question_id = '.$this->id.' LIMIT 1';
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
			WHERE question_id = '.$this->id.' ORDER BY answer_id ASC';
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
	
	public function delete() {
		global $db;

		$delete_responses_query = 'DELETE FROM `'.POLL_RESPONSE_TABLE.'`
			WHERE `question_id` = '.$this->id;
		$delete_answers_query = 'DELETE FROM `'.POLL_ANSWER_TABLE.'`
			WHERE `question_id` = '.$this->id;
		$delete_question_query = 'DELETE FROM `'.POLL_QUESTION_TABLE.'`
			WHERE `question_id` = '.$this->id;
		$delete_responses_handle = $db->sql_query($delete_responses_query);
		if ($db->error[$delete_responses_handle] === 1)
			throw new PollException('Failed to delete poll responses.');
		$num_deleted_respones = $db->sql_affected_rows($delete_responses_handle);
		$delete_answers_handle = $db->sql_query($delete_answers_query);
		if ($db->error[$delete_answers_handle] === 1)
			throw new PollException('Failed to delete poll answer choices.');
		$num_deleted_answers = $db->sql_affected_rows($delete_answers_handle);
		$delete_question_handle = $db->sql_query($delete_question_query);
		if ($db->error[$delete_question_handle] === 1)
			throw new PollException('Failed to delete poll question.');
		Log::addMessage('Deleted poll question, answers and responses for poll \''.$this->question.'\'');
		return array('responses' => $num_deleted_respones, 'answers' => $num_deleted_answers);
	}
}

?>
