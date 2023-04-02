"use strict";
/******************************************************************************
 Copyright 2019 Wolfgang Schildbach

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

// the geomagnetic model. This gets set up at application start
let geoMag;

function setMagdev(p) {
  // pick the right bottom corner of the map to avoid problems with interpolating
  // across the date boundary
  let latitude = (p.b + p.t) / 2;
  let longitude = (p.l + p.r) / 2;

  // get two dates exactly one year apart
  const msInYear = 1000 * 60 * 60 * 24 * 365.25;
  let now = new Date();
  let then = new Date();
  then.setTime(now.getTime() + msInYear);

  let myGeoMagNow = geoMag(latitude, longitude, 0, now);
  let myGeoMagThen = geoMag(latitude, longitude, 0, then);
  let nextyear = (then.getTime() - now.getTime()) / msInYear;
  let deviation = myGeoMagNow.dec;
  let change = (myGeoMagThen.dec - myGeoMagNow.dec) / nextyear;

  document.getElementById("magCompassRose").style.transform =
    "rotate(" + deviation.toFixed(1) + "deg)";
  // EXAMPLE
  // VAR 3.5°5'E (2015)
  // ANNUAL DECREASE 8'
  document.getElementById("magCompassTextTop").innerHTML =
    "VAR " +
    deviation.toFixed(1) +
    (deviation >= 0 ? "E" : "W") +
    " (" +
    now.getFullYear() +
    ")";
  document.getElementById("magCompassTextBottom").innerHTML =
    "ANNUAL " +
    (change >= 0 ? "INCREASE " : "DECREASE ") +
    (60 * change).toFixed(0) +
    "'";
}

// Downloads new magnetic deviation(s) from the server. This is called when the map moves.
function refreshMagdev() {
  const [minX, minY, maxX, maxY] = map.getView().calculateExtent();
  const [l, b] = ol.proj.toLonLat([minX, minY]);
  const [r, t] = ol.proj.toLonLat([maxX, maxY]);
  const params = { b, t, l, r };

  if (geoMag == undefined) {
    /* if the geomagnetic model has not been loaded yet, load it and update the deviation asynchronously */
    fetch(
      "javascript/geomagjs/WMM.COF?" +
        "b=" +
        params.b +
        "&t=" +
        params.t +
        "&l=" +
        params.l +
        "&r=" +
        params.r
    )
      .then((response) => response.text())
      .then((data) => {
        var wmm = cof2Obj(data);
        geoMag = geoMagFactory(wmm);
        setMagdev(params);
      });
  } else {
    /* else, synchronous update */
    setMagdev(params);
  }
}
