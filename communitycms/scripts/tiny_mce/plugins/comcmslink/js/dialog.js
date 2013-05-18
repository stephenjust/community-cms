tinyMCEPopup.requireLangPack('comcmslink_dlg');

var ComCMSLinkDialog = {

	init : function() {
		var f = document.forms[0];
		// Get the selected contents as text and place it in the input
		f.linktext.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	},

	insert : function() {
		// Insert the contents from the input into the document
		if (eval(document.forms[0].type_file.checked) == true) {
			tinyMCEPopup.editor.execCommand('mceInsertContent', false,
				'<a href="files/'+document.forms[0].file_list.value+'">'+document.forms[0].linktext.value+'</a>');
		}
		if (eval(document.forms[0].type_article.checked) == true) {
			if (eval(document.forms[0].type_nopage.checked) == true) {
				tinyMCEPopup.editor.execCommand('mceInsertContent', false,
					'<a href="$ARTICLE_URL_NOPAGE-'+document.forms[0].article_select.value+'$">'+document.forms[0].linktext.value+'</a>');
			}
			if (eval(document.forms[0].type_onpage.checked) == true) {
				tinyMCEPopup.editor.execCommand('mceInsertContent', false,
					'<a href="$ARTICLE_URL_ONPAGE-'+document.forms[0].article_select.value+'$">'+document.forms[0].linktext.value+'</a>');
			}
			if (eval(document.forms[0].type_ownpage.checked) == true) {
				tinyMCEPopup.editor.execCommand('mceInsertContent', false,
					'<a href="$ARTICLE_URL_OWNPAGE-'+document.forms[0].article_select.value+'$">'+document.forms[0].linktext.value+'</a>');
			}
		}
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ComCMSLinkDialog.init, ComCMSLinkDialog);
