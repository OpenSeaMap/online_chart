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

// var defaultStyle = {strokeColor: "blue", strokeOpacity: "0.8", strokeWidth: 3, fillColor: "blue", pointRadius: 3, cursor: "pointer"};
// var style = OpenLayers.Util.applyDefaults(defaultStyle, OpenLayers.Feature.Vector.style["default"]);
// var routeStyle = new OpenLayers.StyleMap({
//     'default': style,
//     'select': {strokeColor: "red", fillColor: "red"}
// });

var editPanel;
var routeDraw;
var routeEdit;

var routeTrack;
var routeObject;

var style_edit = {
  strokeColor: "#CD3333",
  strokeWidth: 3,
  pointRadius: 4,
};
const modifyStyle = new ol.style.Style({
  image: new ol.style.Circle({
    radius: 3,
    fill: new ol.style.Fill({
      color: "blue",
    }),
    stroke: new ol.style.Stroke({
      width: 3,
      color: "rgba(0,0,255,0.8)",
    }),
  }),
  stroke: new ol.style.Stroke({
    width: 3,
    color: "red",
  }),
  geometry: (feature) => {
    const line = feature.getGeometry();
    const multipoint = new ol.geom.MultiPoint(line.getCoordinates());
    const geomColl = new ol.geom.GeometryCollection([line, multipoint]);
    return geomColl;
  },
});

function NauticalRoute_startEditMode() {
  routeDraw = new ol.interaction.Draw({
    type: "LineString",
    source: layer_nautical_route.getSource(),
  });
  routeDraw.on("drawend", NauticalRoute_routeAdded);
  routeEdit = new ol.interaction.Modify({
    source: layer_nautical_route.getSource(),
    style: modifyStyle,
  });
  routeEdit.on("modifyend", NauticalRoute_routeModified);
  map.addInteraction(routeDraw);
  map.addInteraction(routeEdit);
  routeDraw.setActive(true);
  routeEdit.setActive(false);
  layer_nautical_route.setStyle((feature) => {
    return modifyStyle;
  });
}

function NauticalRoute_stopEditMode() {
  if (!routeDraw) {
    return;
  }
  layer_nautical_route.un("addfeature", NauticalRoute_routeAdded);
  routeDraw.setActive(false);
  routeEdit.setActive(false);
  map.removeInteraction(routeEdit);
  map.removeInteraction(routeDraw);
  layer_nautical_route.getSource().clear();
}

function NauticalRoute_DownloadTrack() {
  var format = document.getElementById("routeFormat").value;
  var name = document.getElementById("tripName").value;
  var mimetype, filename;

  if (name == "") {
    name = "route";
  }

  switch (format) {
    case "CSV":
      mimetype = "text/csv";
      filename = name + ".csv";
      content = NauticalRoute_getRouteCsv(routeTrack);
      break;
    case "KML":
      mimetype = "application/vnd.google-earth.kml+xml";
      filename = name + ".kml";
      content = NauticalRoute_getRouteKml(routeObject);
      break;
    case "GPX":
      mimetype = "application/gpx+xml";
      filename = name + ".gpx";
      content = NauticalRoute_getRouteGpx(routeObject);
      break;
    case "GML":
      mimetype = "application/gml+xml";
      filename = name + ".gml";
      content = NauticalRoute_getRouteGml(routeTrack);
      break;
  }

  // Remove previous added forms
  document.querySelector("#actionDialog > form")?.remove();

  form = document.createElement("form");
  form.id = this.id + "_export_form";
  form.method = "post";
  form.action = "./api/export.php";
  document.getElementById("actionDialog").appendChild(form);
  div = document.createElement("div");
  div.className = this.displayClass + "Control";
  form.appendChild(div);
  input = document.createElement("input");
  input.id = this.id + "_export_input_mimetype";
  input.name = "mimetype";
  input.type = "hidden";
  input.value = mimetype;
  div.appendChild(input);
  input = document.createElement("input");
  input.id = this.id + "_export_input_filename";
  input.name = "filename";
  input.type = "hidden";
  input.value = filename;
  div.appendChild(input);
  input = document.createElement("input");
  input.id = this.id + "_export_input_content";
  input.name = "content";
  input.type = "hidden";
  input.value = content;
  div.appendChild(input);

  document.querySelector("#actionDialog > form").submit();

  routeChanged = false;
}

function NauticalRoute_routeAdded(event) {
  routeChanged = true;
  routeDraw.setActive(false);
  routeEdit.setActive(true);
  NauticalRoute_routeModified(event);
}

function NauticalRoute_routeModified(event) {
  routeObject = event.feature || event.features.item(0);
  routeTrack = routeObject
    .getGeometry()
    .getCoordinates()
    .map(([x, y]) => ({ x, y }));
  NauticalRoute_getPoints(routeTrack);
  document.getElementById("buttonRouteDownloadTrack").disabled = false;
}

function NauticalRoute_getPoints(points) {
  var htmlText;
  var latA, latB, lonA, lonB, distance, bearing;
  var totalDistance = 0;
  var distUnits = document.getElementById("distUnits").value;
  var coordFormat = function (lat, lon) {
    return (
      formatCoords(lat, "N __.___°") + " - " + formatCoords(lon, "W___.___°")
    );
  };

  if (document.getElementById("coordFormat").value == "coordFormatdms") {
    coordFormat = function (lat, lon) {
      return (
        formatCoords(lat, "N __°##'##\"") +
        " - " +
        formatCoords(lon, "W___°##'##\"")
      );
    };
  }

  htmlText = '<table id="routeSegmentList">';
  htmlText +=
    "<tr><th/>" +
    "<th>" +
    tableTextNauticalRouteCourse +
    "</th>" +
    "<th>" +
    tableTextNauticalRouteDistance +
    "</th>" +
    "<th>" +
    tableTextNauticalRouteCoordinate +
    "</th></tr>";
  for (i = 0; i < points.length - 1; i++) {
    const [lon0, lat0] = ol.proj.toLonLat([points[i].x, points[i].y]);
    const [lon1, lat1] = ol.proj.toLonLat([points[i + 1].x, points[i + 1].y]);
    latA = lat0;
    lonA = lon0;
    latB = lat1;
    lonB = lon1;
    distance = getDistance(latA, latB, lonA, lonB);
    if (distUnits == "km") {
      distance = nm2km(distance);
    }
    bearing = getBearing(latA, latB, lonA, lonB);
    totalDistance += distance;
    htmlText +=
      "<tr>" +
      "<td>" +
      parseInt(i + 1) +
      ".</td>" +
      "<td>" +
      bearing.toFixed(2) +
      "°</td>" +
      "<td>" +
      distance.toFixed(2) +
      " " +
      distUnits +
      "</td>" +
      "<td>" +
      coordFormat(latB, lonB) +
      "</td></tr>";
  }
  htmlText += "</table>";

  const [lon0, lat0] = ol.proj.toLonLat([points[0].x, points[0].y]);
  const [lon1, lat1] = ol.proj.toLonLat([
    points[points.length - 1].x,
    points[points.length - 1].y,
  ]);

  document.getElementById("routeStart").innerHTML = coordFormat(lat0, lon0);
  document.getElementById("routeEnd").innerHTML = coordFormat(lat1, lon1);
  document.getElementById("routeDistance").innerHTML =
    totalDistance.toFixed(2) + " " + distUnits;
  document.getElementById("routePoints").innerHTML = htmlText;
}

function NauticalRoute_getRouteCsv(points) {
  var buffText =
    ";" +
    tableTextNauticalRouteCourse +
    ";" +
    tableTextNauticalRouteDistance +
    ";" +
    tableTextNauticalRouteCoordinate +
    "\n";
  var latA, latB, lonA, lonB, distance, bearing;
  var totalDistance = 0;

  for (i = 0; i < points.length - 1; i++) {
    const [lon0, lat0] = ol.proj.toLonLat([points[i].x, points[i].y]);
    const [lon1, lat1] = ol.proj.toLonLat([points[i + 1].x, points[i + 1].y]);
    latA = lat0;
    lonA = lon0;
    latB = lat1;
    lonB = lon1;
    distance = getDistance(latA, latB, lonA, lonB).toFixed(2);
    bearing = getBearing(latA, latB, lonA, lonB).toFixed(2);
    totalDistance += parseFloat(distance);
    buffText += parseInt(i + 1) + ";" + bearing + "°;" + distance + 'nm;"';

    if (document.getElementById("coordFormat").value == "coordFormatdms") {
      buffText +=
        formatCoords(latB, "N___°##.####'") +
        " - " +
        formatCoords(lonB, "W___°##.####'");
    } else {
      buffText +=
        formatCoords(lat, "N __.___°") + " - " + formatCoords(lon, "W___.___°");
    }
    buffText += '"\n';
  }

  return convert2Text(buffText);
}

function NauticalRoute_getRouteKml(feature) {
  // var latA, lonA;
  // var buffText = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<kml xmlns=\"http://earth.google.com/kml/2.0\">\n";
  // buffText += "<Folder>\n<name>OpenSeaMap Route</name>\n<description>test</description>";
  // buffText += "<Placemark>\n<name>OpenSeaMap</name>\n<description>No description available</description>";
  // buffText += "<LineString>\n<coordinates>\n";
  // for(i = 0; i < points.length; i++) {
  //     latA = y2lat(points[i].y);
  //     lonA = x2lon(points[i].x);
  //     buffText += lonA + "," + latA + " ";
  // }
  // buffText += "\n</coordinates>\n</LineString>\n</Placemark>\n</Folder>\n</kml>";

  // return buffText;

  var parser = new ol.format.KML();
  return parser.writeFeatures([feature], {
    featureProjection: map.getView().getProjection(),
    dataProjection: "EPSG:4326",
  });
}

function NauticalRoute_getRouteGpx(feature) {
  var parser = new ol.format.GPX();
  return parser.writeFeatures([feature], {
    featureProjection: map.getView().getProjection(),
    dataProjection: "EPSG:4326",
  });
}

function NauticalRoute_getRouteGml(points) {
  // GML2 parser is not implmented in ol7 and GML3 doesn not work.
  // var parser = new ol.format.GML32();
  // return parser.writeFeatures([feature], {
  //     featureProjection: map.getView().getProjection(),
  //     dataProjection: 'EPSG:4326'
  // });
  let coordText = "";
  for (i = 0; i < points.length; i++) {
    const [lonA, latA] = ol.proj.toLonLat([points[i].x, points[i].y]);
    coordText += lonA + "," + latA + " ";
  }
  const gml = `
<gml:featureMember xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/gml http://schemas.opengis.net/gml/2.1.2/feature.xsd">
    <gml:null>
        <gml:geometry>
            <gml:LineString>
                <gml:coordinates decimal="." cs="," ts=" ">${coordText}</gml:coordinates>
            </gml:LineString>
        </gml:geometry>
    </gml:null>
</gml:featureMember>
`;
  return gml;
}
