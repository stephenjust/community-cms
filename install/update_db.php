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



// ----------------------------------------------------------------------------
$num_queries = count($query);
for($i = 0; $i < $num_queries; $i++) {
    $handle = $db->query($query[$i]);
    if(!$handle) {
        echo 'Query '.$i.' failed.<br />';
        $error = 1;
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
