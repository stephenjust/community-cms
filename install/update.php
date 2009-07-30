<?php
$page = "<h1>Update Database</h1>\n";
$new_db_version = 0.02;
if (!defined('SECURITY')) {
	define('SECURITY',1);
}
if(@ !include('../config.php')) {
    $page .= 'Failed to load the configuration file. Is Community CMS installed?';
} else {
	if (!defined('ROOT')) {
		define('ROOT','../');
	}
	include (ROOT . 'functions/main.php');
	include (ROOT . 'includes/constants.php');
	initialize();
    $page .= 'Currently installed database version: '.$site_info['db_version']."<br />\n";
    $page .= 'Update to: '.$new_db_version."<br />\n";
    if($site_info['db_version'] < $new_db_version) {
        $page .= '<a href="update_db.php">Update Database</a>';
    } else {
        $page .= 'You already have the latest version of the database.';
    }
    clean_up();
}
return $page;
?>
