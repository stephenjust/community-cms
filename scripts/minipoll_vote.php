<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
define('SECURITY', 1);
define('ROOT', '../');

// Load database configuration
require_once('../config.php');
require_once('../include.php');
require_once('../vendor/autoload.php');
initialize();
$user_ip     = $_SERVER['REMOTE_ADDR'];
$referer     = $_SERVER['HTTP_REFERER'];
if (preg_match('#/$#', $referer)) {
    $referer .= 'index';
}
$referer_directory = dirname($referer);
$current_directory = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
if ($current_directory != $referer_directory.'/scripts') {
    die('Security breach.');
}

$query  = 'INSERT INTO `'.POLL_RESPONSE_TABLE.'` '
    . '(`question_id`, `answer_id`, `value`, `ip_addr`) '
    . 'VALUES '
    . '(:question_id, :answer_id, NULL, :ip);';
try {
    DBConn::get()->query(
        $query,
        [
            ':question_id' => FormUtil::get('question_id'),
            ':answer_id' => FormUtil::get('answer_id'),
            ':ip' => ip2long($user_ip)
        ]);
    echo('Thank you for voting.');
} catch (Exceptions\DBException $ex) {
    echo('Failed to submit your vote.');
}
