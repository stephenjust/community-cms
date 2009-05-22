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
if(!include_once('./config.php')) {
	err_page(0001);
	}
// Check if site is disabled.
if($CONFIG['disabled'] == 1) {
	err_page(1);
	}
// Once the database connections are made, include all other necessary files.
if(!include_once('./include.php')) {
	err_page(2001);
	}
initialize();

// Initialize some variables to keep PHP from complaining.
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
function display_admin() {
	global $CONFIG;
	global $db;
	global $site_info;
	$template_page = new template;
	$template_page->load_admin_file();
	$page_title = 'Community CMS Administration';
	$css_include = '<link rel="StyleSheet" type="text/css" href="'.$template_page->path.'style.css" />';
	$image_path = $template_page->path.'images/';
	$template_page->scripts = '<link type="text/css" href="./scripts/jquery-ui/jquery-ui.css" rel="stylesheet" />
<script language="javascript" type="text/javascript" src="./scripts/tiny_mce/tiny_mce_gzip.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/ajax.js"></script>
<script language="javascript" type="text/javascript" src="./scripts/jquery.js"></script>
<script language="javascript" type="text/javascript" src="./scripts/jquery-ui.js"></script>
<script language="javascript" type="text/javascript" src="./scripts/jquery-custom.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/dynamic_file_list.js"></script>
<script language="javascript" type="text/javascript" src="./admin/scripts/block_options.js"></script>
<script type="text/javascript">
tinyMCE_GZ.init({
	plugins : \'style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,spellchecker,noneditable,visualchars,nonbreaking,xhtmlxtras\',
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
    plugins : "style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,spellchecker,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,help,code",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,cleanup,|,forecolor,backcolor,|,spellchecker",
    theme_advanced_buttons3 : "tablecontrols,|,sub,sup,|,charmap,iespell,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,insertdate,inserttime,preview,|,hr,removeformat,visualaid",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_fonts:"Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva",
    theme_advanced_font_sizes:"x-small,small,medium,large,x-large,xx-large",
    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
</script>
<script type="text/javascript">

</script>';
		$template_page->nav_bar = '<div id="menu"><div><h3><a href="#">Main</a></h3><div>
<a href="admin.php?'.SID.'">Admin Home</a><br />
<a href="index.php?'.SID.'" target="_blank">View Site</a><br />
'.admin_nav();
		$template_page->nav_login = display_login_box();
		$template_page->page_title = $page_title;
		$template_page->css_include = $css_include;
		$template_page_bottom = $template_page->split('content');
		$template_page->image_path = $image_path;
		echo $template_page;
		unset($template_page);
		if(isset($_GET['module'])) {
			if(!include('./admin/'.addslashes($_GET['module']).'.php')) {
				include('./admin/index.php');
				}
			} else {
			include('./admin/index.php');
			}
		$template_page_bottom->content = $content;
		$template_page_bottom->image_path = $image_path;
		if(DEBUG === 1) {
			$query_debug = $db->print_error_query();
		} else {
			$query_debug = NULL;
		}
		$template_page_bottom->footer = 'Powered by Community CMS'.$query_debug;
		echo $template_page_bottom;
		unset($template_page_bottom);
		}
	display_admin($content);

	clean_up();
?>
