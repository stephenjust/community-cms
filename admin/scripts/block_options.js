<!--
//XMLHttpRequest class function
function datosServidor() {
};
datosServidor.prototype.iniciar = function() {
	try {
		// Mozilla / Safari
		this._xh = new XMLHttpRequest();
	} catch (e) {
		// Explorer
		var _ieModelos = new Array(
		'MSXML2.XMLHTTP.5.0',
		'MSXML2.XMLHTTP.4.0',
		'MSXML2.XMLHTTP.3.0',
		'MSXML2.XMLHTTP',
		'Microsoft.XMLHTTP'
		);
		var success = false;
		for (var i=0;i < _ieModelos.length && !success; i++) {
			try {
				this._xh = new ActiveXObject(_ieModelos[i]);
				success = true;
			} catch (e) {
			}
		}
		if ( !success ) {
			return false;
		}
		return true;
	}
}

datosServidor.prototype.ocupado = function() {
	estadoActual = this._xh.readyState;
	return (estadoActual && (estadoActual < 4));
}

datosServidor.prototype.procesa = function() {
	if (this._xh.readyState == 4 && this._xh.status == 200) {
		this.procesado = true;
	}
}

datosServidor.prototype.enviar = function(urlget,datos) {
	if (!this._xh) {
		this.iniciar();
	}
	if (!this.ocupado()) {
		this._xh.open("GET",urlget,false);
		this._xh.send(datos);
		if (this._xh.readyState == 4 && this._xh.status == 200) {
			return this._xh.responseText;
		}
		
	}
	return false;
}

var urlBase = "./admin/scripts/block_options.php";

function block_options_list_update() {
	var blocktypeoptions = document.getElementById('adm_block_type_options');
	var blocktypelist = document.getElementById('adm_block_type_list');
	var blocktype = blocktypelist.value;
	remotos = new datosServidor;
	nt = remotos.enviar(urlBase + "?blocktype=" + encodeURI(blocktype),"");
	blocktypeoptions.innerHTML = nt;
//	blocktypeoptions.innerHTML = window.onLoad.value;
	}
-->