tinyMCEPopup.requireLangPack();

var FileLinkDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.linktext.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	},

	insert : function() {
		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<a href="'+document.forms[0].file_list.value+'">'+document.forms[0].linktext.value+'</a>');
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(FileLinkDialog.init, FileLinkDialog);
