<?php
/**
 * Script to delete expired news items
 */
define('ROOT', '../');
define('SECURITY',1);
require(ROOT.'config.php');
require(ROOT.'include.php');
require(ROOT.'functions/news.php');
initialize();

$query = "SELECT `id`, `name` FROM `".NEWS_TABLE."`
	WHERE `delete_date` <= NOW()
	AND `delete_date` IS NOT NULL";
$handle = $db->sql_query($query);
if ($db->error[$handle]) {
	Log::addMessage('Failed to look for expired news articles in script', LOG_LEVEL_ADMIN);
	die('Failed to look for expired news articles in script');
}

for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
	$result = $db->sql_fetch_assoc($handle);
	delete_article($result['id']);
	printf('Automated deletion of article "%s"<br />', $result['name']);
	Log::addMessage(sprintf('Automated deletion of article "%s"', $result['name']));
}

?>
