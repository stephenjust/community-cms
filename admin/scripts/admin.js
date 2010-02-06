<!--
var urlBaseDFL = "./admin/scripts/dynamic_file_list.php";

function update_dynamic_file_list() {
	var dynamiclistdiv = document.getElementById('dynamic_file_list');
	var folderlist = document.getElementById('dynamic_folder_dropdown_box');
	var newfolder = folderlist.value;
	loadHTML(urlBaseDFL + "?newfolder=" + encodeURI(newfolder),dynamiclistdiv);
}

var urlBaseDALL = "./admin/scripts/dynamic_article_link_list.php";

function update_dynamic_article_link_list() {
	var dynamiclistdiv = document.getElementById('dynamic_article_link_list');
	var pagelist = document.getElementById('page_select');
	var page = pagelist.value;
	loadHTML(urlBaseDALL + "?page=" + encodeURI(page),dynamiclistdiv);
}
-->