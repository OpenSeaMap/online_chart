// -----------------------------------------------------------------------------
// Bing Layer - OpenLayers class to display bing layer
//
// Written in 2013 by Dominik Fässler
//
// To the extent possible under law, the author(s) have dedicated all copyright
// and related and neighboring rights to this software to the public domain
// worldwide. This software is distributed without any warranty.
//
// You should have received a copy of the CC0 Public Domain Dedication along
// with this software. If not, see
// <http://creativecommons.org/publicdomain/zero/1.0/>.
// -----------------------------------------------------------------------------


// -----------------------------------------------------------------------------
// Description
// -----------------------------------------------------------------------------
// The zoom level changes when activating the 'Bing' layer. The goal was to
// have the same zoom level for both 'Mapnik' and 'Bing' layer.
//
// The code between 'BEGIN OVERWRITE' and 'END OVERWRITE' has been copied from
// the original sources and is subject to another license.
// -----------------------------------------------------------------------------


Bing = OpenLayers.Class(OpenLayers.Layer.Bing, {
    // BEGIN OVERWRITE
    initLayer: function() {
        var res = this.metadata.resourceSets[0].resources[0];
        var url = res.imageUrl.replace("{quadkey}", "${quadkey}");
        url = url.replace("{culture}", this.culture);
        url = url.replace(this.protocolRegex, this.protocol);
        this.url = [];
        for (var i=0; i<res.imageUrlSubdomains.length; ++i) {
            this.url.push(url.replace("{subdomain}", res.imageUrlSubdomains[i]));
        }
        this.addOptions({
            // This is a fix to get OpenSeaMap 'Mapnik' layer working
            // together with this layer.
            // http://trac.osgeo.org/openlayers/ticket/3485
            maxResolution: Math.min(
                this.serverResolutions[0],
                this.maxResolution || Number.POSITIVE_INFINITY
            ),
            numZoomLevels: Math.min(
                res.zoomMax + 1 - res.zoomMin, this.numZoomLevels
            )
        }, true);
        if (!this.isBaseLayer) {
            this.redraw();
        }
        this.updateAttribution();
    }
    // END OVERWRITE
});
