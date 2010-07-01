<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * @ignore
 */
DEFINE('SECURITY',1);
DEFINE('ADMIN',1);
define('ROOT','./');

$content = NULL;
// Load error handling code
require_once('./functions/error.php');
// Load database configuration
if (!include_once('./config.php')) {
	err_page(0001);
}
// Check if site is disabled.
if ($CONFIG['disabled'] == 1) {
	err_page(1);
}
// Once the database connections are made, include all other necessary files.
if (!include_once('./include.php')) {
	err_page(2001);
}
initialize();

// Initialize some variables to keep PHP from complaining.
if (!isset($_GET['view'])) {
	$_GET['view'] = NULL;
}
if (!isset($_GET['login'])) {
	$_GET['login'] = NULL;
}
if (!isset($_GET['module'])) {
	$_GET['module'] = NULL;
}
if (!isset($_GET['action'])) {
	$_GET['action'] = NULL;
}
if (!isset($_GET['id'])) {
	$_GET['id'] = NULL;
}
if (!isset($_GET['ui'])) {
	$_GET['ui'] = 0;
}
// Run login checks.
checkuser_admin();
include('./functions/admin.php');
include('./includes/admin.php');
function display_admin() {
	global $CONFIG;
	global $db;
	global $acl;
	$template_page = new template;
	$template_page->load_admin_file();
	$page_title = 'Community CMS Administration';

	// Don't cache compressed TinyMCE when debugging
	if (DEBUG === 1) {
		$mce_disk_cache = 'false';
	} else {
		$mce_disk_cache = 'true';
	}

	$template_page->scripts = '<link type="text/css"
		href="./scripts/jquery-ui/jquery-ui.css" rel="stylesheet" />
		<script language="javascript" type="text/javascript"
		src="./admin/scripts/ajax.js"></script>
		<script language="javascript" type="text/javascript"
		src="./admin/scripts/admin.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery-ui.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery-autocomplete.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery-custom.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/tiny_mce/tiny_mce_gzip.js"></script>
		<script type="text/javascript">
		tinyMCE_GZ.init({
			plugins : \'style,layer,table,save,advhr,advimage,advlist,advlink,comcmslink,cleanupstyles,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,spellchecker,noneditable,visualchars,nonbreaking,xhtmlxtras\',
			themes : "advanced",
			languages : "en",
			disk_cache : '.$mce_disk_cache.',
			debug : false
		});
		</script>
		<script language="javascript" type="text/javascript">
		tinyMCE.init({
			mode : "textareas",
			editor_deselector : "mceNoEditor",
			theme : "advanced",
			skin : "o2k7",
			plugins : "style,layer,table,save,advhr,advimage,advlist,advlink,comcmslink,cleanupstyles,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,spellchecker,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
			content_css : "scripts/tiny_mce/editor_styles.css",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,fontselect,fontsizeselect,|,help,code",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,comcmslink,image,cleanup,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,sub,sup,|,charmap,iespell,advhr,|,print,|,ltr,rtl,|,fullscreen,|,spellchecker",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,insertdate,inserttime,preview,|,hr,removeformat,visualaid",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : "true",
			theme_advanced_fonts:"Arial=arial,helvetica,sans-serif;Arial Narrow=arial narrow,arial,sans-serif;Arial Black=arial black,avant garde;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva",
			theme_advanced_font_sizes:"x-small,small,medium,large,x-large,xx-large",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			style_formats : [
				{title : "No spacing", block : "p", classes : "no-spacing"},
				{title : "Header Styles"},
				{title : "Heading 1", block : "h2"},
				{title : "Heading 2", block : "h3"},
				{title : "Heading 3", block : "h4"},
				{title : "Table styles"},
				{title : "Black Border", selector : "table", classes : "black-border"},
				{title : "No Border", selector : "table", classes : "no-border"}]
		});
		
		/* Simple Editor */
		tinyMCE.init({
			mode : "textareas",
			editor_selector : "mceSimple",
			theme : "advanced",
			skin : "o2k7",
			plugins : "style,layer,table,save,advhr,advimage,advlist,advlink,comcmslink,cleanupstyles,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,spellchecker,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
			theme_advanced_buttons1 : "bold,italic,underline,|,forecolor,backcolor,|,cut,copy,paste,pastetext,pasteword,|,search,replace",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "hidden",
			theme_advanced_fonts:"Arial=arial,helvetica,sans-serif;Arial Narrow=arial narrow,arial,sans-serif;Arial Black=arial black,avant garde;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva",
			theme_advanced_font_sizes:"x-small,small,medium,large,x-large,xx-large",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			style_formats : [
				{title : "No spacing", block : "p", classes : "no-spacing"},
				{title : "Header Styles"},
				{title : "Heading 1", block : "h2"},
				{title : "Heading 2", block : "h3"},
				{title : "Heading 3", block : "h4"},
				{title : "Table styles"},
				{title : "Black Border", selector : "table", classes : "black-border"},
				{title : "No Border", selector : "table", classes : "no-border"}]
		});
		</script>';
	$image_path = $template_page->path.'images/';

	$icon_bar = NULL;
	if ($acl->check_permission('adm_feedback')) {
		$icon_bar .= '<a href="admin.php?module=feedback">
			<img src="<!-- $IMAGE_PATH$ -->send_feedback.png" alt="Send Feedback" border="0px" /></a>';
	}
	if ($acl->check_permission('adm_help')) {
		$icon_bar .= '<a href="admin.php?module=help">
			<img src="<!-- $IMAGE_PATH$ -->help.png" alt="Help" border="0px" /></a>';
	}
	$icon_bar .= '<a href="index.php?login=2">
		<img src="<!-- $IMAGE_PATH$ -->log_out.png" alt="Log Out" border="0px" /></a>';
	$template_page->icon_bar = $icon_bar;

	$template_page->nav_bar = '<div id="menu">'.admin_nav().'</div>';
	$template_page->nav_login = display_login_box();
	$template_page->page_title = $page_title;
	$css_include = '<link rel="StyleSheet" type="text/css" href="'.$template_page->path.'style.css" />';
	$template_page->css_include = $css_include;
	$template_page->image_path = $image_path;
	$template_page_bottom = $template_page->split('content');
	echo $template_page;
	unset($template_page);
	$content = NULL;
	if (isset($_GET['module'])) {
		if (!include('./admin/'.addslashes($_GET['module']).'.php')) {
			include('./admin/index.php');
		}
	} else {
		include('./admin/index.php');
	}
	$template_page_bottom->content = $content;
	$template_page_bottom->image_path = $image_path;
	if (DEBUG === 1) {
		$query_debug = $db->print_error_query();
	} else {
		$query_debug = NULL;
	}
	$template_page_bottom->footer = 'Powered by Community CMS'.$query_debug;
	echo $template_page_bottom;
	unset($template_page_bottom);
}

display_admin($content);
if (DEBUG === 1) {
	$debug->display_traces();
}
clean_up();
?>
