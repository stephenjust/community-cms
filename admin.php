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
		$template_path = './admin/templates/default/';
		$template_file = $template_path."index.html";
		$handle = fopen($template_file, "r");
		$template = fread($handle, filesize($template_file));
		fclose($handle);
		$page_title = 'Community CMS Administration';
		$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
		$image_path = $template_path.'images/';
		$scripts = "<script language=\"javascript\" type=\"text/javascript\" src=\"./scripts/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>
<script type=\"text/javascript\">
tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
});
</script>
<script language=\"javascript\" type=\"text/javascript\">
tinyMCE.init({
	mode : \"textareas\",
	theme : \"advanced\",
	plugins : \"style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras\",
	theme_advanced_buttons1 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect\",
	theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
	theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
	theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking\",
	theme_advanced_toolbar_location : \"top\",
	theme_advanced_toolbar_align : \"left\",
	theme_advanced_statusbar_location : \"bottom\",
	extended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\"
});
</script>";
$nav_bar = NULL;
		$nav_bar .= "<span class='nav_header'>Main</span><br />
		<a href='admin.php?".SID."'>Admin Home</a><br />
<a href='index.php?".SID."' target='_blank'>View Site</a><br />
".admin_nav()."
<span class='nav_header'>Newsletters</span><br />
<a href='admin.php?module=newsletter&".SID."'>Newsletters</a><br />
<span class='nav_header'>Pages</span><br />
<a href='admin.php?module=pages&".SID."'>Pages</a><br />
Page Types<br />
<span class='nav_header'>Users</span><br />
<a href='admin.php?module=user_create'>New User</a><br />
<a href='admin.php?module=user'>User List</a>";
		$nav_login = NULL;
		$nav_login .= display_login_box();
		$template = str_replace('<!-- $PAGE_TITLE$ -->',$page_title,$template);
		$template = str_replace('<!-- $SCRIPTS$ -->',$scripts,$template);
		$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
		$template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
		$template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
		$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
		$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
		echo $template;
		}
	display_admin($content);

	mysql_close($connect);
	$db->close();
?>
