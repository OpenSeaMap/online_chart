/******************************************************************************
 Copyright 2008 - 2011 Xavier Le Bourdon, Christoph Böhme, Mitja Kleider

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
 Version 0.1.5  03.10.2011
 ******************************************************************************/

function getPopupContentHTML(harbour) {
  let { id, lon, lat, name, link, type } = harbour;
  var names = name.split("-");
  var popupText = "<b>" + names[0] + "</b>&nbsp;&nbsp;&nbsp;&nbsp;<br/>";
  if (typeof names[1] != "undefined") {
    popupText += names[1];
  }
  if (typeof names[2] != "undefined") {
    popupText += "<br/><i>" + names[2] + "</i>";
  }
  if (link != "") {
    popupText +=
      "<br/><br/><a href='" +
      link +
      "' target='blank'>" +
      linkTextSkipperGuide +
      "</a>";
  }
  popupText +=
    "<br/><a href='https://weather.openportguide.de/cgi-bin/weather.pl/weather.png?var=meteogram&nx=614&ny=750&lat=" +
    lat +
    "&lon=" +
    lon +
    "&lang=" +
    language +
    "&unit=metric&label=" +
    convert2Locode(names[0]) +
    "' target='blank'>" +
    linkTextWeatherHarbour +
    "</a>";
  return popupText;
}

function isWPI(type) {
  /**********************************
    1 = L
    2 = M
    3 = S
    4 = V (Very small)
    5 = Marina (representative skipperguide)
    6 = Anchorage (representative skipperguide)
    7 = other descr. skipperguide
    ***********************************/
  return type <= 4;
}

function determineHarbourType(harbour, harboursById) {
  const myName = harbour.name.split("-")[0];
  const harbours = Object.values(harboursById);
  for (var i in harbours) {
    var otherName = harbours[i].name.split("-");
    if (myName == otherName[0]) {
      return 6;
    }
  }
  return 5;
}

function getHarbourVisibility(zoom) {
  var maxType = 1;
  if (zoom >= 7) {
    maxType = 2;
  }
  if (zoom >= 8) {
    maxType = 3;
  }
  if (zoom >= 9) {
    maxType = 4;
  }
  if (zoom >= 10) {
    maxType = 6;
  }
  return maxType;
}

const marinaStyle = new ol.style.Style({
  image: new ol.style.Icon({
    src: "resources/places/marina_32.png",
    imgSize: [32, 32],
    size: [32, 32],
    scale: 24 / 32,
  }),
});

const harbourStyle = new ol.style.Style({
  image: new ol.style.Icon({
    src: "resources/places/harbour_32.png",
    imgSize: [32, 32],
    size: [32, 32],
    scale: 24 / 32,
  }),
});

const anchorageStyle = new ol.style.Style({
  image: new ol.style.Icon({
    src: "resources/places/anchorage_32.png",
    imgSize: [32, 32],
    size: [32, 32],
    scale: 24 / 32,
  }),
});

// StyleFunction used by the ol.layer.Vector
function harbourStyleFunction(feature, resolution) {
  const zoom = map.getView().getZoomForResolution(resolution);
  const type = feature.get("type");
  const maxType = getHarbourVisibility(zoom);
  let style = null;
  if (type <= maxType) {
    style = marinaStyle;
    if (isWPI(type)) {
      style = harbourStyle;
    } else if (type == 6) {
      style = anchorageStyle;
    }
  }

  return style;
}

// Loader used by the ol.source.Vector
let callCount = 0;
let harboursById = {};
function harbourSourceLoader(extent, resolution, projection, success, failure) {
  const proj = projection.getCode();
  const bbox = ol.proj.transformExtent(
    extent,
    map.getView().getProjection(),
    "EPSG:4326"
  );
  const params = new URLSearchParams({
    b: bbox[1].toFixed(5),
    t: bbox[3].toFixed(5),
    l: bbox[0].toFixed(5),
    r: bbox[2].toFixed(5),
    ucid: callCount++,
    maxSize: getHarbourVisibility(zoom),
    zoom: zoom,
  });

  // CORS errors
  const url =
    "/api/proxy.php?method=GET&cors=https://harbours.openseamap.org/getHarbours.php&" +
    params.toString();

  const xhr = new XMLHttpRequest();
  xhr.open("GET", url);
  const vectorSource = this;
  const onError = function () {
    vectorSource.removeLoadedExtent(extent);
    failure();
  };
  xhr.onerror = onError;
  xhr.onload = function () {
    if (xhr.status == 200) {
      // Parse JSONP response into ol features
      let text = xhr.responseText;
      text = text.replace(/(\r|\n)/g, "");
      text = text.replace(/putHarbourMarker\(/g, "");
      text = text.replace(/\);/g, ";");
      harboursById = {};
      const features = text
        .split(";")
        .filter((str) => str)
        .map((str) => {
          const [id, lon, lat, name, link, type] = str.split(", ");
          const harbour = {
            id: parseInt(id, 10),
            lon: parseFloat(lon),
            lat: parseFloat(lat),
            name: name?.replace(/'/g, ""),
            link: link?.replace(/'/g, ""),
            type: parseInt(type, 10),
          };

          // Determine a type when type = -1;
          if (harbour.type === -1) {
            harbour.type = determineHarbourType(harbour, harboursById);
          }

          harboursById[harbour.id] = harbour;

          // Generate popup content
          harbour.popupContentHTML = getPopupContentHTML(harbour);

          // Build the feature
          const feature = new ol.Feature(
            new ol.geom.Point(ol.proj.fromLonLat([harbour.lon, harbour.lat]))
          );
          feature.setProperties(harbour);
          return feature;
        });
      vectorSource.clear(true);
      vectorSource.addFeatures(features);
      success(features);
    } else {
      onError();
    }
  };
  xhr.send();
}
