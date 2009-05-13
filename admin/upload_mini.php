<?php
define('SECURITY',1);
define('ADMIN',1);
define('ROOT','../');
include(ROOT.'config.php');
include(ROOT.'include.php');
include(ROOT.'functions/admin.php');
include(ROOT.'functions/error.php');
initialize();
checkuser_admin();
$content = '<html>
<head>
<title>Upload File</title>
</head>
<body>';
// Check if the form has been submitted.
if(isset($_GET['upload'])) {
	$content .= file_upload($_POST['path']);
}
// Display upload form and upload location selector.
$content .= file_upload_box(1);
$content .= '</body></html>';
echo $content;
clean_up();
?>