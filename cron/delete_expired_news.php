<?php
/**
 * Script to delete expired news items
 */
define('ROOT', '../');
define('SECURITY',1);
require(ROOT.'config.php');
require(ROOT.'include.php');
require(ROOT.'vendor/autoload.php');
initialize();

$query = "SELECT `id`, `name` FROM `".NEWS_TABLE."` "
    . "WHERE `delete_date` <= NOW() "
    . "AND `delete_date` IS NOT NULL";

try {
    $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
} catch (Exceptions\DBException $ex) {
    Log::addMessage('Failed to look for expired news articles in script', LOG_LEVEL_ADMIN);
    die('Failed to look for expired news articles in script');
}

foreach ($results as $result) {
    $c = new Content($result['id']);
    $c->delete();
    printf('Automated deletion of article "%s"<br />', $result['name']);
    Log::addMessage(sprintf('Automated deletion of article "%s"', $result['name']));
}
