<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * get_block - Get contents of a block
 * @global acl $acl
 * @global db $db
 * @param int $block_id ID of block to display
 * @return string
 */
function get_block($block_id = NULL) {
	$block_id = (int)$block_id;
	if(strlen($block_id) < 1 || $block_id <= 0) {
		return;
	}
	global $acl;
	global $db;
	$block_content = NULL;
	$block_query = 'SELECT * FROM ' . BLOCK_TABLE . '
		WHERE id = '.$block_id.' LIMIT 1';
	$block_handle = $db->sql_query($block_query);
	if ($db->error[$block_handle] === 0) {
		if ($db->sql_num_rows($block_handle) == 1) {
			$block_info = $db->sql_fetch_assoc($block_handle);
			$block_content .= include(ROOT.'content_blocks/'.$block_info['type'].'_block.php');
		} else {
			if ($acl->check_permission('show_fe_errors')) {
				$block_content .= '<div class="notification"><strong>Error:</strong> Could not load block '.$block_id.'.</div>';
			}
		}
	}
	return $block_content;
}

/**
 * delete_block - Delete a block
 * @global object $acl Permission object
 * @global db $db Database connection object
 * @global Debug $debug Debug object
 * @global log $log Logger object
 * @param integer $id Block ID
 * @return string Response message
 */
function delete_block($id) {
	global $acl;
	global $db;
	global $debug;
	global $log;
	$message = NULL;

	if (!$acl->check_permission('block_delete')) {
		$message = '<span class="errormessage">You do not have the necessary permissions to delete a block.</span><br />';
		return $message;
	}

	// Check data types
	if (!is_numeric($id)) {
		$message .= 'Malformed block ID provided.<br />'."\n";
		return $message;
	}
	$block_exists_query = 'SELECT * FROM `' . BLOCK_TABLE . '`
		WHERE `id` = '.$id.' LIMIT 1';
	$block_exists_handle = $db->sql_query($block_exists_query);
	if($db->error[$block_exists_handle] === 1) {
		$message .= 'Failed to read block information.<br />'."\n";
	} else {
		if ($db->sql_num_rows($block_exists_handle) == 1) {
			$delete_block_query = 'DELETE FROM `' . BLOCK_TABLE . '`
				WHERE `id` = '.$id;
			$delete_block = $db->sql_query($delete_block_query);
			if (!$db->error[$delete_block] === 1) {
				$message .= 'Failed to delete block.<br />'."\n";
			} else {
				$block_exists = $db->sql_fetch_assoc($block_exists_handle);
				$log->new_message('Deleted block \''.$block_exists['type'].' ('.$block_exists['attributes'].')\'');
				$message .= 'Successfully deleted block.<br />'."\n";
			}
		} else {
			$message .= 'Could not find the block you are trying to delete.<br />'."\n";
		}
		return $message;
	}
}

/**
 * Generate the form for block management
 * @global db $db
 * @global Debug $debug
 * @param string $type Block type
 * @param array $vars Array of parameters to set as form defaults
 * @return string HTML for form (or false on failure)
 */
function block_edit_form($type,$vars = array()) {
	global $db;
	global $debug;

	$return = NULL;
	if (!is_array($vars)) {
		$debug->addMessage('Invalid set of variables',true);
		return false;
	}
	switch ($type) {
		default:
			break;
		case 'text':
			if (!isset($vars['article_id'])) {
				$vars['article_id'] = 0;
			}
			if (!isset($vars['show_border'])) {
				$vars['show_border'] = 'yes';
			}
			$news_query = 'SELECT `news`.`name`, `news`.`id`, `news`.`page`, `page`.`title`
				FROM `'.NEWS_TABLE.'` `news`
				LEFT JOIN `'.PAGE_TABLE.'` `page`
				ON `news`.`page` = `page`.`id`
				ORDER BY `news`.`page` ASC, `news`.`name` ASC';
			$news_handle = $db->sql_query($news_query);
			if ($db->error[$news_handle] === 1) {
				$debug->addMessage('Failed to read news articles',true);
				return false;
			}
			$num_articles = $db->sql_num_rows($news_handle);
			if ($num_articles == 0) {
				return 'No articles exist.<br />'."\n";
			}
			$return .= 'News Article <select name="article_id">'."\n";
			for ($i = 1; $i <= $num_articles; $i++) {
				$news_result = $db->sql_fetch_assoc($news_handle);
				if ($news_result['title'] == NULL && $news_result['page'] == 0) {
					$news_result['title'] = 'No Page';
				} elseif ($news_result['title'] == NULL && $news_result['page'] != 0) {
					$news_result['title'] = 'Unknown Page';
				}
				if ($vars['article_id'] == $news_result['id']) {
					$return .= "\t".'<option value="'.$news_result['id'].'" selected>'.$news_result['title'].' - '.$news_result['name'].'</option>'."\n";
				} else {
					$return .= "\t".'<option value="'.$news_result['id'].'">'.$news_result['title'].' - '.$news_result['name'].'</option>'."\n";
				}
			}
			$return .= '</select><br />'."\n";
			$return .= 'Show Border <select name="show_border">'."\n";
			if ($vars['show_border'] == 'yes') {
				$return .= "\t".'<option value="yes" selected>Yes</option>'."\n".
					"\t".'<option value="no">No</option>'."\n";
			} else {
				$return .= "\t".'<option value="yes">Yes</option>'."\n".
					"\t".'<option value="no" selected>No</option>'."\n";
			}
			$return .= '</select><br />'."\n";
			$return .= '<input type="hidden" name="attributes" value="article_id,show_border" />';
			break;

// ----------------------------------------------------------------------------

		case 'poll':
			if (!isset($vars['question_id'])) {
				$vars['question_id'] = 0;
			}
			$poll_query = 'SELECT `question_id`,`question` FROM `'.POLL_QUESTION_TABLE.'`
				ORDER BY `question` ASC';
			$poll_handle = $db->sql_query($poll_query);
			if ($db->error[$poll_handle] === 1) {
				$debug->addMessage('Failed to read poll table');
				return false;
			}
			$poll_count = $db->sql_num_rows($poll_handle);
			if ($poll_count == 0) {
				$return .= 'No polls currently exist.<br />'."\n";
			}
			$return .= 'Question <select name="question_id">'."\n";
			for ($i = 1; $i <= $poll_count; $i++) {
				$poll = $db->sql_fetch_assoc($poll_handle);
				if ($vars['question_id'] == $poll['question_id']) {
					$return .= "\t".'<option value="'.$poll['question_id'].'" selected>'.$poll['question'].'</option>'."\n";
				} else {
					$return .= "\t".'<option value="'.$poll['question_id'].'">'.$poll['question'].'</option>'."\n";
				}
			}
			$return .= '</select><br />'."\n";
			$return .= '<input type="hidden" name="attributes" value="question_id" />'."\n";
			break;

// ----------------------------------------------------------------------------

		case 'calendarcategories':
			$return .= '<input type="hidden" name="attributes" value="" />'."\n";
			$return .= 'No options exist for this type.<br />'."\n";
			break;

// ----------------------------------------------------------------------------

		case 'events':
			if (!isset($vars['mode'])) {
				$vars['mode'] = 'upcoming';
			}
			if (!isset($vars['num'])) {
				$vars['num'] = 10;
			}
			$return .= 'Display Mode <select name="mode">'."\n";
			if ($vars['mode'] == 'upcoming') {
				$return .= "\t".'<option value="upcoming" selected>Upcoming</option>'."\n";
				$return .= "\t".'<option value="past">Past</option>'."\n";
			} else {
				$return .= "\t".'<option value="upcoming">Upcoming</option>'."\n";
				$return .= "\t".'<option value="past" selected>Past</option>'."\n";
			}
			$return .= '</select><br />'."\n";
			$return .= 'Number of Entries <input type="text" name="num" size="4" value="'.$vars['num'].'" />';
			$return .= '<input type="hidden" name="attributes" value="mode,num" />'."\n";
			break;
	}
	return $return;
}
?>