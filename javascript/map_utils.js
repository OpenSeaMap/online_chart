
var projMerc = new OpenLayers.Projection("EPSG:900913");
var proj4326 = new OpenLayers.Projection("EPSG:4326");


function jumpTo(lon, lat, zoom) {
	var lonlat = new OpenLayers.LonLat(lon, lat);
	lonlat.transform(proj4326, projMerc);
	map.setCenter(lonlat, zoom);
}

function Lon2Merc(lon) {
	return 20037508.34 * lon / 180;
}

function Lat2Merc(lat) {
	var PI = 3.14159265358979323846;
	lat = Math.log(Math.tan( (90 + lat) * PI / 360)) / (PI / 180);
	return 20037508.34 * lat / 180;
}

function plusfacteur(a) {
	return a * (20037508.34 / 180);
}

function moinsfacteur(a) {
	return a / (20037508.34 / 180);
}

function y2lat(a) {
	return 180/Math.PI * (2 * Math.atan(Math.exp(moinsfacteur(a)*Math.PI/180)) - Math.PI/2);
}

function lat2y(a) {
	return plusfacteur(180/Math.PI * Math.log(Math.tan(Math.PI/4+a*(Math.PI/180)/2)));
}

function x2lon(a) {
	return moinsfacteur(a);
}

function lon2x(a) {
	return plusfacteur(a);
}

function getTileURL(bounds) {
	var res = this.map.getResolution();
	var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
	var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
	var z = this.map.getZoom();
	var limit = Math.pow(2, z);
	if (y < 0 || y >= limit) {
		return null;
	} else {
		x = ((x % limit) + limit) % limit;
		url = this.url;
		path= z + "/" + x + "/" + y + "." + this.type;
		if (url instanceof Array) {
			url = this.selectUrl(path, url);
		}
		return url+path;
	}
}