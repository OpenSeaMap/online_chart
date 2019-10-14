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
var geoMag ;

function setMagdev(p) {
    var latitude = (p.b+p.t)/2;
    var longitude = (p.l+p.r)/2;
    myGeoMag = geoMag(latitude, longitude);
    deviation = -myGeoMag.dec;
    document.getElementById('magCompassRose').style.transform = 'rotate('+deviation.toFixed(1)+'deg)';
}

function initModel(data) {
    var wmm = cof2Obj(data);
    geoMag = geoMagFactory(wmm);
    setMagdev($(this)[0]);
}

// Downloads new magnetic deviation(s) from the server.
function refreshMagdev() {
    bounds = map.getExtent().toArray();

    var params = { "b": y2lat(bounds[1]), "t": y2lat(bounds[3]), "l": y2lat(bounds[0]), "r": y2lat(bounds[2])};

    if (geoMag == undefined) {
        /* if the geomagnetic model has not been loaded yet, load it and update the deviation asynchronously */
        jQuery.ajax({
            url:"javascript/geomagjs/WMM.COF",
            context:params}).done(initModel);
    } else {
        /* else, synchronous update */
        setMagdev(params);
    }
}
