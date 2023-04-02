/******************************************************************************
 Javascript OpenLayers map_utils
 author Olaf Hannemann
 license GPL V3
 version 0.1.3
 date 11.09.2011

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
 ******************************************************************************/

// Constants-------------------------------------------------------------------
var earthRadius = 6371.221; //Km

function km2nm(a) {
  return a * 0.54;
}

function nm2km(a) {
  return a / 0.54;
}

let lat2DegreeMinute = (buffLat) => formatCoords(buffLat, "N__°##.####'");
let lon2DegreeMinute = (buffLon) => formatCoords(buffLon, "W___°##.####'");

/***
 * @summary format a datum.
 * Examples:
 *  formatCoords(9.75,'N __°##\'##.###"') -> N  9°45'00.000"
 *  formatCoords(9.75,'N __°')            -> N 10°
 *  formatCoords(9.75,'W ###°##,#\'')     -> W 009°45,0'
 *
 * @coord {number} - the datum, a latitude or a longitude
 * @format {string} - the format
 * @throws Will throw exceptions if the format string is malformatted.
 */
function formatCoords(coord, format) {
  function didf(x, di, df, sep, fill) {
    let s = x.toFixed(df);
    s = s.replace(/[.,]/, sep);
    let z = df + di + (df > 0 ? 1 : 0) - s.length;
    if (z > 0) {
      /* need to prepend zeros */
      s = fill.repeat(z) + s;
    }
    return s;
  }

  // TODO verify if this bug still exists
  // OL2 had a bug maybe OL7 also (?) where sometimes the coordinates go beyond +-180
  if (coord > 180) coord -= 360;
  if (coord < -180) coord += 360;

  let a = Math.abs(coord);
  let deg = Math.trunc(a);
  let b = 60 * (a - deg);
  let min = Math.trunc(b);
  let sec = 60 * (b - min);

  let s = "";
  let i = 0;

  let di = 0;
  let df = 0;
  let sep = "";
  let fill = "#";

  do {
    let c = format.charAt(i);
    switch (c) {
      case "N":
      case "S":
        s += coord >= 0 ? "N" : "S";
        i++;
        sep = "#";
        break;
      case "W":
      case "E":
        s += coord >= 0 ? "E" : "W";
        i++;
        sep = "#";
        break;
      case " ":
        s += " ";
        i++;
        sep = "#";
        break;
      case "#":
      case "_":
        di = 0;
        df = 0;
        fill = c == "_" ? " " : "0";
        do {
          di++;
          i++;
        } while (format.charAt(i) === c);
        if (format.charAt(i) == "," || format.charAt(i) == ".") {
          sep = format.charAt(i);
          i++;
        } else {
          continue;
        }
        while (format.charAt(i) === c) {
          df++;
          i++;
        }
        break;
      case "°":
        if (fill === "#") {
          throw "missing format specifier";
        }
        /* If decimal places are to be rendered, use the full number.
               If this is the least significant place, use it to enable rounding */
        if (df > 0 || !format.includes("'")) {
          deg = a;
        }
        s += didf(deg, di, df, sep, fill) + "°";
        i++;
        break;
      case "'":
        if (fill === "#") {
          throw "missing format specifier";
        }
        if (!format.includes("°")) throw "malformed format: missing °";
        if (df > 0 || !format.includes('"')) {
          min = b;
        }
        s += didf(min, di, df, sep, fill) + "'";
        i++;
        break;
      case '"':
        if (fill === "#") {
          throw "missing format specifier";
        }
        if (!format.includes("'")) throw "malformed format: missing '";
        s += didf(sec, di, df, sep, fill) + '"';
        i++;
        break;
      default:
        throw "error in format string:" + c;
        break;
    }
  } while (i < format.length);
  return s;
}

/**
 * Centers the map to a location the user searched for and selected. The location is marked with a pin.
 */
function jumpToSearchedLocation(longitude, latitude) {
  /**
   *  The user has previously searched and selected a location. A marker already exists on the map for that location.
   *  I remove that marker.
   */
  if (searchedLocationMarker !== null) {
    layer_marker.getSource().clear();
  }
  // I add a market at the location the user searched for.
  searchedLocationMarker = addMarker(layer_marker, longitude, latitude);

  // I center the map at the searched location.
  jumpTo(longitude, latitude, zoom);
}

// Common utilities------------------------------------------------------------
function jumpTo(lon, lat, zoom) {
  map.getView().setCenter(ol.proj.fromLonLat([lon, lat]));
  map.getView().setZoom(zoom);
}

function getTileUrlFunction(url, type, coordinates) {
  var x = coordinates[1];
  var y = coordinates[2];
  var z = coordinates[0];
  var limit = Math.pow(2, z);
  if (y < 0 || y >= limit) {
    return null;
  } else {
    x = ((x % limit) + limit) % limit;

    path = z + "/" + x + "/" + y + "." + type;
    if (url instanceof Array) {
      url = this.selectUrl(path, url);
    }
    return url + path;
  }
}

function getTileURLMarine(url, coordinates) {
  var x = coordinates[1];
  var y = coordinates[2];
  var z = coordinates[0];
  var limit = Math.pow(2, z);
  if (y < 0 || y >= limit) {
    return null;
  } else {
    x = ((x % limit) + limit) % limit;
    url = url
      .replace("${z}", String(z))
      .replace("${y}", String(y))
      .replace("${x}", String(x));
    return url;
  }
}

const markerStyle = new ol.style.Style({
  image: new ol.style.Icon({
    src: "resources/icons/Needle_Red_32.png",
    size: [32, 32],
    anchor: [0.5, 1],
  }),
});

function addMarker(layer, lon, lat, popupContentHTML) {
  const coord = ol.proj.fromLonLat([lon, lat]);
  var feature = new ol.Feature(new ol.geom.Point(coord));
  feature.set("popupContentHTML", popupContentHTML);
  feature.setStyle(markerStyle);
  layer.getSource().addFeature(feature);
  return feature;
}

function toDeg(x) {
  return (x * 180) / Math.PI;
}

function toRad(x) {
  return (x * Math.PI) / 180;
}

function getDistance(latA, latB, lonA, lonB) {
  var dLat = toRad(latB - latA);
  var dLon = toRad(lonB - lonA);
  var lat1 = toRad(latA);
  var lat2 = toRad(latB);

  var a = Math.PI / 2 - lat2;
  var b = Math.PI / 2 - lat1;
  var c = Math.acos(
    Math.cos(a) * Math.cos(b) + Math.sin(a) * Math.sin(b) * Math.cos(dLon)
  );
  var d = km2nm(earthRadius * c);

  return d;
}

function getBearing(latA, latB, lonA, lonB) {
  var dLat = toRad(latB - latA);
  var dLon = toRad(lonB - lonA);
  var lat1 = toRad(latA);
  var lat2 = toRad(latB);

  var y = Math.sin(dLon) * Math.cos(lat2);
  var x =
    Math.cos(lat1) * Math.sin(lat2) -
    Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
  var brng = toDeg(Math.atan2(y, x));

  return (brng + 360) % 360;
}
