<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * Assist in generating admin pages
 *
 * @package CommunityCMS.admin
 */
class admin_page extends page {
	/**
	 * Module
	 * @var string
	 */
	public $module = "";

	public function __construct($module) {
		// FIXME: Stub
		return;
	}

	/**
	 * display_header - Print the page header
	 */
	public function display_header() {
		global $acl;

		$template = new template;
		$template->load_admin_file('header');

		// Include javascript
		// Don't cache compressed TinyMCE when debugging
		if (DEBUG === 1) {
			$mce_disk_cache = 'false';
		} else {
			$mce_disk_cache = 'true';
		}

		// Make sure modified javascript is reloaded
		$admin_js_mtime = filemtime('./admin/scripts/admin.js');

		$scripts = '<link type="text/css"
			href="./scripts/jquery-ui/jquery-ui.css" rel="stylesheet" />
			<script language="javascript" type="text/javascript"
			src="./scripts/ajax.js"></script>
			<script language="javascript" type="text/javascript"
			src="./admin/scripts/admin.js?t='.$admin_js_mtime.'"></script>
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
		$template->scripts = $scripts;
		unset($scripts);

		// Include StyleSheets
		$css_include = '<link rel="StyleSheet" type="text/css" href="'.$template->path.'style.css" />';
		if (DEBUG === 1) {
			$css_include .= '<link rel="StyleSheet" type="text/css" href="'.$template->path.'debug.css" />';
		}
		$template->css_include = $css_include;
		unset($css_include);

		// Display icon bar
		$icon_bar = NULL;
		if ($acl->check_permission('adm_feedback')) {
			$icon_bar .= '<a href="admin.php?module=feedback">
				<img src="<!-- $IMAGE_PATH$ -->send_feedback.png" alt="Send Feedback"
				border="0px" width="32px" height="32px" /></a>';
		}
		if ($acl->check_permission('adm_help')) {
			$icon_bar .= '<a href="admin.php?module=help">
				<img src="<!-- $IMAGE_PATH$ -->help.png" alt="Help"
				border="0px" width="32px" height="32px" /></a>';
		}
		$icon_bar .= '<a href="index.php?login=2">
			<img src="<!-- $IMAGE_PATH$ -->log_out.png" alt="Log Out"
			border="0px" width="32px" height="32px" /></a>';
		$template->icon_bar = $icon_bar;

		echo $template;
		unset($template);
	}

	public function display_debug() {
		global $db;
		global $debug;

		$template = new template;
		$template->load_admin_file('debug');
		$template->debug_queries = $db->print_queries();
		$template->debug_query_stats = $db->print_query_stats();
		$template->debug_log = $debug->display_traces();
		echo $template;
		unset($template);
	}

	public function display_footer() {
		$template = new template;
		$template->load_admin_file('footer');
		$template->footer = 'Powered by Community CMS';
		echo $template;
	}
}
?>
