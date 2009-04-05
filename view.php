<?php
	// Report all PHP errors
	error_reporting(E_ALL);
	header('Content-type: text/html; charset=utf-8');
	// The not-so-secure security check.
	define('SECURITY',1);
	define('ROOT','./');
	session_start();
    $content = NULL;
	// Load error handling code
	require_once('./functions/error.php');
	// Load database configuration
	if(!include_once('./config.php')) {
		err_page(0001);
		}
	// Check if site is disabled.
	if($CONFIG['disabled'] == 1) {
		err_page(1);
		}
	// Try to establish a connection to the MySQL server using the MySQLi classes.		
	@ $db = new mysqli($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass'],$CONFIG['db_name']);
	if(mysqli_connect_errno()) {
		err_page(1001); // Database connect error.
		}

	// Once the database connections are made, include all other necessary files.
	if(!include_once('./include.php')) {
		err_page(2001); // File not found error.
		}
	// Initialize some variables to keep PHP from complaining.
	if(!isset($_GET['view'])) {
		$_GET['view'] = NULL;
		}
	
	// Load global site information.
	$site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config';
	$site_info_handle = $db->query($site_info_query);
	$site_info = $site_info_handle->fetch_assoc();
	
	if(!isset($_GET['article_id'])) {
		$_GET['article_id'] = "";
		}
    $_GET['article_id'] = (int)$_GET['article_id'];
    $template_handle = load_template_file('article_page.html');
    $template = $template_handle['contents'];
    $template_path = $template_handle['template_path'];
	// Get item contents.
	if($_GET['article_id'] == "") {
		$content .= 'No article to be displayed.';
		$page_title = 'Article Not Found';
		header("HTTP/1.0 404 Not Found");
		} else {
		$article_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE id = '.$_GET['article_id'].' LIMIT 1';
		$article_handle = $db->query($article_query);
		$i = 1;
		if($article_handle->num_rows == 0) {
			$content .= 'The article you requested could not be found.';
			$page_title = 'Article Not Found';
			header("HTTP/1.0 404 Not Found");
			} else {
			$article = $article_handle->fetch_assoc();
			$page_title = stripslashes($article['name']);
			$content = '<strong>'.stripslashes($article['name']).'</strong><br /><br />'.stripslashes($article['description']);
			}
		}
	// Display page
	$image_path = $template_path.'images/';
	$nav_bar = display_nav_bar();
	$nav_login = display_login_box($page_info,$site_info);
	$template = str_replace('<!-- $PAGE_TITLE$ -->',$page_title,$template);
	$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
	$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
	echo $template;
	
	// Close database connections and clean up loose ends.
	$db->close();
?>