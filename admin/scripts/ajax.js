<!--
function createXHR()
{
    var request = false;
        try {
            request = new ActiveXObject('Msxml2.XMLHTTP');
        }
        catch (err2) {
            try {
                request = new ActiveXObject('Microsoft.XMLHTTP');
            }
            catch (err3) {
		try {
			request = new XMLHttpRequest();
		}
		catch (err1)
		{
			request = false;
		}
            }
        }
    return request;
}

/**
	responseHTML
	(c) 2007-2008 xul.fr
	Licence Mozilla 1.1
*/

/**
	Loads a HTML page
	Put the content of the body tag into the current page.
	Arguments:
		url of the other HTML page to load
		id of the tag that has to hold the content
*/

function loadHTML(url, storage)
{
	var xhr = createXHR();
	xhr.onreadystatechange=function()
	{
		if(xhr.readyState == 4)
		{
			//if(xhr.status == 200)
			{
				storage.innerHTML = xhr.responseText;
			}
		}
	};

	xhr.open("GET", url , true);
	xhr.send(null);

}

function changeContent(file, mode, action)
{
	var contentdiv = document.getElementById('content_container');
	loadHTML("./admin/" + encodeURI(file) + ".php?mode=" + encodeURI(mode) + "&action=" + encodeURI(action), contentdiv);
	$('div.tab_selected').removeClass('tab_selected');
	$('#tab-' + mode).addClass('tab_selected');
}



// ----------------------------------------------------------------------------
// Installer Functions
// ----------------------------------------------------------------------------

function setDefaultPort() {
	var dbenginefield = document.getElementById('db_engine');
	var dbportfield = document.getElementById('db_port');
	var dbengine = dbenginefield.value;
	var dbport = dbportfield.value;
	if (dbengine == 'MySQL') {
		dbportfield.value = '3306';
	}
	if (dbengine == 'PostgreSQL') {
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
	if (db_engine.length == 0) {
		errormessage = errormessage + 'No database engine given. ';
	}
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
	if (db_pass.length == 0) {
		errormessage = errormessage + 'No database password given. ';
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
