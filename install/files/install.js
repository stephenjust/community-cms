<!--
// ----------------------------------------------------------------------------
// Installer Functions
// ----------------------------------------------------------------------------

function setDefaultPort() {
	var dbenginefield = document.getElementById('db_engine');
	var dbportfield = document.getElementById('db_port');
	var dbengine = dbenginefield.value;
	var dbport = dbportfield.value;
	if (dbengine == 'MySQL' && dbport == '5432') {
		dbportfield.value = '3306';
	}
	if (dbengine == 'PostgreSQL' && dbport == '3306') {
		dbportfield.value = '5432';
	}
}
function testSettings() {
	var db_engine_field = document.getElementById('db_engine');
	var db_host_field = document.getElementById('db_host');
	var db_port_field = document.getElementById('db_port');
	var db_name_field = document.getElementById('db_name');
	var db_user_field = document.getElementById('db_user');
	var db_pass_field = document.getElementById('db_pass');
	var db_pfix_field = document.getElementById('db_pfix');
	var db_engine = db_engine_field.value;
	var db_host = db_host_field.value;
	var db_port = db_port_field.value;
	var db_name = db_name_field.value;
	var db_user = db_user_field.value;
	var db_pass = db_pass_field.value;
	var db_pfix = db_pfix_field.value;
	var errormessage = '';
	if (db_host.length == 0) {
		errormessage = errormessage + 'No database host given. ';
	}
	if (db_port.length == 0) {
		errormessage = errormessage + 'No host port given. ';
	}
	if (db_name.length == 0) {
		errormessage = errormessage + 'No database name given. ';
	}
	if (db_user.length == 0) {
		errormessage = errormessage + 'No database user given. ';
	}
	if (db_pfix.length == 0) {
		errormessage = errormessage + 'No table prefix given.';
	}
	var messageplaceholder = document.getElementById('db_error');
	messageplaceholder.innerHTML = errormessage;

	// Quit if there was an error
	if (errormessage.length != 0) {
		return;
	}
	loadHTML('dbcheck.php?e=' + encodeURI(db_engine) + '&h=' +
		encodeURI(db_host) + '&p=' + encodeURI(db_port) + '&n=' +
		encodeURI(db_name) + '&u=' + encodeURI(db_user) + '&pa=' +
		encodeURI(db_pass) + '&pr=' + encodeURI(db_pfix),messageplaceholder);
}
-->