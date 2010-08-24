<!--
// Block list functions
function block_list_update() {
	var urlBase = "./admin/scripts/block_list.php";
	var pagefield = document.getElementById('adm_page');
	var page = pagefield.value;
	var blocklist = document.getElementById('adm_block_list');
	var blocklist_left = document.getElementById('adm_blocklist_left');
	var blocks_left;
	if (blocklist_left == undefined) {
		blocks_left = '0';
	} else {
		blocks_left = blocklist_left.value;
	}
	var blocklist_right = document.getElementById('adm_blocklist_right');
	var blocks_right;
	if (blocklist_right == undefined) {
		blocks_right = '0';
	} else {
		blocks_right = blocklist_right.value;
	}
	loadHTML(urlBase + "?a=update&page=" + encodeURI(page) + "&left=" + encodeURI(blocks_left) + "&right=" + encodeURI(blocks_right),blocklist);
}

function block_list_add(position,side) {
	var blocklist_field = document.getElementById('adm_blocklist_' + side);
	var add_field = document.getElementById('adm_add_block_list');
	if (add_field == undefined) {
		return;
	}
	var blocklist = blocklist_field.value;
	var block_add = add_field.value;
	blocklist = blocklist.split(',');
	blocklist.splice(position,0,block_add);
	blocklist = blocklist.join(',');
	blocklist_field.value = blocklist;
	block_list_update();
}

function block_list_remove(position,side) {
	var blocklist_field = document.getElementById('adm_blocklist_' + side);
	var blocklist = blocklist_field.value;
	blocklist = blocklist.split(',');
	blocklist.splice(position,1);
	blocklist = blocklist.join(',');
	blocklist_field.value = blocklist;
	block_list_update();
}

function block_options_list_update() {
	var urlBase = "./admin/scripts/block_options.php";
	var blocktypeoptions = document.getElementById('adm_block_type_options');
	var blocktypelist = document.getElementById('adm_block_type_list');
	var blocktype = blocktypelist.value;
	loadHTML(urlBase + "?blocktype=" + encodeURI(blocktype),blocktypeoptions);
}

// Other functions
function update_dynamic_file_list() {
	var urlBase = "./admin/scripts/dynamic_file_list.php";
	var dynamiclistdiv = document.getElementById('dynamic_file_list');
	var folderlist = document.getElementById('dynamic_folder_dropdown_box');
	var newfolder = folderlist.value;
	loadHTML(urlBase + "?newfolder=" + encodeURI(newfolder),dynamiclistdiv);
}

function update_dynamic_article_link_list() {
	var urlBase = "./admin/scripts/dynamic_article_link_list.php";
	var dynamiclistdiv = document.getElementById('dynamic_article_link_list');
	var pagelist = document.getElementById('page_select');
	var page = pagelist.value;
	loadHTML(urlBase + "?page=" + encodeURI(page),dynamiclistdiv);
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

function update_file_list(dir) {
	var urlBase = './admin/scripts/file_list.php';
	var listdiv = document.getElementById('adm_file_list');
	var dirlist = document.getElementById('adm_file_dir_list');
	listdiv.innerHTML = 'Loading...';

	if (dir == '-') {
		dir = dirlist.value;
	}

	loadHTML(urlBase + "?directory=" + encodeURI(dir),listdiv);
}
-->