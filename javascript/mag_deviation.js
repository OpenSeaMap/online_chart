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
let geoMag ;

function setMagdev(p) {
    // pick the right bottom corner of the map to avoid problems with interpolating
    // across the date boundary
    let latitude = (p.b+p.t)/2;
    let longitude = (p.l+p.r)/2;

    let myGeoMag = geoMag(latitude, longitude);
    let deviation = -myGeoMag.dec;
    document.getElementById('magCompassRose').style.transform = 'rotate('+deviation.toFixed(1)+'deg)';
}

// Downloads new magnetic deviation(s) from the server. This is called when the map moves.
function refreshMagdev() {
    let bounds = map.getExtent().toArray();
    let params = { "b": y2lat(bounds[1]), "t": y2lat(bounds[3]), "l": x2lon(bounds[0]), "r": x2lon(bounds[2])};

    if (geoMag == undefined) {
        function initModel(data) {
            var wmm = cof2Obj(data);
            geoMag = geoMagFactory(wmm);
            setMagdev($(this)[0]);
        }

        /* if the geomagnetic model has not been loaded yet, load it and update the deviation asynchronously */
        jQuery.ajax({
            url:"javascript/geomagjs/WMM.COF",
            context:params}).done(initModel);
    } else {
        /* else, synchronous update */
        setMagdev(params);
    }
}
