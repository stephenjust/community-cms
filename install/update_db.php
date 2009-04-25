<?php
define('SECURITY',1);
require('../config.php');
require('../functions/main.php');
initialize();
echo "<html>\n<head>\n<title>Community CMS Database Update</title>\n</head><body>";
$query = NULL;
$error = 0;
// ----------------------------------------------------------------------------
// QUERY ARRAY (VERSION 0.01 -> 0.02)
// ----------------------------------------------------------------------------

$query[0] = 'DROP TABLE IF EXISTS '.$CONFIG['db_prefix'].'admin_pages';
$query[1] = 'ALTER TABLE '.$CONFIG['db_prefix'].'pages ADD `text_id` TEXT NULL AFTER `id`';
$query[2] = 'CREATE TABLE IF NOT EXISTS '.$CONFIG['db_prefix'].'user_groups (
 	`id` int(5) NOT NULL auto_increment,
 	`name` text NOT NULL,
 	`label_format` text NOT NULL,
 	PRIMARY KEY (`id`)
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1';
 $query[3] = 'INSERT INTO '.$CONFIG['db_prefix'].'user_groups
 (`name`,`label_format`) VALUES
 ("Administrator","font-weight: bold; color: #009900;")';
$query[4] = 'ALTER TABLE '.$CONFIG['db_prefix'].'users ADD `groups` TEXT NULL AFTER `password`';
$query[5] = 'UPDATE '.$CONFIG['db_prefix'].'users SET `groups` = "1" WHERE `id` = 1 LIMIT 1';

$query[6] = 'UPDATE '.$CONFIG['db_prefix'].'config SET `db_version` = 0.02';

// ----------------------------------------------------------------------------
$num_queries = count($query);
for($i = 0; $i < $num_queries; $i++) {
    $handle = $db->query($query[$i]);
    echo $query[$i];
    if(!$handle) {
        echo ' <span style="color: #CC0000; font-weight: bold;">FAILED</span><br />';
        $error = 1;
    } else {
        echo ' <span style="color: #00CC00; font-weight: bold;">SUCCESS</span><br />';
    }
}
if($error == 1) {
    echo 'Something went wrong. That is bad. You may need to repair the database
        manually.';
} else {
    echo 'Update successful.';
}
clean_up();
echo "</body>\n</html>\n";

?>
