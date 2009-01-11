<!--
//XMLHttpRequest class function
function datosServe() {
};
datosServe.prototype.iniciar = function() {
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

datosServe.prototype.ocupado = function() {
	estadoActual = this._xh.readyState;
	return (estadoActual && (estadoActual < 4));
}

datosServe.prototype.procesa = function() {
	if (this._xh.readyState == 4 && this._xh.status == 200) {
		this.procesado = true;
	}
}

datosServe.prototype.enviar = function(urlget,datos) {
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

var urlBase = "./scripts/minipoll_vote.php";

function minipoll_vote(pollid,answerid) {
	var answerdiv = document.getElementById("minipoll_answer_block_" + pollid);
	remotos = new datosServe;
//	nt = remotos.enviar(urlBase + "?newfolder=" + encodeURI(newfolder),"");
	nt = remotos.enviar(urlBase + "?question_id=" + encodeURI(pollid) + "&answer_id=" + encodeURI(answerid),"");
	answerdiv.innerHTML = nt;
	}
-->