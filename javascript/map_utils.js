/******************************************************************************
 Javascript OpenLayers map_utils
 author Olaf Hannemann
 license GPL V3
 version 0.1.1

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
 ******************************************************************************/

// Projections-----------------------------------------------------------------
var projMerc = new OpenLayers.Projection("EPSG:900913");
var proj4326 = new OpenLayers.Projection("EPSG:4326");

// Transformations-------------------------------------------------------------
function Lon2Merc(value) {
	return 20037508.34 * value / 180;
}

function Lat2Merc(value) {
	var PI = 3.14159265358979323846;
	lat = Math.log(Math.tan( (90 + value) * PI / 360)) / (PI / 180);
	return 20037508.34 * value / 180;
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

function lonLatToMercator(ll) {
	return new OpenLayers.LonLat(lon2x(ll.lon), lat2y(ll.lat));
}

// shorten coordinate to 5 digits in decimal fraction
function shorter_coord(coord) {
	return Math.round(coord*100000)/100000;
}


// Utilities-------------------------------------------------------------------
function jumpTo(lon, lat, zoom) {
	var lonlat = new OpenLayers.LonLat(lon, lat);
	lonlat.transform(proj4326, projMerc);
	map.setCenter(lonlat, zoom);
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