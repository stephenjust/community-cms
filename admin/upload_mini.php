<?php
	define('SECURITY',1);
	define('ADMIN',1);
	define('ROOT','../');
	include(ROOT.'config.php');
	@ $db = new mysqli($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass'],$CONFIG['db_name']);
	include(ROOT.'include.php');
	include(ROOT.'functions/error.php');
	function log_action($message) {
		global $db;
		global $CONFIG;
		$date = date('Y-m-d H:i:s');
		$user = $_SESSION['userid'];
		$ip_octet = $_SERVER['REMOTE_ADDR'];
		$ip_int = ip2long($ip_octet);
		$log_query = 'INSERT INTO '.$CONFIG['db_prefix'].'logs (user_id,action,date,ip_addr) VALUES ('.$user.',"'.$message.'","'.$date.'",'.$ip_int.')';
		if(!$db->query($log_query)) {
			$message_error = mysqli_error($db);
			}
		return $message_error;
		}
	session_start();
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
  $db->close();
?>