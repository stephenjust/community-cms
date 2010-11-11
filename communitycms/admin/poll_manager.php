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

if (!$acl->check_permission('adm_poll_manager')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;
$tab_layout = new tabs;

switch ($_GET['action']) {
	default:
		break;
	case 'new':
		if (!$acl->check_permission('poll_create')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to create a new poll.</span><br />'."\n";
		}
		$question = addslashes($_POST['question']);
		$short_name = addslashes($_POST['short_name']);
		$answers = addslashes($_POST['answers']);
		$answer_array = explode("\n",$answers);
		$num_answers = count($answer_array);
		if ($num_answers < 2) {
			$content .= '<span class="errormessage">Not enough answer choices.</span><br />'."\n";
			break;
		}
		$i = 1;
		$new_question_query = 'INSERT INTO ' . POLL_QUESTION_TABLE . "
			(question,short_name) VALUES ('$question','$short_name')";
		$new_question_handle = $db->sql_query($new_question_query);
		if ($db->error[$new_question_handle] === 1) {
			$content .= '<span class="errormessage">Failed to create poll question.</span><br />'."\n";
			break;
		}
		$question_check_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
			ORDER BY question_id DESC LIMIT 1';
		$question_check_handle = $db->sql_query($question_check_query);
		$question_check = $db->sql_fetch_assoc($question_check_handle);
		while ($i <= $num_answers) {
			$current_answer = $answer_array[$i - 1];
			if (strlen($current_answer) > 0) {
				$new_answer_query = 'INSERT INTO ' . POLL_ANSWER_TABLE . '
					(question_id,answer,answer_order) VALUES ('.$question_check['question_id'].',\''.$current_answer.'\','.$i.')';
				$new_answer_handle = $db->sql_query($new_answer_query);
				if ($db->error[$new_answer_handle] === 1) {
					$content .= '<span class="errormessage">Failed to create poll answer.</span><br />'."\n";
				}
			}
			$i++;
		}
		$content .= 'Created poll.<br />'."\n";
		$log->new_message('Created poll question \''.$question.'\'');
		break;
}
if ($_GET['action'] == 'del') {
	$content .= 'Are you sure you want to really delete this poll, all related poll answer choices, and respones?<br />';
	$content .= '<form method="post" action="admin.php?module=poll_manager&action=really_delete">
		<input type="hidden" name="question_id" value="'.addslashes($_GET['id']).'" />
		<input type="submit" value="Delete Poll" /></form>';
} elseif ($_GET['action'] == 'really_delete') {
	$delete_responses_query = 'DELETE FROM ' . POLL_RESPONSE_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_answers_query = 'DELETE FROM ' . POLL_ANSWER_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_question_query = 'DELETE FROM ' . POLL_QUESTION_TABLE . '
		WHERE question_id = '.(int)$_POST['question_id'];
	$delete_responses_handle = $db->sql_query($delete_responses_query);
	if ($db->error[$delete_responses_handle] === 0) {
		$num_deleted_respones = $db->sql_affected_rows($delete_responses_handle);
		$delete_answers_handle = $db->sql_query($delete_answers_query);
		if ($db->error[$delete_answers_handle] === 0) {
			$num_deleted_answers = $db->sql_affected_rows($delete_answers_handle);
			$delete_question_handle = $db->sql_query($delete_question_query);
			if ($db->error[$delete_question_handle] === 0) {
				$content .= 'Deleted '.$num_deleted_respones.' poll respones, '.$num_deleted_answers.' poll answer choices, and the poll question.<br />'.
				$log->new_message('Deleted poll question, answers and responses for poll ID '.$_POST['question_id']);
			}
		}
	}
}

$tab_content['manage'] = '<table class="admintable">
<tr><th>ID</th><th width="350">Question:</th><th colspan="2">&nbsp;</th></tr>';
// Get page list in the order defined in the database. First is 0.
$question_list_query = 'SELECT * FROM ' . POLL_QUESTION_TABLE . '
	ORDER BY question_id ASC';
$question_list_handle = $db->sql_query($question_list_query);
if ($db->sql_num_rows($question_list_handle) == 0) {
	$tab_content['manage'] .= '<tr class="row1">
		<td colspan="4">No polls exist.</td>
		</tr>';
}
$rowstyle = 'row1';
for ($i = 1; $i <= $db->sql_num_rows($question_list_handle); $i++) {
	$question_list = $db->sql_fetch_assoc($question_list_handle);
	$tab_content['manage'] .= '<tr class="'.$rowstyle.'">
		<td>'.$question_list['question_id'].'</td>
		<td>'.stripslashes($question_list['question']).'</td>
		<td><a href="?module=poll_manager&action=del&id='.$question_list['question_id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=poll_results&id='.$question_list['question_id'].'">Results</a></td>';
	if ($rowstyle == 'row1') {
		$rowstyle = 'row2';
	} else {
		$rowstyle = 'row1';
	}
} // FOR
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Polls',$tab_content['manage']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('poll_create')) {
	$form = new form;
	$form->set_target('admin.php?module=poll_manager&amp;action=new');
	$form->set_method('post');
	$form->add_hidden('author',$_SESSION['name']);
	$form->add_textbox('question', 'Question');
	$form->add_textbox('short_name','Unique Identifier');
	$form->add_textarea('answers', 'Answers (One per line)', NULL, 'class="mceNoEditor"');
	$form->add_submit('submit','Create Poll');
	$tab_content['create'] = $form;
	$tab_layout->add_tab('Create Poll',$tab_content['create']);
}

$content .= $tab_layout;
?>