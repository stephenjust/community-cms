<?php
$page = "<h1>Update Database</h1>\n";
$new_db_version = 0.03;
if (!defined('SECURITY')) {
	define('SECURITY',1);
}
if(@ !include('../config.php')) {
    $page .= 'Failed to load the configuration file. Is Community CMS installed?';
} else {
	if (!defined('ROOT')) {
		define('ROOT','../');
	}
	include_once (ROOT . 'functions/main.php');
	include_once (ROOT . 'includes/constants.php');
	initialize();
	$db_version = get_config('db_version');
	if (is_null($db_version)) {
		// Using old schema - check for db_version
		$ver_handle = $db->sql_query('SELECT db_version FROM '.CONFIG_TABLE.' LIMIT 1');
		if ($db->error[$ver_handle] === 1) {
			die('Failed to read database version. Please reinstall the CMS.');
		}
		if ($db->sql_num_rows($ver_handle) != 1) {
			die('Failed to read database version. Please reinstall the CMS.');
		}
		$ver_result = $db->sql_fetch_assoc($ver_handle);
		$db_version = $ver_result['db_version'];
	}
    $page .= 'Currently installed database version: '.$db_version."<br />\n";
    $page .= 'Update to: '.$new_db_version."<br />\n";
    if($db_version < $new_db_version) {
        $page .= '<a href="update_db.php?old_ver='.$db_version.'">Update Database</a>';
    } else {
        $page .= 'You already have the latest version of the database.';
    }
    clean_up();
}
return $page;
?>
