<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

function adm_display_replace_placeholders($template) {
	if (!is_object($template)) {
		return false;
	}
	$template->image_path = $template->path.'images/';
	$template->css_include = '<link rel="StyleSheet" type="text/css"
		href="'.$template->path.'style.css" />';
	return (string)$template;
}

function adm_display_header($title = 'Community CMS Administration') {
	global $site_info;
	$template = new template;
	$template->load_admin_file('header');
	$template->scripts = '<link type="text/css"
		href="./scripts/jquery-ui/jquery-ui.css" rel="stylesheet" />
		<script language="javascript" type="text/javascript"
		src="./scripts/tiny_mce/tiny_mce_gzip.js"></script>
		<script language="javascript" type="text/javascript"
		src="./admin/scripts/ajax.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery-ui.js"></script>
		<script language="javascript" type="text/javascript"
		src="./scripts/jquery-custom.js"></script>
		<script language="javascript" type="text/javascript"
		src="./admin/scripts/dynamic_file_list.js"></script>
		<script language="javascript" type="text/javascript"
		src="./admin/scripts/block_options.js"></script>
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
		</script>';
		$template->page_title = $title;
		$template = adm_display_replace_placeholders($template);
		echo $template;
}
?>
