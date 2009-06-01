<?php
$page = "<h1>Update Database</h1>\n";
$new_db_version = 0.02;
if (!defined('SECURITY')) {
	define('SECURITY',1);
}
if ($_GET['page'] == '3') {
	$page .= 'This tab is inactive.';
	return true;
}
if(@ !include('../config.php')) {
    $page .= 'Failed to load the configuration file. Is Community CMS installed?';
} else {
	if (!defined('ROOT')) {
		define('ROOT','../');
	}
	require(ROOT . 'includes/constants.php');
	require(ROOT . 'includes/db/db.php');
	require(ROOT . 'includes/acl.php');
    require(ROOT . 'functions/main.php');
    initialize();
    $site_info_query = 'SELECT * FROM ' . CONFIG_TABLE . ' LIMIT 1';
    $site_info_handle = $db->sql_query($site_info_query);
    if($db->error[$site_info_handle] === 1) {
        clean_up();
        $page .= 'Failed to read site information.';
        return $page;
    }
    $site_info = $db->sql_fetch_assoc($site_info_handle);
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
