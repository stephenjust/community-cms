<?php
$page = "<h1>Update Database</h1>\n";
$new_db_version = 0.02;
define('SECURITY',1);
if(@ !include('../config.php')) {
    $page .= 'Failed to load the configuration file. Is Community CMS installed?';
} else {
    include('../functions/main.php');
    initialize();
    $site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config LIMIT 1';
    $site_info_handle = $db->query($site_info_query);
    if(!$site_info_handle) {
        clean_up();
        $page .= 'Failed to read site information.';
        return $page;
    }
    $site_info = $site_info_handle->fetch_assoc();
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
