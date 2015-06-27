function minipoll_vote(pollid,answerid) {
	var urlBase = "./scripts/minipoll_vote.php";
	var answerdiv = document.getElementById("minipoll_answer_block_" + pollid);
	loadHTML(urlBase + "?question_id=" + encodeURI(pollid) + "&answer_id=" + encodeURI(answerid),answerdiv)
}
