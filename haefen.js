/*
 * Copyright 2008, 2009 Xavier Le Bourdon, Christoph Böhme, Mitja Kleider
 *
 * This file originates from the Openstreetbugs project and was modified 
 * by Matthias Hoffmann for the OpenSeaMap project.
 *
 *
 * This file is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This file is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file.  If not, see <http://www.gnu.org/licenses/>.
 */


/*
 * This file implements the client-side of the harbour display. To use it in an 
 * application simply add this file and call init_haefen with a map
 * object and the path of the server-side scripts.
 */


/* 
 * Some utility functions
 */

/* These functions do some coordinate transformations
 */
function plusfacteur(a) { return a * (20037508.34 / 180); }
function moinsfacteur(a) { return a / (20037508.34 / 180); }
function y2lat(a) { return 180/Math.PI * (2 * Math.atan(Math.exp(moinsfacteur(a)*Math.PI/180)) - Math.PI/2); }
function lat2y(a) { return plusfacteur(180/Math.PI * Math.log(Math.tan(Math.PI/4+a*(Math.PI/180)/2))); }
function x2lon(a) { return moinsfacteur(a); }
function lon2x(a) { return plusfacteur(a); }
function lonLatToMercator(ll) { return new OpenLayers.LonLat(lon2x(ll.lon), lat2y(ll.lat)); }


/*
 * Openstreetbugs main functions
 */


/* Map object to which openseamap/haefen has been added: 
 */
var oseamh_map = null;

/* The path on the server which contains the openseamap/haefen
 * server side scripts:
 */
var oseamh_server_path = null;

/* Feature layer for openseamap/haefen:
 */
var oseamh_layer = null;

/* List of downloaded harbours: 
 */
var oseamh_harbours = new Array();

/* Current state of the user interface. This is used
 * to keep track which popups are displayed. */
var oseamh_state = 0;
var oseamh_current_feature = null;


/* Call this method to activate openstreetbugs on the map.
 * The argument map must refer to an Openlayers.Map object. The
 * second argument defines the path on the server which contains 
 * the openstreetbugs server side scripts.
 */
function init_haefen(map, server_path)
{
	oseamh_map = map;
	oseamh_server_path = server_path;
	if (oseamh_server_path.charAt(oseamh_server_path.length-1) != "/")
		oseamh_server_path += "/";


	oseamh_layer = new OpenLayers.Layer.Markers("OpenSeaMap:Haefen");
	oseamh_layer.setOpacity(0.7);

	oseamh_map.addLayer(oseamh_layer);

	oseamh_map.events.register('moveend', oseamh_map, refresh_oseamh);
	
	//load harbours in current view
	refresh_oseamh();
}


/*
 * AJAX functions
 */

/* Request harbours from the server.
 */
function make_request(url, params)
{
	url = oseamh_server_path+url;
	for (var name in params)
	{
		url += (url.indexOf("?") > -1) ? "&" : "?";
		url += encodeURIComponent(name) + "=" + encodeURIComponent(params[name]);
	}

	var script = document.createElement("script");
	script.src = url;
	script.type = "text/javascript";
	document.body.appendChild(script);

}

/* This function is called from the scripts that are returned 
 * on make_request calls. 
 */
function putAJAXMarker(id, lon, lat, text, type)
{
	
	if (!harbour_exist(id,type))
	{
		var harbour = {id: id, text: text, lat: lat, lon: lon, type: type, feature: null};
		
		harbour.feature = create_feature(lon2x(lon), lat2y(lat), harbour.text, type);

 		
		oseamh_harbours.push(harbour);
	}
}

/*
 * Harbour management
 */

/* Downloads new harbours from the server.
 */
function refresh_oseamh()
{
	if (refresh_oseamh.call_count == undefined)
		refresh_oseamh.call_count = 0;
	else
		++refresh_oseamh.call_count;
	
	bounds = oseamh_map.getExtent().toArray();
	b = shorter_coord(y2lat(bounds[1]));
	t = shorter_coord(y2lat(bounds[3]));
	l = shorter_coord(x2lon(bounds[0]));
	r = shorter_coord(x2lon(bounds[2]));


	var params = { "b": b, "t": t, "l": l, "r": r, "ucid": refresh_oseamh.call_count };
	make_request("getHarbours.php", params);
}

/* shorten coordinate to 5 digits in decimal fraction */
function shorter_coord(coord)
{
	return Math.round(coord*100000)/100000;
}

/* Check if a harbour has been downloaded already.
 */
function harbour_exist(id,type)
{
	for (var i in oseamh_harbours)
	{
		if (oseamh_harbours[i].id == id && oseamh_harbours[i].type == type) 
			return true;
	}
	return false;
}

/* Return a harbour description from the list of downloaded harbours.
 */
function get_harbour(id,type)
{
	for (var i in oseamh_harbours)
	{
	    if (oseamh_harbours[i].id == id && oseamh_harbours[i].type == type) 
			return oseamh_harbours[i];
	}
	return '';
}

/* This function creates a feature and adds a corresponding
 * marker to the map.
 */
function create_feature(x, y, popup_content, type)
{

 if(!create_feature.harbour_icon)
	{
		var harbourIcon='./resources/places/harbour.png';
		var marinaIcon='./resources/places/marina.png';
		icon_size = new OpenLayers.Size(32, 32);
		icon_offset = new OpenLayers.Pixel(-icon_size.w/2, -icon_size.h/2);
		create_feature.harbour_icon = new OpenLayers.Icon(harbourIcon, icon_size, icon_offset);
		create_feature.marina_icon = new OpenLayers.Icon(marinaIcon, icon_size, icon_offset);
	}

	var icon = !type ? create_feature.harbour_icon.clone() : create_feature.marina_icon.clone();
	var feature = new OpenLayers.Feature(oseamh_layer, new OpenLayers.LonLat(x, y), {icon: icon});
	feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud);
	feature.data.popupContentHTML = popup_content;

	create_marker(feature);

	return feature;
}

function create_marker(feature)
{
	var marker = feature.createMarker();
	var marker_click = function (ev)
	{
		if (oseamh_state == 0)	//no popup is open
		{
			this.createPopup();
			oseamh_map.addPopup(this.popup);
			oseamh_state = 1;
			oseamh_current_feature = this;
		}
		else if (oseamh_state == 1 && oseamh_current_feature == this)
		{	
			//click on the harbour to which belongs the open popup => remove popup
			oseamh_map.removePopup(this.popup)
			oseamh_state = 0;
			oseamh_current_feature = null;
		}
		else if (oseamh_state == 1 && oseamh_current_feature != this)
		{
			//click on another harbour => remove old popup and create a new one at this harbour
			oseamh_map.removePopup(oseamh_current_feature.popup)
			this.createPopup();
			oseamh_map.addPopup(this.popup);
			oseamh_state = 1;
			oseamh_current_feature = this;
		}
		OpenLayers.Event.stop(ev);
	};
	var marker_mouseover = function (ev)
	{
		if (this != oseamh_current_feature)
		{
			document.getElementById("map_OpenLayers_Container").style.cursor = "pointer";
			this.createPopup();
			oseamh_map.addPopup(this.popup)
		}
		else //Mouse is over the harbour, which is currently popped-up => we do not need to create a popup
			document.getElementById("map_OpenLayers_Container").style.cursor = "pointer";

		OpenLayers.Event.stop(ev);
	};
	var marker_mouseout = function (ev)
	{
		if (this != oseamh_current_feature)
		{
			document.getElementById("map_OpenLayers_Container").style.cursor = "default";
			oseamh_map.removePopup(this.popup);
		}
		else	//Mouse was over the harbour, which is currently popped-up by been clicked on it
			//	=> popup can only be removed by clicking
			document.getElementById("map_OpenLayers_Container").style.cursor = "default";
		OpenLayers.Event.stop(ev);
	};
	/* marker_click must be registered as click and not as mousedown!
	 * Otherwise a click event will be propagated to the click control
	 * of the map under certain conditions.
	 */
	marker.events.register("click", feature, marker_click);
	marker.events.register("mouseover", feature, marker_mouseover);
	marker.events.register("mouseout", feature, marker_mouseout);

	oseamh_layer.addMarker(marker);
}

