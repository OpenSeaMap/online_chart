/******************************************************************************
 Copyright 2008 - 2009 Xavier Le Bourdon, Christoph Böhme, Mitja Kleider
 
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
 This file implements the client-side of the harbour display. To use it in an
 application simply add this file and call init_harbours with a map
 object and the path of the server-side scripts.
 ******************************************************************************/

// List of downloaded harbours:
var oseamh_harbours = new Array();


// Current state of the user interface. This is used
// to keep track which popups are displayed.
var oseamh_state = 0;
var oseamh_current_feature = null;

//last zoomlevel of the map
var oldZoom=0;

const GROUP_HARBOURS=10;
const DISPLAY_ALL=12;

const UNCLASSIFIED_SKG=-1;
const UNCLASSIFIED_WPI=0;
var popuptime=0;


/* Call this method to activate openstreetbugs on the map.
 * The argument map must refer to an Openlayers.Map object. The
 * second argument defines the path on the server which contains 
 * the openstreetbugs server side scripts.
 */
function init_harbours() {
	//load harbours in current view
	refresh_oseamh();
}



// AJAX functions--------------------------------------------------------------------------------------------

// Request harbours from the server.
function make_request(params) {
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
	
	/*var script2 = document.createElement("script2");
	script2.src = wpiUrl;
	script2.type = "text/javascript";
	document.body.appendChild(script2);*/
}

function putAJAXMarkerWpi(id, lon, lat, names, type) {
	alert("hafen");
	putAJAXMarker(id, lon, lat, names, "", type);
}

// This function is called from the scripts that are returned on make_request calls.
function putAJAXMarker(id, lon, lat, names, link, type) {
	if (!harbour_exist(id,type)) {
		var name = names.split("-");
		if(type==UNCLASSIFIED_SKG)
		  type = determineType(name[0]);
		var popupText = "<b>" + name[0] +"</b> Type=" + type +"<br/>";
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
		harbour.feature = create_feature(lon2x(lon), lat2y(lat), popupText, type);
		oseamh_harbours.push(harbour);
	}
}


// Harbour management----------------------------------------------------------------------------------------

// Downloads new harbours from the server.
function refresh_oseamh() {
	
	var zoomLevel = map.getZoom();
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
	if(oldZoom!=zoomLevel){
	  oldZoom=zoomLevel
	 ensureVisibility(zoomLevel); 
	}
	oldZoom=zoomLevel
	
	if (refresh_oseamh.call_count == undefined) {
		refresh_oseamh.call_count = 0;
	} else {
		++refresh_oseamh.call_count;
	}
	bounds = map.getExtent().toArray();
	b = y2lat(bounds[1]).toFixed(5);
	t = y2lat(bounds[3]).toFixed(5);
	l = x2lon(bounds[0]).toFixed(5);
	r = x2lon(bounds[2]).toFixed(5);

	var params = { "b": b, "t": t, "l": l, "r": r, "ucid": refresh_oseamh.call_count, "maxSize":getVisibility(zoomLevel), "zoom":zoomLevel };
	//keep the number of array elements reasonable
// 	if(oseamh_harbours.length>1000){
// 	  oseamh_harbours=new Array();
// 	  arrayNeu=new Array();
// 	  for(var i=0; i<oseamh_harbours.length; i+=2)
// 	    arrayNeu.push(oseamh_harbours[i]);
// 	  oseamh_harbours=arrayNeu;
// 	}

	make_request(params);
	
}

// Check if a harbour has been downloaded already.
function harbour_exist(id,type) {
	for (var i in oseamh_harbours) {
		if (oseamh_harbours[i].id == id && (oseamh_harbours[i].type == type
						    || type==UNCLASSIFIED_SKG && (oseamh_harbours[i].type==5
										 ||oseamh_harbours[i].type==6 ))) {
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
  if(type<=4)
    return true;
  return false;
}

function determineType(myName){
  
  for (var i in oseamh_harbours) {
	var otherName = oseamh_harbours[i].name.split("-");
	if(myName==otherName[0])
	    return 6;
  }
  return 5;
}

function ensureVisibility(zoom){
 harbour_clear();

  
 var maxType=getVisibility(zoom);

 for (var i in oseamh_harbours) {
		if (oseamh_harbours[i].type <= maxType) {
		      if(zoom>=5 || (zoom<5 && Math.random() < (1*zoom/10)))
			create_marker(oseamh_harbours[i].feature,oseamh_harbours[i].type);
		}
	}
}

function getVisibility(zoom){
 var maxType=1;
 if(zoom>=7)
   maxType=2;
 if(zoom>=8)
   maxType=3;
 if(zoom>=9)
   maxType=4;
 if(zoom>=GROUP_HARBOURS)
   maxType=5;
 if(zoom>=DISPLAY_ALL)
   maxType=6;
 return maxType;
}

// Remove previously displayed harbours from layer
function harbour_clear() {
	// Remove Markers from layer
	var toBeDestroyed= layer_harbours.markers;
	for(var i=layer_harbours.markers.length-1; i>=0;i--){
		layer_harbours.removeMarker(toBeDestroyed[i]);
	}
	
	
	// Reset all layer values
	//refresh_oseamh.call_count = null;
 	if(oseamh_current_feature != null)
 	  map.removePopup(oseamh_current_feature.popup);
	oseamh_current_feature = null;
	oseamh_state = 0;
}

// Return a harbour description from the list of downloaded harbours.
function get_harbour(id,type) {
	for (var i in oseamh_harbours) {
		if (oseamh_harbours[i].id == id && oseamh_harbours[i].type == type) {
			return oseamh_harbours[i];
		}
	}
	return '';
}

// This function creates a feature and adds a corresponding marker to the map.
function create_feature(x, y, popup_content, type) {
	if(!create_feature.harbour_icon) {
		var harbourIcon='./resources/places/harbour_32.png';
		var marinaIcon='./resources/places/marina_32.png';
		icon_size = new OpenLayers.Size(32, 32);
		icon_offset = new OpenLayers.Pixel(-16, -16);
		create_feature.harbour_icon = new OpenLayers.Icon(harbourIcon, icon_size, icon_offset);
		create_feature.marina_icon = new OpenLayers.Icon(marinaIcon, icon_size, icon_offset);
	}
	var icon = isWPI(type) ? create_feature.harbour_icon.clone() : create_feature.marina_icon.clone();
	var feature = new OpenLayers.Feature(layer_harbours, new OpenLayers.LonLat(x, y), {icon: icon});
	feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud);
	feature.data.popupContentHTML = popup_content;

	create_marker(feature,type);

	return feature;
}

function create_marker(feature,type) {
	var marker = feature.createMarker();
	var marker_click = function (ev) {
	  var d=new Date();
	  var now=d.getTime();
	  if((now-popuptime)<500){
	    OpenLayers.Event.stop(ev);
	    return;
	  }
		
		if (oseamh_state == 0) {
			// no popup is open
			this.createPopup();
			map.addPopup(this.popup);
			oseamh_state = 1;
			oseamh_current_feature = this;
			popuptime=now;
		} else if (oseamh_state == 1 && oseamh_current_feature == this)	{
			// click on the harbour to which belongs the open popup => remove popup
			map.removePopup(this.popup)
			oseamh_state = 0;
			oseamh_current_feature = null;
			popuptime=now;
		} else if (oseamh_state == 1 && oseamh_current_feature != this) {
			// click on another harbour => remove old popup and create a new one at this harbour
			map.removePopup(oseamh_current_feature.popup);
			this.createPopup();
			map.addPopup(this.popup);
			oseamh_state = 1;
			oseamh_current_feature = this;
		}
		OpenLayers.Event.stop(ev);
		
	};
	var marker_mouseover = function (ev) {
		if (this != oseamh_current_feature) {
			this.createPopup();
			map.addPopup(this.popup)
		}
		map.div.style.cursor = "pointer";
		OpenLayers.Event.stop(ev);
	};
	var marker_mouseout = function (ev) {
		if (this != oseamh_current_feature) {
			map.removePopup(this.popup);
		}
		map.div.style.cursor = "default";
		OpenLayers.Event.stop(ev);
	};
	// marker_click must be registered as click and not as mousedown! Otherwise a click event will be
	// propagated to the click control of the map under certain conditions.
	marker.events.register("click", feature, marker_click);
	marker.events.register("mouseover", feature, marker_mouseover);
	marker.events.register("mouseout", feature, marker_mouseout);

	var maxType=getVisibility(map.getZoom());
	if(type<=maxType){
	  if(zoom>=5 ||refresh_oseamh.call_count>0 || (zoom<5 && Math.random() < (map.getZoom()/10)))
	    layer_harbours.addMarker(marker);
	}
}

