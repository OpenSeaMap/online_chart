/*
	Syncronous XML HTTP Request
	
	Returns the contents of the file at the specified URL.
*/

/*jslint devel: true, browser: true, windows: true */
if (window.XMLHttpRequest === undefined) {
	window.XMLHttpRequest = function() {
		try {
			return new ActiveXObject("Msxml2.XMLHTTP.6.0");
		} catch (e1) {
			try {
				return new ActiveXObject("Msxml2.XMLHTTP.3.0");
			} catch (e2) {
				throw new Error("XMLHttpRequest is not supported");
			}
		}
	};
}

function syncXHR(url) {
	'use strict';
	var xmlHttp = new XMLHttpRequest();

	xmlHttp.open("GET", url, false);
	if ("overrideMimeType" in xmlHttp) {xmlHttp.overrideMimeType("text/plain");}
	xmlHttp.send(null);

	return (xmlHttp.status === 200 || xmlHttp.readyState === 4) ? xmlHttp.responseText : false;
}
