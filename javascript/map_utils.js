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

// Zoom------------------------------------------------------------------------
var zoomUnits= [
	30*3600,	// zoom=0
	30*3600,
	15*3600,
	10*3600,
	5*3600,
	5*3600,
	2*3600,
	1*3600,
	30*60,
	20*60,
	10*60,		// zoom=10
	5*60,
	2*60,
	1*60,
	30,
	30,
	12,
	6,
	6,
	3			// zoom=19
];

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

function addMarker(layer, buffLon, buffLat, popupContentHTML) {

	var pos = new OpenLayers.LonLat(buffLon, buffLat);
	pos.transform(proj4326, projMerc);
	var mFeature = new OpenLayers.Feature(layer, pos);
	mFeature.closeBox = true;
	mFeature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {minSize: new OpenLayers.Size(260, 100) } );
	mFeature.data.popupContentHTML = popupContentHTML;

	var marker = new OpenLayers.Marker(pos);
	marker.feature = mFeature;

	var markerClick = function(evt) {
		if (this.popup == null) {
			this.popup = this.createPopup(this.closeBox);
			map.addPopup(this.popup);
			this.popup.show();
		} else {
			this.popup.toggle();
		}
		OpenLayers.Event.stop(evt);
	};


	layer.addMarker(marker);
	if (popupContentHTML != -1) {
		marker.events.register("mousedown", mFeature, markerClick);
		map.addPopup(mFeature.createPopup(mFeature.closeBox));
	}
}