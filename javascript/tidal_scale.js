/******************************************************************************
 Copyright 2010 Olaf Hannemann
 
 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this file.  If not, see <http://www.gnu.org/licenses/>.
 ******************************************************************************

 ******************************************************************************
 This file implements the client-side of the TidalScale display.
 Version 0.0.1  07.11.2010
 ******************************************************************************/

// List of downloaded scales:
var arrayTidalScales = new Array();


// Current state of the user interface. This is used
// to keep track which popups are displayed.
var TidalScaleState = 0;
var TidalScaleCurrentFeature = null;
var TidalScalePopupTime = 0;


// AJAX functions--------------------------------------------------------------------------------------------

// Request tidal scales from the server.
function makeTidalScaleRequest(params) {
	var url = "";
	for (var name in params) {
		url += (url.indexOf("?") > -1) ? "&" : "?";
		url += encodeURIComponent(name) + "=" + encodeURIComponent(params[name]);
	}
	var TidalScaleUrl="http://harbor.openseamap.org/getTidalScaleTest.php"+url;
	
	var script = document.createElement("script");
	script.src = TidalScaleUrl;
	script.type = "text/javascript";
	document.body.appendChild(script);
}

// This function is called from the scripts that are returned on makeTidalScaleRequest calls.
function putTidalScaleMarker(id, lon, lat, tidal_name, pnp) {
	//alert("recive :" + tidal_name + pnp + " : " + id + " : " + pnp);
	if (!tidal_scale_exist(id)) {
		var popupText = "<b>" + tidal_name +"</b>";
		var TidalScale = {id: id, name: tidal_name, lat: lat, lon: lon, feature: null};
		TidalScale.feature = createTidalScaleFeature(lon2x(lon), lat2y(lat), popupText, 1);
		arrayTidalScales.push(TidalScale);
	}
}


// TidalScale management----------------------------------------------------------------------------------------

// Downloads new TidalScales from the server.
function refreshTidalScales() {

	if (refreshTidalScales.call_count == undefined) {
		refreshTidalScales.call_count = 0;
	} else {
		++refreshTidalScales.call_count;
	}
	bounds = map.getExtent().toArray();
	b = y2lat(bounds[1]).toFixed(5);
	t = y2lat(bounds[3]).toFixed(5);
	l = x2lon(bounds[0]).toFixed(5);
	r = x2lon(bounds[2]).toFixed(5);

	var params = { "b": b, "t": t, "l": l, "r": r};
	makeTidalScaleRequest(params);
	
}

// Check if a tidal_scale has been downloaded already.
function tidal_scale_exist(id) {
	for (var i in arrayTidalScales) {
		if (arrayTidalScales[i].id == id) {
			return true;
		}
	}

	return false;
}

function ensureTidalScaleVisibility(zoom){
	clearTidalScales();
	for (var i in arrayTidalScales) {
		if(zoom>=5) {
			createTidalScaleMarker(arrayTidalScales[i].feature,arrayTidalScales[i].type);
		}
	}
}

// Remove previously displayed tidal scales from layer
function clearTidalScales() {
	// Remove Markers from layer
	var toBeDestroyed= layer_tidal_scale.markers;
	for(var i=layer_tidal_scale.markers.length-1; i>=0;i--) {
		layer_tidal_scale.removeMarker(toBeDestroyed[i]);
	}

	// Reset all layer values
	if(TidalScaleCurrentFeature != null) {
		map.removePopup(TidalScaleCurrentFeature.popup);
	}
	TidalScaleCurrentFeature = null;
	TidalScaleState = 0;
}

// This function creates a feature and adds a corresponding marker to the map.
function createTidalScaleFeature(x, y, popup_content, type) {
	if(!createTidalScaleFeature.TidalScale_icon) {
		var TidalScaleIcon='./resources/places/tidal_scale_32.png';
		icon_size = new OpenLayers.Size(32, 32);
		icon_offset = new OpenLayers.Pixel(-16, -16);
		createTidalScaleFeature.TidalScale_icon = new OpenLayers.Icon(TidalScaleIcon, icon_size, icon_offset);
	}
	var icon = createTidalScaleFeature.TidalScale_icon.clone();
	var feature = new OpenLayers.Feature(layer_tidal_scale, new OpenLayers.LonLat(x, y), {icon: icon});
	feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud);
	feature.data.popupContentHTML = popup_content;

	createTidalScaleMarker(feature,type);

	return feature;
}

function createTidalScaleMarker(feature,type) {
	var TidalScale_marker = feature.createMarker();
	var TidalScale_marker_click = function (ev) {
		var d=new Date();
		var now=d.getTime();
		if((now-TidalScalePopupTime)<500){
			OpenLayers.Event.stop(ev);
			return;
		}
		if (TidalScaleState == 0) {
			// no popup is open
			this.createPopup();
			map.addPopup(this.popup);
			TidalScaleState = 1;
			TidalScaleCurrentFeature = this;
			TidalScalePopupTime=now;
		} else if (TidalScaleState == 1 && TidalScaleCurrentFeature == this)	{
			// click on the tidal scale to which belongs the open popup => remove popup
			map.removePopup(this.popup)
			TidalScaleState = 0;
			TidalScaleCurrentFeature = null;
			TidalScalePopupTime=now;
		} else if (TidalScaleState == 1 && TidalScaleCurrentFeature != this) {
			// click on another tidal scale => remove old popup and create a new one
			map.removePopup(TidalScaleCurrentFeature.popup);
			this.createPopup();
			map.addPopup(this.popup);
			TidalScaleState = 1;
			TidalScaleCurrentFeature = this;
		}
		OpenLayers.Event.stop(ev);
	};
	var TidalScale_marker_mouseover = function (ev) {
		if (this != TidalScaleCurrentFeature) {
			this.createPopup();
			map.addPopup(this.popup)
		}
		map.div.style.cursor = "pointer";
		OpenLayers.Event.stop(ev);
	};
	var  TidalScale_marker_mouseout = function (ev) {
		if (this != TidalScaleCurrentFeature) {
			map.removePopup(this.popup);
		}
		map.div.style.cursor = "default";
		OpenLayers.Event.stop(ev);
	};
	// marker_click must be registered as click and not as mousedown! Otherwise a click event will be
	// propagated to the click control of the map under certain conditions.
	TidalScale_marker.events.register("click", feature, TidalScale_marker_click);
	TidalScale_marker.events.register("mouseover", feature, TidalScale_marker_mouseover);
	TidalScale_marker.events.register("mouseout", feature,  TidalScale_marker_mouseout);

	if(zoom>=5 ||refreshTidalScales.call_count>0) {
		layer_tidal_scale.addMarker(TidalScale_marker);
	}
}

