<?php
	DEFINE('SECURITY',1);
	DEFINE('ADMIN',1);
	define('ROOT','./');
	session_start();
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
		err_page(1001);
		}
	$connect = mysql_connect($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass']);
	if (!$connect) {
		die('Could not connect to the MySQL server.');
		}
	// Try to open the database that is used by Community CMS.
	$select_db = mysql_select_db($CONFIG['db_name'],$connect);
	if(!$select_db) {
		die('Unable to select mysql database.');
		}
	// Once the database connections are made, include all other necessary files.
	if(!include_once('./include.php')) {
		err_page(2001);
		}
	// Load global site information.
	$site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config';
	$site_info_handle = $db->query($site_info_query);
	$site_info = $site_info_handle->fetch_assoc();
	
	// Initialize some variables to keep PHP from complaining.
	if(!isset($_GET['id'])) {
		$_GET['id'] = 1;
		}
	if(!isset($_GET['view'])) {
		$_GET['view'] = NULL;
		}
	if(!isset($_GET['login'])) {
		$_GET['login'] = NULL;
		}
	if(!isset($_GET['module'])) {
		$_GET['module'] = NULL;
		}
		if(!isset($_GET['action'])) {
		$_GET['action'] = NULL;
		}
	if(!isset($_GET['id'])) {
		$_GET['id'] = NULL;
		}
	// Run login checks.
	checkuser_admin();
	include('./functions/admin.php');
	function display_admin($content) {
		$template_page = new template;
		$template_page->load_admin_file();
		$page_title = 'Community CMS Administration';
		$css_include = '<link rel="StyleSheet" type="text/css" href="'.$template_page->path.'style.css" />';
		$image_path = $template_page->path.'images/';
		$template_page->scripts = '<script language="javascript" type="text/javascript" src="./scripts/tiny_mce/tiny_mce_gzip.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/ajax.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/dynamic_file_list.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/block_options.js"></script>
<script type="text/javascript">
tinyMCE_GZ.init({
	plugins : \'style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,spellchecker,noneditable,visualchars,nonbreaking,xhtmlxtras\',
	themes : "advanced",
	languages : "en",
	disk_cache : true,
	debug : false
});
</script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	editor_deselector : "mceNoEditor",
	theme : "advanced",
	skin : "o2k7",
	plugins : "style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,spellchecker,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,help,code",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,cleanup,|,forecolor,backcolor,|,spellchecker",
	theme_advanced_buttons3 : "tablecontrols,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,insertdate,inserttime,preview,|,hr,removeformat,visualaid",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
</script>';
		$template_page->nav_bar = '<span class="nav_header">Main</span><br />
		<a href="admin.php?'.SID.'">Admin Home</a><br />
<a href="index.php?'.SID.'" target="_blank">View Site</a><br />
'.admin_nav();
		$template_page->nav_login = display_login_box();
		$template_page->page_title = $page_title;
		$template_page->css_include = $css_include;
		$template_page->nav_login = $nav_login;
		$template_page->content = $content;
		$template_page->image_path = $image_path;
		echo $template_page;
		unset($template_page);
		}
	display_admin($content);

	mysql_close($connect);
	$db->close();
?>
