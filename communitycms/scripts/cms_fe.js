<!--
window.onload=$(function(){
	// Nav Menu
	$('#nav-menu').dropDownMenu({timer: 1000, parentMO: 'menuitem-hover', childMO: 'submenuitem-hover'});
});

function minipoll_vote(pollid,answerid) {
	var urlBase = "./scripts/minipoll_vote.php";
	var answerdiv = document.getElementById("minipoll_answer_block_" + pollid);
	loadHTML(urlBase + "?question_id=" + encodeURI(pollid) + "&answer_id=" + encodeURI(answerid),answerdiv)
}

function gallery_load(galleryid) {
	var urlBase = "./scripts/gallery.php";
	var gallerydiv = document.getElementById("image_gallery-" + galleryid);
	loadHTML(urlBase + "?id=" + encodeURI(galleryid),gallerydiv);
}

function gallery_load_image(galleryid,imagepath,desc) {
	var containerdiv = document.getElementById("gallery_body-" + galleryid);
	containerdiv.innerHTML = '<div class="image_caption">' + desc + '</div><img src="' + encodeURI(imagepath) +  '" />';
}
-->