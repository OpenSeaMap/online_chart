function wmmViaXHR(url) {
	var xmlHttp, modelLines, wmm, i, vals, epoch, model, modelDate, temp;
	try {
		xmlHttp = new XMLHttpRequest();
	} catch (e0) {
		try {// Internet Explorer 5 & 6
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e1) {
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e2) {
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	xmlHttp.open("GET", url, false);
	xmlHttp.send(null);
	if (xmlHttp.status === 200 || xmlHttp.readyState === 4) {
		modelLines = xmlHttp.responseText.split('\n');
		wmm = [];
		for (i in modelLines) {
			if (modelLines.hasOwnProperty(i)) {
				vals = modelLines[i].replace(/^\s+|\s+$/g, "").split(/\s+/);
				if (vals.length === 3) {
					epoch = parseFloat(vals[0]);
					model = vals[1];
					modelDate = vals[2];
				} else if (vals.length === 6) {
					temp = {
						n: parseInt(vals[0], 10),
						m: parseInt(vals[1], 10),
						gnm: parseFloat(vals[2]),
						hnm: parseFloat(vals[3]),
						dgnm: parseFloat(vals[4]),
						dhnm: parseFloat(vals[5])
					};
					wmm.push(temp);
				}
			}
		}
		modelLines = null;
		xmlHttp = null;
		
		return {epoch: epoch, model: model, modelDate: modelDate, wmm: wmm};
	} else {
		return false;
	}
}
