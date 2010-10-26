/******************************************************************************
 Copyright 2008 - 2010 Xavier Le Bourdon, Christoph Böhme, Mitja Kleider
 
 This file originates from the Openstreetbugs project and was modified
 by Matthias Hoffmann and Olaf Hannemann for the OpenSeaMap project.
 
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
 ******************************************************************************/

/******************************************************************************
 This file implements the client-side of the harbour display.
 ******************************************************************************/

// List of downloaded harbours:
var harbours = new Array();


// Current state of the user interface. This is used
// to keep track which popups are displayed.
var harbour_state = 0;
var harbour_current_feature = null;

var popuptime=0;


// AJAX functions--------------------------------------------------------------------------------------------

// Request harbours from the server.
function make_harbour_request(params) {
	var url = "";
	for (var name in params) {
		url += (url.indexOf("?") > -1) ? "&" : "?";
		url += encodeURIComponent(name) + "=" + encodeURIComponent(params[name]);
	}
	//var skgUrl="http://harbor.openseamap.org/getHarboursSkipperGuide.php"+url;
	var skgUrl="http://harbor.openseamap.org/getHarboursWpi.php"+url;
	
	var script = document.createElement("script");
	script.src = skgUrl;
	script.type = "text/javascript";
	document.body.appendChild(script);
}

// This function is called from the scripts that are returned on make_harbour_request calls.
function putAJAXMarker(id, lon, lat, names, link, type) {
	//alert(type);
	if (!harbour_exist(id,type)) {
		var name = names.split("-");
		if(type==-1)
			type = determineHarbourType(name[0]);
		var popupText = "<b>" + name[0] +"</b><br/>";
		if (typeof name[1] != "undefined") {
			popupText += name[1];
		}
		if (typeof name[2] != "undefined") {
			popupText += "<br/><i>" + name[2] + "</i>";
		}
		if (link != '') {
			popupText += "<br/><br/><a href='" + link + "' target='blank'>" + linkText + "</a>";
		}
		
		var harbour = {id: id, name: names, lat: lat, lon: lon, type: type, feature: null};
		harbour.feature = create_harbour_feature(lon2x(lon), lat2y(lat), popupText, type);
		harbours.push(harbour);
	}
}


// Harbour management----------------------------------------------------------------------------------------

// Downloads new harbours from the server.
function refresh_harbours() {
	
	/*
	 * Decision Block to select which harbours' visibility has to be changed.
	 * As there currently no appropriate data-structure, there is currently no 
	 * use of that block but thats only a question of time until there is such
	 * an data structure
	
	if(zoomLevel<oldZoom){//Area displayed on map gets less detailed
	  if(oldZoom==DISPLAY_ALL){//clear all skg-harbours that are not the representant of a group
	   //TODO 
	  }
	  else if(oldZoom==GROUP_HARBOURS){//clear all skg-harbours
	    //TODO
	  }
	}
	else if(zoomLevel>oldZoom){//Area displayed on map gets more detailled
	  if(zoomLevel==GROUP_HARBOURS){//display all harbours that are a representant
	   //TODO 
	  }
	  else if(zoomLevel==DISPLAY_ALL){//display all harbours
	    //TODO
	  }
	}
	*/
	
	if (refresh_harbours.call_count == undefined) {
		refresh_harbours.call_count = 0;
	} else {
		++refresh_harbours.call_count;
	}
	bounds = map.getExtent().toArray();
	b = y2lat(bounds[1]).toFixed(5);
	t = y2lat(bounds[3]).toFixed(5);
	l = x2lon(bounds[0]).toFixed(5);
	r = x2lon(bounds[2]).toFixed(5);

	var params = { "b": b, "t": t, "l": l, "r": r, "ucid": refresh_harbours.call_count, "maxSize":getHarbourVisibility(zoom), "zoom":zoom};
	make_harbour_request(params);
	
}

// Check if a harbour has been downloaded already.
function harbour_exist(id,type) {
	for (var i in harbours) {
		if (harbours[i].id == id && (harbours[i].type == type
						    || type==-1 && (harbours[i].type==5
										 ||harbours[i].type==6 ))) {
			return true;
		}
	}
	
	return false;
}

function isWPI(type) {
  /*
  1 = L
  2 = M
  3 = S
  4 = V (Very small)
  5 = representative skipperguide
  6 = other skipperguide
  */
	if(type<=4) {
		return true;
	}
	return false;
}

function determineHarbourType(myName){
	for (var i in harbours) {
		var otherName = harbours[i].name.split("-");
		if(myName==otherName[0]) {
			return 6;
		}
	}
	return 5;
}

function ensureHarbourVisibility(zoom){
	harbour_clear();
	var maxType = getHarbourVisibility(zoom);

	for (var i in harbours) {
		if (harbours[i].type <= maxType) {
			if(zoom>=5) {
				create_harbour_marker(harbours[i].feature,harbours[i].type);
			}
		}
	}
}

function getHarbourVisibility(zoom){
 var maxType=1;
 if(zoom>=7)
   maxType=2;
 if(zoom>=8)
   maxType=3;
 if(zoom>=9)
   maxType=4;
 if(zoom>=10)
   maxType=5;
 if(zoom>=12)
   maxType=6;
 return maxType;
}

// Remove previously displayed harbours from layer
function harbour_clear() {
	// Remove Markers from layer
	var toBeDestroyed= layer_harbours.markers;
	for(var i=layer_harbours.markers.length-1; i>=0;i--) {
		layer_harbours.removeMarker(toBeDestroyed[i]);
	}

	// Reset all layer values
	//refresh_harbours.call_count = null;
	if(harbour_current_feature != null) {
		map.removePopup(harbour_current_feature.popup);
	}
	harbour_current_feature = null;
	harbour_state = 0;
}

// Return a harbour description from the list of downloaded harbours.
function get_harbour(id,type) {
	for (var i in harbours) {
		if (harbours[i].id == id && harbours[i].type == type) {
			return harbours[i];
		}
	}
	return '';
}

// This function creates a feature and adds a corresponding marker to the map.
function create_harbour_feature(x, y, popup_content, type) {
	if(!create_harbour_feature.harbour_icon) {
		var harbourIcon='./resources/places/harbour_32.png';
		var marinaIcon='./resources/places/marina_32.png';
		icon_size = new OpenLayers.Size(32, 32);
		icon_offset = new OpenLayers.Pixel(-16, -16);
		create_harbour_feature.harbour_icon = new OpenLayers.Icon(harbourIcon, icon_size, icon_offset);
		create_harbour_feature.marina_icon = new OpenLayers.Icon(marinaIcon, icon_size, icon_offset);
	}
	var icon = isWPI(type) ? create_harbour_feature.harbour_icon.clone() : create_harbour_feature.marina_icon.clone();
	var feature = new OpenLayers.Feature(layer_harbours, new OpenLayers.LonLat(x, y), {icon: icon});
	feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud);
	feature.data.popupContentHTML = popup_content;

	create_harbour_marker(feature,type);

	return feature;
}

function create_harbour_marker(feature,type) {
	var harbour_marker = feature.createMarker();
	var harbour_marker_click = function (ev) {
		var d=new Date();
		var now=d.getTime();
		if((now-popuptime)<500){
			OpenLayers.Event.stop(ev);
			return;
		}
		
		if (harbour_state == 0) {
			// no popup is open
			this.createPopup();
			map.addPopup(this.popup);
			harbour_state = 1;
			harbour_current_feature = this;
			popuptime=now;
		} else if (harbour_state == 1 && harbour_current_feature == this)	{
			// click on the harbour to which belongs the open popup => remove popup
			map.removePopup(this.popup)
			harbour_state = 0;
			harbour_current_feature = null;
			popuptime=now;
		} else if (harbour_state == 1 && harbour_current_feature != this) {
			// click on another harbour => remove old popup and create a new one at this harbour
			map.removePopup(harbour_current_feature.popup);
			this.createPopup();
			map.addPopup(this.popup);
			harbour_state = 1;
			harbour_current_feature = this;
		}
		OpenLayers.Event.stop(ev);
	};
	var harbour_marker_mouseover = function (ev) {
		if (this != harbour_current_feature) {
			this.createPopup();
			map.addPopup(this.popup)
		}
		map.div.style.cursor = "pointer";
		OpenLayers.Event.stop(ev);
	};
	var harbour_marker_mouseout = function (ev) {
		if (this != harbour_current_feature) {
			map.removePopup(this.popup);
		}
		map.div.style.cursor = "default";
		OpenLayers.Event.stop(ev);
	};
	// marker_click must be registered as click and not as mousedown! Otherwise a click event will be
	// propagated to the click control of the map under certain conditions.
	harbour_marker.events.register("click", feature, harbour_marker_click);
	harbour_marker.events.register("mouseover", feature, harbour_marker_mouseover);
	harbour_marker.events.register("mouseout", feature, harbour_marker_mouseout);

	var maxType=getHarbourVisibility(map.getZoom());
	if(type<=maxType){
		if(zoom>=5 ||refresh_harbours.call_count>0) {
			layer_harbours.addMarker(harbour_marker);
		}
	}
}

