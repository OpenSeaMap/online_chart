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

function NauticalRoute_DownloadTrack() {
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

function NauticalRoute_routeAdded(event) {
    routeObject = event.object.features[0];

    routeTrack = routeObject.geometry.getVertices();
    routeDraw.deactivate();
    routeEdit.activate();
    NauticalRoute_getPoints(routeTrack);
    // Select element for editing
    routeEdit.selectFeature(routeObject);
    document.getElementById('buttonRouteDownloadTrack').disabled=false;
}

function NauticalRoute_routeModified(event) {
    var routeObject = event.object.features[0];

    routeTrack = routeObject.geometry.getVertices();
    NauticalRoute_getPoints(routeTrack);
}

routeChanged = false;

function NauticalRoute_getPoints(points) {

    routeChanged = true;

    var htmlText;
    var latA, latB, lonA, lonB, distance, bearing;
    var totalDistance = 0;
    var distUnits = document.getElementById("distUnits").value;
    var coordFormat = function(lat,lon) {return formatCoords(lat,'N __.___°') + " - " + formatCoords(lon,'W___.___°');}

    if (document.getElementById("coordFormat").value == "coordFormatdms") {
        coordFormat = function(lat,lon) {return formatCoords(lat,'N __°##\'##"') + " - " + formatCoords(lon,'W___°##\'##"');}
    }

    htmlText = '<table id="routeSegmentList">';
    htmlText +=
        '<tr><th/>' +
        '<th>' + tableTextNauticalRouteCourse + '</th>' +
        '<th>' + tableTextNauticalRouteDistance + '</th>' +
        '<th>' + tableTextNauticalRouteCoordinate + '</th></tr>'
    for(i = 0; i < points.length - 1; i++) {
        latA = y2lat(points[i].y);
        lonA = x2lon(points[i].x);
        latB = y2lat(points[i + 1].y);
        lonB = x2lon(points[i + 1].x);
        distance = getDistance(latA, latB, lonA, lonB);
        if (distUnits == "km") {
            distance = nm2km(distance);
        }
        bearing = getBearing(latA, latB, lonA, lonB);
        totalDistance += distance;
        htmlText +=
            '<tr>' +
            '<td>' + parseInt(i+1) + '.</td>' +
            '<td>' + bearing.toFixed(2) + '°</td>' +
            '<td>' + distance.toFixed(2) + ' ' + distUnits + '</td>' +
            '<td>' + coordFormat(latB,lonB) + '</td></tr>'
    }
    htmlText += '</table>'

    document.getElementById("routeStart").innerHTML = coordFormat(y2lat(points[0].y),x2lon(points[0].x));
    document.getElementById("routeEnd").innerHTML   = coordFormat(y2lat(points[points.length-1].y),x2lon(points[points.length-1].x));
    document.getElementById("routeDistance").innerHTML = totalDistance.toFixed(2) + ' ' + distUnits;
    document.getElementById("routePoints").innerHTML = htmlText;
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

