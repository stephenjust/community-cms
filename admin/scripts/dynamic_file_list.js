<!--
var urlBaseDFL = "./admin/scripts/dynamic_file_list.php";

function update_dynamic_file_list() {
	var dynamiclistdiv = document.getElementById('dynamic_file_list');
	var folderlist = document.getElementById('dynamic_folder_dropdown_box');
	var newfolder = folderlist.value;
	remotos = new datosServidor;
//	nt = self.location.href;
	nt = remotos.enviar(urlBaseDFL + "?newfolder=" + encodeURI(newfolder),"");
	dynamiclistdiv.innerHTML = nt;
	unset( remotos );
	}
-->