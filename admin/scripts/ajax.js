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

-->
