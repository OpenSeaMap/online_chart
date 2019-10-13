/******************************************************************************
 Copyright 2019 Wolfgang Schildbach

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
 This file implements the client-side of the magnetic compass display.
 ******************************************************************************/

// List of downloaded harbours:
var harbours = new Array();

var selectControlHarbour;

// Current state of the user interface. This is used
// to keep track which popups are displayed.
var harbour_state = 0;
var harbour_current_feature = null;
var popuptime=0;


// AJAX functions--------------------------------------------------------------------------------------------

// Request magnetic deviation from the server.
function make_magdev_request(params) {
    var url = "";
    for (var name in params) {
        url += (url.indexOf("?") > -1) ? "&" : "?";
        url += encodeURIComponent(name) + "=" + encodeURIComponent(params[name]);
    }
    // Example: http://dev.openseamap.org/website/map/api/getMagdev.php?b=43.16098&t=43.46375&l=16.23863&r=17.39219&ucid=0
    var skgUrl="http://dev.openseamap.org/website/map/api/getMagdev.php"+url;

    var script = document.createElement("script");
    script.src = skgUrl;
    script.type = "text/javascript";
    document.body.appendChild(script);

    // debug
    var ucid = 0;
    if (make_magdev_request.dev == undefined) {
        make_magdev_request.dev = 0;
    } else {
        make_magdev_request.dev = make_magdev_request.dev + 10;
    }
    setMagdev(ucid, make_magdev_request.dev);
}

// This function is called from the scripts that are returned on make_magdev_request calls.
function setMagdev(id, deviation) {
    document.getElementById('magCompassRose').style.transform = 'rotate('+deviation.toFixed(1)+'deg)';
}

// Downloads new magnetic deviation(s) from the server.
function refreshMagdev() {

    if (refreshMagdev.call_count == undefined) {
        refreshMagdev.call_count = 0;
    } else {
        ++refreshMagdev.call_count;
    }
    bounds = map.getExtent().toArray();
    var b = y2lat(bounds[1]).toFixed(5);
    var t = y2lat(bounds[3]).toFixed(5);
    var l = x2lon(bounds[0]).toFixed(5);
    var r = x2lon(bounds[2]).toFixed(5);

    var params = { "b": b, "t": t, "l": l, "r": r, "ucid": refreshMagdev.call_count};
    make_magdev_request(params);
}
