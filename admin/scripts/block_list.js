<!--
var urlBaseBL = "./admin/scripts/block_list.php";

function block_list_update() {
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
	loadHTML(urlBaseBL + "?a=update&page=" + encodeURI(page) + "&left=" + encodeURI(blocks_left) + "&right=" + encodeURI(blocks_right),blocklist);
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
-->