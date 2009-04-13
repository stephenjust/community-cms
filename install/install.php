<?php
$nav_bar = "<div align='center'><span style='color: #CCCC00;'>Check file
    permissions</span><hr />\n<span style='color: #CC0000;'>Configure settings
    </span><hr />\n<span style='color: #CC0000;'>Download/save config file</span></div>\n";
// TODO: Check for required libraries: GD / ImageMagick, MySQLi, MySQL, PHP5, etc.
$content = "<h1>Check File Permissions</h1>\n";
$content = $content."config.php - ";
if(is_writable('../config.php')) {
    $content = $content."Writable<br />\n";
    $i[1] = 1;
} else {
    $i[1] = 0;
    $content = $content."Not writable. If you do not have a config.php file, please create one.<br />\n";
}
$content = $content."files/ - ";
if(is_writable('../files')) {
    $content = $content."Writable<br />\n";
    $i[2] = 1;
} else {
    $i[2] = 0;
    $content = $content."Not writable<br />\n";
}
$content = $content."templates/ - ";
if(is_writable('../templates')) {
    $content = $content."Writable<br />\n";
    $i[3] = 1;
} else {
    $i[3] = 0;
    $content = $content."Not writable<br />\n";
}
if($i[1] == 1 && $i[2] == 1 && $i[3] == 1) {
    $content = $content."<form method='POST' action='index.php?page=2'><input type='submit' value='Next' /></form>";
}
?>