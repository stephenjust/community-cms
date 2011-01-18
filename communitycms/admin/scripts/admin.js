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
function validate_form_field(module,field,field_id) {
	var form_field = document.getElementById(field_id);
	var form_value = form_field.value;
	var pass_style = 'solid 1px #000000';
	var fail_style = 'solid 1px #FF0000';

	if (module == 'calendar' && field == 'date') {
		if (form_value.match(/^[0-1][0-9]\/[0-3][0-9]\/[1-2][0-9]{3}$/)) {
			form_field.style.border = pass_style;
		} else {
			form_field.style.border = fail_style;
		}
	}
	if (module == 'calendar' && field == 'time') {
		if (form_value.match(/^[0-1]?[0-9]:[0-9][0-9] ?[ap]m?$/i) ||
			form_value.match(/^[0-2]?[0-9]:[0-5][0-9]$/i) ||
			form_value.match(/^[0-1]?[0-9] ?[ap]m?$/i)) {
			form_field.style.border = pass_style;
		} else {
			form_field.style.border = fail_style;
		}
	}
}

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

function update_page_message_list(page) {
	var urlBase = './admin/scripts/page_message_list.php';
	var listdiv = document.getElementById('adm_page_message_list');
	var pagelist = document.getElementById('adm_page_message_page_list');
	listdiv.innerHTML = 'Loading...';

	if (page == '-') {
		page = pagelist.value;
	}

	loadHTML(urlBase + "?page=" + encodeURI(page),listdiv);
}

function confirm_delete(target) {
	if (confirm("Really delete this item?")) {
		window.location = target;
	}
}

function import_event(event_div_id) {
	var event_div = document.getElementById(event_div_id);
	event_div.style.borderColor = '#000000';
	event_div.style.backgroundColor = '#CCCCCC';

	// Initialize default values
	var event_title = '';
	var event_location = '';
	var event_start = '';
	var event_end = '';
	var event_uid = '';

	var event_title_field = document.getElementById(event_div_id + "-title");
	event_title = event_title_field.innerHTML;

	event_div.innerHTML = "This doesn't work yet.";
	setTimeout(function(){event_div.style.display = 'none';},5000);
}

function update_cl_manager(page) {
	var urlBase = './admin/scripts/cl_manager.php';
	var listdiv = document.getElementById('adm_contact_list_manager');
	var pagelist = document.getElementById('adm_cl_list');
	listdiv.innerHTML = 'Loading...';

	if (page == '-') {
		page = pagelist.value;
	}

	loadHTML(urlBase + "?page=" + encodeURI(page),listdiv);
}
function update_cl_manager_add() {
	var urlBase = './admin/scripts/cl_manager.php';
	var listdiv = document.getElementById('adm_contact_list_manager');
	var pagelist = document.getElementById('adm_cl_list');
	var contactelem = document.getElementById('cl_add_contact');
	var contact = contactelem.value;
	listdiv.innerHTML = 'Loading...';
	var page = pagelist.value;

	loadHTML(urlBase + "?page=" + encodeURI(page) + "&action=add&id=" + encodeURI(contact),listdiv);
}
function update_cl_manager_remove(contact) {
	var urlBase = './admin/scripts/cl_manager.php';
	var listdiv = document.getElementById('adm_contact_list_manager');
	var pagelist = document.getElementById('adm_cl_list');
	listdiv.innerHTML = 'Loading...';
	var page = pagelist.value;

	loadHTML(urlBase + "?page=" + encodeURI(page) + "&action=remove&id=" + encodeURI(contact),listdiv);
}
-->