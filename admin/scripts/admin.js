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

// Unused
function gallery_dir_check() {
	var scriptURL = "./admin/scripts/gallery_dir_check.php";
	var gallerydircheckdiv = document.getElementById('_gallery_dir_check_');
	var gallerytypefield = document.getElementById('_gallery_app');
	var gallerytype = gallerytypefield.value;
	var gallerydirfield = document.getElementById('_gallery_dir');
	var gallerydir = gallerydirfield.value;
	if (gallerytype == 'disabled') {
		return;
	}
	loadHTML(scriptURL + "?type=" + encodeURI(gallerytype) + "&dir=" + encodeURI(gallerydir),gallerydircheckdiv);
	var gallerydircheckresponse = gallerydircheckdiv.innerHTML;
	if (gallerydircheckresponse == "false") {
		gallerydirfield.style.border = '1px solid #FF0000';
		gallerydirfield.style.background = '#FFCCCC';
	} else {
		gallerydirfield.style.border = 'auto';
		gallerydirfield.style.background = 'auto';
	}
}

function update_article_list(page) {
	var urlBase = './admin/scripts/news_article_list.php';
	var listdiv = document.getElementById('adm_news_article_list');
	var pagelist = document.getElementById('adm_article_page_list');
	listdiv.innerHTML = 'Loading...';

	if (page == '-') {
		page = pagelist.value;
	}

	loadHTML(urlBase + "?page=" + encodeURI(page),listdiv);
}

function update_newsletter_list(page) {
	var urlBase = './admin/scripts/newsletter_list.php';
	var listdiv = document.getElementById('adm_newsletter_list');
	var pagelist = document.getElementById('adm_newsletter_page_list');
	listdiv.innerHTML = 'Loading...';

	if (page == '-') {
		page = pagelist.value;
	}

	loadHTML(urlBase + "?page=" + encodeURI(page),listdiv);
}
-->