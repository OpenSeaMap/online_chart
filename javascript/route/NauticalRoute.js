/******************************************************************************
 Copyright 2011 Olaf Hannemann

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
 This file implements the nautical route service to the OpenSeaMap map.
 Version 0.1.1  15.10.2011
 ******************************************************************************/

var defaultStyle = {strokeColor: "blue", strokeOpacity: "0.8", strokeWidth: 3, fillColor: "blue", pointRadius: 3, cursor: "pointer"};
var style = OpenLayers.Util.applyDefaults(defaultStyle, OpenLayers.Feature.Vector.style["default"]);
var routeStyle = new OpenLayers.StyleMap({
    'default': style,
    'select': {strokeColor: "red", fillColor: "red"}
});

var editPanel;
var routeDraw;
var routeEdit;

var routeTrack;
var routeObject;

var style_edit = {
    strokeColor: "#CD3333",
    strokeWidth: 3,
    pointRadius: 4
};

function NauticalRoute_initControls() {
    editPanel = new OpenLayers.Control.Panel();
    routeDraw = new OpenLayers.Control.DrawFeature(layer_nautical_route, OpenLayers.Handler.Path, {title: 'Draw line'});
    routeEdit = new OpenLayers.Control.ModifyFeature(layer_nautical_route, {title: 'Edit feature'}),
    editPanel.addControls([routeDraw, routeEdit]);
    editPanel.defaultControl = routeDraw;
    map.addControl(editPanel);
    routeEdit.standalone = true;
}

function NauticalRoute_startEditMode() {
    NauticalRoute_initControls();
    routeChanged = false;
}

function NauticalRoute_stopEditMode() {
    if (!routeDraw) {
        return;
    }
    routeDraw.deactivate();
    routeEdit.deactivate();
    layer_nautical_route.removeAllFeatures();
}

function NauticalRoute_addMode() {
    routeDraw.activate();
    routeEdit.deactivate();
}

function NauticalRoute_editMode() {
    routeDraw.deactivate();
    routeEdit.activate();
    //layer_nautical_route.style = style_green;
}

function get_nautical_actionDialog() {
    let htmlText = `<div id="actionDialogMenu" class="menu">
    <ul>
        <li><span>File</span>
            <ul>
                <li><a onclick="toggleOpenFileDialog();">Open...</a></li>
                <li><a onclick="toggleSaveFileDialog();">Save...</a></li>
                <li class="separator"></li>
                <li onclick="togglePrefDialog();">Preferences...</li>
                <li class="separator"></li>
                <li><a onClick="if (!routeChanged || confirm('${confirmClose}')) {closeNauticalRoute();}">Close</a></li>
            </ul>
        </li>
        <li><span>Edit</span>
            <ul>
                <li><a onclick="if (!routeChanged || confirm('${confirmDelete}')) {closeNauticalRoute();addNauticalRoute();}"/>Clear route</a></li>
            </ul>
        </li>
        <li><span><a href="#help">Help</a></span>
        </li>
    </ul></div>

    <div class="modal" id="openfiledialog">
        <div class="modal-content">
            <span class="close-button" onclick="toggleOpenFileDialog();">×</span>
            <h1>Open File</h1>
            <input type="file" id="openfilename" accept="application/gpx+xml"/> Choose file<br>
            <input type="radio" id="useTrack"> use track information<br>
            <input type="radio" id="useRoute"> use route information<br>
            <button class="trigger" onclick="toggleOpenFileDialog();NauticalRoute_openTrack();">Load</button>
            <button class="trigger" onclick="toggleOpenFileDialog();">Cancel</button>
        </div>
    </div>

    <div class="modal" id="savefiledialog">
        <div class="modal-content">
            <span class="close-button" onclick="toggleSaveFileDialog();">×</span>
            <h1>Save File</h1>
            Format <select id="routeFormat">
                <option value="GPX"/>GPX
                <option value="CSV"/>CSV
                <option value="GML">GML
                <option value="KML"/>KML
            </select>
            <button class="trigger" onclick="toggleSaveFileDialog();NauticalRoute_saveTrack();">Download</button>
            <button class="trigger" onclick="toggleSaveFileDialog();">Cancel</button>
        </div>
    </div>

    <div class="modal" id="preferences">
        <div class="modal-content">
            <span class="close-button" onclick="togglePrefDialog();">×</span>
            <h1>Preferences</h1>
            <ul>
                <li>Coordinate format <input type="text" size="4em" id="coordFormat" onchange="NauticalRoute_getPoints(routeTrack);" value="N __°##.#'">
                </li>
                <li>Unit <select id="distUnits" onchange="NauticalRoute_getPoints(routeTrack);">
                    <option value="nm"/>[nm]
                    <option value="ft"/>[ft]
                    <option value="km"/>[km]
                    <option value="m"/>[m]
                    </select>
                </li>
            </ul>
        </div>
    </div>

    <table id="tripSummary">
        <thead>
            <tr><th>Start</th><th>Finish</th><th>Distance</th></tr>
        </thead>
        <tbody>
            <tr><td id="routeStart">--</td><td id="routeEnd">--</td><td id="routeDistance">--</td></tr>
        </tbody>
    </table>

    <table id="segmentList">
        <thead>
            <tr>
                <th>#</th>
                <th>${tableTextNauticalRouteCourse}</th>
                <th>${tableTextNauticalRouteDistance}</th>
                <th>Name</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="routePoints">
        </tbody>
    </table>
    `;
    return htmlText;
}

function toggleOpenFileDialog() {
    let o=$('#openfiledialog');
    o.toggleClass('show-modal');
}
function togglePrefDialog() {$('#preferences').toggleClass('show-modal');}
function toggleSaveFileDialog() {$('#savefiledialog').toggleClass('show-modal');}

function NauticalRoute_saveTrack() {
    var format = document.getElementById("routeFormat").value;
    var name   = document.getElementById("tripName").value;
    var mimetype, filename;

    if (name=="") {
        name = "route";
    }

    switch (format) {
        case 'CSV':
            mimetype = 'text/csv';
            filename = name+'.csv';
            content = NauticalRoute_getRouteCsv(routeTrack);
            break;
        case 'KML':
            mimetype = 'application/vnd.google-earth.kml+xml';
            filename = name+'.kml';
            content = NauticalRoute_getRouteKml(routeTrack);
            break;
        case 'GPX':
            mimetype = 'application/gpx+xml';
            filename = name+'.gpx';
            content = NauticalRoute_getRouteGpx(routeObject);
            break;
        case 'GML':
            mimetype = 'application/gml+xml';
            filename = name+'.gml';
            content = NauticalRoute_getRouteGml(routeObject);
            break;
    }

    // Remove previous added forms
    $('#actionDialog > form').remove();

    form = document.createElement('form');
    form.id = this.id + '_export_form';
    form.method = 'post';
    form.action = './api/export.php';
    document.getElementById('actionDialog').appendChild(form);
    div = document.createElement('div');
    div.className = this.displayClass + "Control";
    form.appendChild(div);
    input = document.createElement('input');
    input.id = this.id + '_export_input_mimetype';
    input.name = 'mimetype';
    input.type = 'hidden';
    input.value = mimetype;
    div.appendChild(input);
    input = document.createElement('input');
    input.id = this.id + '_export_input_filename';
    input.name = 'filename';
    input.type = 'hidden';
    input.value = filename;
    div.appendChild(input);
    input = document.createElement('input');
    input.id = this.id + '_export_input_content';
    input.name = 'content';
    input.type = 'hidden';
    input.value = content;
    div.appendChild(input);

    routeChanged = false;

    $('#actionDialog > form').get(0).submit();
}

function NauticalRoute_openTrack() {
    let fileInput = document.querySelector("#openfilename");
    let files = fileInput.files;
    let gpxFile = files[0];

    var reader = new FileReader();
    reader.onload = function(event) {
        var contents = event.target.result;
        let parser = new DOMParser();
        let xmlDoc = parser.parseFromString(contents, "text/xml");

            // change from XPath to other selection syntax?
    // see https://www.topografix.com/GPX/1/1/#type_rteType
    // and https://en.wikipedia.org/wiki/GPS_Exchange_Format
    // and https://developer.mozilla.org/en-US/docs/Web/API/Document
    // specifically https://developer.mozilla.org/en-US/docs/Web/API/Document/evaluate
    // and https://developer.mozilla.org/en-US/docs/Web/API/XPathResult

        // get the name of the route
        let name = xmlDoc.getElementsByTagName("name");
        $('#tripName').val(name[0].innerHTML)

        let points=[] ;
        let rte = xmlDoc.getElementsByTagName("rtept");
        if (rte.length == 0) // if no route contained, use track
            rte = xmlDoc.getElementsByTagName("trkpt")

        for (const pt of rte) {
            const lat = $(pt).attr('lat')
            const lon = $(pt).attr('lon')
            points.push(new OpenLayers.Geometry.Point(lon2x(lon),lat2y(lat)))
        }

        // then convert into a feature for OpenLayers
        let ls = new OpenLayers.Geometry.LineString(points)
        let feat = new OpenLayers.Feature.Vector(ls,{},style)

        layer_nautical_route.removeAllFeatures({silent:true});
        layer_nautical_route.addFeatures(feat);
    };

    reader.onerror = function(event) {
        console.error("File could not be read! Code " + event.target.error.code);
    };

    reader.readAsText(gpxFile);
}

function NauticalRoute_routeAdded(event) {
    routeChanged = true;
    routeObject = event.object.features[0];

    routeDraw.deactivate();
    routeEdit.activate();
    // Select element for editing
    routeEdit.selectFeature(routeObject);

    NauticalRoute_getPoints();
}

function NauticalRoute_routeModified(event) {
    routeChanged = true;
    routeObject = event.object.features[0];

    NauticalRoute_getPoints();
}

let routeChanged = false;

function NauticalRoute_getPoints() {
    let points = routeObject.geometry.getVertices();

    const distFactors = {km: 1/ 0.540, m : 1000 / 0.540, nm : 1, ft : 1000 / (0.540*0.3048)};
    let distFactor = distFactors[$('#distUnits').val()];

    var htmlText;
    var latA, latB, lonA, lonB, distance, bearing;
    let totalDistance = 0;

    let latFormat = $('#coordFormat').val();
    let lonFormat = $('#coordFormat').val().replace(/[NS]/,'W');
    let coordFormat = function(lat,lon) {return formatCoords(lat, latFormat) + " - " + formatCoords(lon, lonFormat);}

    document.getElementById("routeStart").innerHTML = '--';
    document.getElementById("routeEnd").innerHTML   = '--';
    document.getElementById("routeDistance").innerHTML = '--';
    document.getElementById("routePoints").innerHTML = '';

    htmlText = '';
    if (points != undefined) {
        for(i = 0; i < points.length - 1; i++) {
            latA = y2lat(points[i].y);
            lonA = x2lon(points[i].x);
            latB = y2lat(points[i + 1].y);
            lonB = x2lon(points[i + 1].x);
            bearing = getBearing(latA, latB, lonA, lonB);
            distance = getDistance(latA, latB, lonA, lonB) * distFactor;
            totalDistance += distance;
            htmlText +=
                '<tr>' +
                '<td>' + parseInt(i+1) + '.</td>' +
                '<td>' + bearing.toFixed(1) + '°</td>' +
                '<td>' + distance.toFixed(1) + ' ' + $('#distUnits').val() + '</td>' +
                '<td>' + coordFormat(latB,lonB) + '</td>' +
                '<td>' + 'O' + '</td></tr>'
        }
        document.getElementById("routeStart").innerHTML = coordFormat(y2lat(points[0].y),x2lon(points[0].x));
        document.getElementById("routeEnd").innerHTML   = coordFormat(y2lat(points[points.length-1].y),x2lon(points[points.length-1].x));
        document.getElementById("routeDistance").innerHTML = totalDistance.toFixed(2) + ' ' + $('#distUnits').val();
        document.getElementById("routePoints").innerHTML = htmlText;
    }
}

function NauticalRoute_getRouteCsv(points) {
    var buffText = ";" + tableTextNauticalRouteCourse + ";" + tableTextNauticalRouteDistance + ";" + tableTextNauticalRouteCoordinate + "\n";
    var latA, latB, lonA, lonB, distance, bearing;
    var totalDistance = 0;

    for(i = 0; i < points.length - 1; i++) {
        latA = y2lat(points[i].y);
        lonA = x2lon(points[i].x);
        latB = y2lat(points[i + 1].y);
        lonB = x2lon(points[i + 1].x);
        distance = getDistance(latA, latB, lonA, lonB).toFixed(2);
        bearing = getBearing(latA, latB, lonA, lonB).toFixed(2);
        totalDistance += parseFloat(distance);
        buffText += parseInt(i+1)+ ";" + bearing + "°;" + distance + "nm;\"" + lat2DegreeMinute(latB) + " - " + lon2DegreeMinute(lonB) + "\"\n";
    }

    return convert2Text(buffText);
}

function NauticalRoute_getRouteKml(points) {
    var latA, lonA;
    var buffText = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<kml xmlns=\"http://earth.google.com/kml/2.0\">\n";
    buffText += "<Folder>\n<name>OpenSeaMap Route</name>\n<description>test</description>";
    buffText += "<Placemark>\n<name>OpenSeaMap</name>\n<description>No description available</description>";
    buffText += "<LineString>\n<coordinates>\n";
    for(i = 0; i < points.length; i++) {
        latA = y2lat(points[i].y);
        lonA = x2lon(points[i].x);
        buffText += lonA + "," + latA + " ";
    }
    buffText += "\n</coordinates>\n</LineString>\n</Placemark>\n</Folder>\n</kml>";

    return buffText;
}

function NauticalRoute_getRouteGpx(feature) {
    var parser = new OpenLayers.Format.GPX({
        internalProjection: map.projection,
        externalProjection: proj4326
    });

    return parser.write(feature);
}

function NauticalRoute_getRouteGml(feature) {
    var parser = new OpenLayers.Format.GML.v2({
        internalProjection: map.projection,
        externalProjection: proj4326
    });

    return parser.write(feature);
}
