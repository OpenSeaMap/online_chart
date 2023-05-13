/******************************************************************************
 Copyright 2009 - 2011 Olaf Hannemann

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
 Version 0.1.1  03.10.2011
 ******************************************************************************/

/*
angepasst von Tim Reinartz im Rahmen der Bachelor-Thesis
*/

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
    url += url.indexOf("?") > -1 ? "&" : "?";
    url += encodeURIComponent(name) + "=" + encodeURIComponent(params[name]);
  }
  // Get tidal scales for Germany
  var TidalScaleDeUrl = "https://gauges.openseamap.org/getTides.php";
  var scriptDe = document.createElement("script");
  scriptDe.src = TidalScaleDeUrl + url;
  scriptDe.type = "text/javascript";
  document.body.appendChild(scriptDe);
  // Get tidal scales for Switzerland
  var TidalScaleChUrl = "http://osmch.chaosdwarfs.de/web/getTidalTest.php";
  var scriptCh = document.createElement("script");
  scriptCh.src = TidalScaleChUrl + url;
  scriptCh.type = "text/javascript";
  document.body.appendChild(scriptCh);
}

function putTidalScaleMarker(
  id,
  lon,
  lat,
  tidal_name,
  name,
  namegebiet,
  messwert,
  tendenz,
  pnp,
  datum,
  uhrzeit,
  daten_fehler
) {
  if (!tidal_scale_exist(id)) {
    var popupText =
      '<div style="position:absolute; top:4.5px; right:5px; cursor:pointer;">';
    popupText +=
      '<img src="./resources/action/info.png"  width="17" height="17" onClick="showMapKey(\'help-tidal-scale\');"/></div>';
    popupText +=
      "<table><tr><td colspan='2'><b>" + tidal_name + "</b></td><td></td></tr>";
    popupText +=
      "<tr><td colspan='3' nowrap>" + name + ", " + namegebiet + "</td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText +=
      "<tr><td>" +
      linkTextMeasuringValue +
      "</td><td>" +
      linkTextTendency +
      "</td><td>PnP</td></tr><tr><td>" +
      messwert +
      "</td><td>" +
      tendenz +
      "</td><td>" +
      pnp +
      "</td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText +=
      "<tr><td colspan='3'>" + datum + " - " + uhrzeit + "</td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText += "<tr><td colspan='3'>" + daten_fehler + "</td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText +=
      "<tr><td colspan='3'><a href='http://www.pegelonline.wsv.de/gast/stammdaten?pegelnr=" +
      id +
      "' target='blank'>" +
      linkTextHydrographCurve +
      "</a></td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText += "<tr><td colspan='3'></td></tr>";
    popupText +=
      "<tr><td colspan='3'><a href='http://weather.openportguide.de/cgi-bin/weather.pl/weather.png?var=meteogram&nx=614&ny=750&lat=" +
      lat +
      "&lon=" +
      lon +
      "&lang=de&unit=metric&label=" +
      tidal_name +
      "' target='blank'>" +
      linkTextWeatherHarbour +
      "</a></td></tr></table>";

    const [x, y] = ol.proj.fromLonLat([lon, lat]);
    createTidalScaleMarker(x, y, popupText);

    var TidalScale = {
      id: id,
      name: tidal_name,
      lat: lat,
      lon: lon,
      feature: null,
    };
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

  const [minX, minY, maxX, maxY] = map.getView().calculateExtent();
  const [l, b] = ol.proj.toLonLat([minX, minY]);
  const [r, t] = ol.proj.toLonLat([maxX, maxY]);
  const params = { b, t, l, r };
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

var tiddalScaleStyle = new ol.style.Style({
  image: new ol.style.Icon({
    src: "resources/places/tidal_scale_24.png",
    imgSize: [32, 32],
    size: [32, 32],
    scale: 24 / 32,
  }),
});

function createTidalScaleMarker(x, y, popupText) {
  if (zoom >= 5 || refreshTidalScales.call_count > 0) {
    var pointFeature = new ol.Feature(new ol.geom.Point([x, y]));
    pointFeature.setStyle(tiddalScaleStyle);
    pointFeature.set("popupContentHTML", popupText);
    layer_tidalscale.getSource().addFeature(pointFeature);
  }
}
