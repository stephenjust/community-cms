<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * delete_article - Deletes one or more news articles
 * @global object $acl
 * @global db $db
 * @global Debug $debug
 * @param mixed $article
 * @return boolean
 */
function delete_article($article) {
	global $acl;
	global $db;
	global $debug;

	if (!$acl->check_permission('news_delete')) {
		return false;
	}

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->addMessage('Given non-numeric input',false);
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT `news`.`id`,`news`.`name` FROM
			`' . NEWS_TABLE . '` `news` WHERE
			`news`.`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->addMessage('Query failed',true);
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->addMessage('Article not found',true);
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Delete article
		$delete_query = 'DELETE FROM `' . NEWS_TABLE . '`
			WHERE `id` = '.$current;
		$delete = $db->sql_query($delete_query);
		if ($db->error[$delete] === 1) {
			return false;
		} else {
			Log::addMessage('Deleted news article \''.stripslashes($info['name']).'\' ('.$info['id'].')');
		}

		unset($delete_query);
		unset($delete);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

function move_article($article,$new_location) {
	global $db;
	global $debug;

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	if (!is_numeric($new_location)) {
		$debug->addMessage('Given non-numeric input for new location',true);
	}

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->addMessage('Given non-numeric input',true);
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT `news`.`id`,`news`.`name` FROM
			`' . NEWS_TABLE . '` `news` WHERE
			`news`.`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->addMessage('Query failed',true);
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->addMessage('Article not found',true);
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Move article
		$move_query = 'UPDATE `' . NEWS_TABLE . '`
			SET `page` = '.$new_location.'
			WHERE `id` = '.$current;
		$move = $db->sql_query($move_query);
		if ($db->error[$move] === 1) {
			return false;
		} else {
			Log::addMessage('Moved news article \''.stripslashes($info['name']).'\'');
		}

		unset($move_query);
		unset($move);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

function copy_article($article,$new_location) {
	global $db;
	global $debug;

	$id = array();
	if (is_numeric($article)) {
		$id[] = $article;
	} elseif (is_array($article)) {
		$id = $article;
	}
	unset($article);

	if (!is_numeric($new_location)) {
		$debug->addMessage('Given non-numeric input for new location',true);
	}

	for ($i = 0; $i < count($id); $i++) {
		$current = $id[$i];

		// Check data type
		if (!is_numeric($current)) {
			$debug->addMessage('Given non-numeric input',true);
			unset($current);
			continue;
		}

		// Read article information for log
		$info_query = 'SELECT * FROM
			`' . NEWS_TABLE . '` WHERE
			`id` = '.$current.' LIMIT 1';
		$info_handle = $db->sql_query($info_query);
		if ($db->error[$info_handle] === 1) {
			$debug->addMessage('Query failed',true);
			return false;
		}
		if ($db->sql_num_rows($info_handle) === 0) {
			$debug->addMessage('Article not found',true);
			return false;
		}
		$info = $db->sql_fetch_assoc($info_handle);

		// Move article
		$move_query = 'INSERT INTO `' . NEWS_TABLE . '`
			(`page`,`name`,`description`,`author`,`date`,`date_edited`,`image`,`showdate`)
			VALUES ('.$new_location.",'{$info['name']}','{$info['description']}','{$info['author']}',
			'{$info['date']}','{$info['date_edited']}','{$info['image']}',{$info['showdate']})";
		$move = $db->sql_query($move_query);
		if ($db->error[$move] === 1) {
			return false;
		} else {
			Log::addMessage('Copied news article \''.stripslashes($info['name']).'\'');
		}

		unset($move_query);
		unset($move);
		unset($info_query);
		unset($info_handle);
		unset($info);
		unset($current);
	}
	return true;
}

// ----------------------------------------------------------------------------

function save_priorities($form_array) {
	global $db;

	if (!is_array($form_array)) {
		return false;
	}
	foreach($form_array AS $key => $value) {
		if (preg_match('/^pri\-/',$key)) {
			$key = str_replace('pri-','',$key);
			$pri_save_query = 'UPDATE `'.NEWS_TABLE.'`
				SET `priority` = '.(int)$value.'
				WHERE `id` = '.(int)$key;
			$pri_save_handle = $db->sql_query($pri_save_query);
			if ($db->error[$pri_save_handle] === 1) {
				return false;
			}
		}
		unset($key);
		unset($value);
		unset($pri_save_query);
		unset($pri_save_handle);
	}
	return true;
}

function news_publish($article_id,$publish = true) {
	global $acl;
	global $db;
	global $debug;

	// Validate parameters
	if (!is_numeric($article_id)) {
		$debug->addMessage('Article ID is not numeric',true);
		return false;
	}
	if (!is_bool($publish)) {
		$debug->addMessage('Publishing state is not a boolean',true);
		return false;
	}
	$article_id = (int)$article_id;

	// Check for permission
	if (!$acl->check_permission('news_publish')) {
		$debug->addMessage('Insufficient permissions',true);
		return false;
	}

	// Get article info
	$info_query = 'SELECT `name`,`publish`
		FROM `'.NEWS_TABLE.'`
		WHERE `id` = '.$article_id.'
		LIMIT 1';
	$info_handle = $db->sql_query($info_query);
	if ($db->error[$info_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($info_handle) != 1) {
		return false;
	}
	$info = $db->sql_fetch_assoc($info_handle);

	// Check to see if we're changing the current state at all
	if ($info['publish'] == 1 && $publish == true) {
		return false;
	} elseif ($info['publish'] == 0 && $publish == false) {
		return false;
	}

	if ($publish === true) {
		$query = 'UPDATE `'.NEWS_TABLE.'`
			SET `publish` = 1
			WHERE `id` = '.$article_id;
	} else {
		$query = 'UPDATE `'.NEWS_TABLE.'`
			SET `publish` = 0
			WHERE `id` = '.$article_id;
	}
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		return false;
	}
	if ($publish === true) {
		Log::addMessage('Published article \''.$info['name'].'\'');
	} else {
		Log::addMessage('Unpublished article \''.$info['name'].'\'');
	}
	return true;
}
?>