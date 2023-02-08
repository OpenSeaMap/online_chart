// -----------------------------------------------------------------------------
// Permalink - Custom permalink and argparser
//
// Written in 2012 by Dominik Fässler
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
// The base OpenLayers.Control.Permalink can not handle dynamic layers. The
// solution was to introduce a 'layerId' for each layer. Now it is possible
// to change layer order without side effects.
//
// The 'layerId' is used to determine the position in the param string. So
// in the pattern 'BFFTFF', layers with id 1 and 4 are visible.
// -----------------------------------------------------------------------------

/*
OpenSeaMap         = OpenLayers.Class(Object, {});
OpenSeaMap.Control = OpenLayers.Class(Object, {});


OpenSeaMap.Control.ArgParser = OpenLayers.Class(OpenLayers.Control.ArgParser, {
    CLASS_NAME: 'OpenSeaMap.Control.ArgParser',
    ignoredLayers: 0,
    configureLayers: function() {
        if (this.layers.length === (this.map.layers.length + this.ignoredLayers)) {
            this.map.events.unregister('addlayer', this, this.configureLayers);

            for (var i = 0, len = this.map.layers.length; i < len; i++) {
                var layer = this.map.layers[i];
                var id = layer.layerId || 0;
                if (id < 1) {
                    // No layerId set -> ignore the layer
                    continue;
                }
                if (this.layers.length < id) {
                    // Setting for layer not in arguments
                    continue;
                }
                var c = this.layers.charAt(id-1);
                if (c == 'B') {
                    this.map.setBaseLayer(layer);
                } else if ((c == 'T') || (c == 'F')) {
                    layer.setVisibility(c == 'T');
                }
            }
        }
    }
});


OpenSeaMap.Control.Permalink = OpenLayers.Class(OpenLayers.Control.Permalink, {
    ignoredLayers: 0,
    argParserClass: OpenSeaMap.Control.ArgParser,
    setMap: function(map) {
        OpenLayers.Control.Permalink.prototype.setMap.apply(this, arguments);

        for(var i = 0, len = this.map.controls.length; i < len; i++) {
            var control = this.map.controls[i];
            if (control.CLASS_NAME === 'OpenSeaMap.Control.ArgParser') {
                control.ignoredLayers = this.ignoredLayers;
                break;
            }
        }
    },
    createParams: function(center, zoom, layers) {
        center = center || this.map.getCenter();

        var params = OpenLayers.Util.getParameters(this.base);

        if (center) {
            // Call parent method
            params = OpenLayers.Control.Permalink.prototype.createParams.apply(this, [center, zoom, layers]);

            // Override layers param
            params.layers = this.getLayerString(layers);
        }

        return params;
    },
    getLayerString: function(layers) {
        var layers = layers || this.map.layers;
        var result = '';
        for (var i = 0, len = layers.length; i < len; i++) {
            var layer = layers[i];
            var id = layer.layerId || 0;
            var flag;
            if (id < 1) {
                // No layerId set -> ignore the layer
                continue;
            }
            if (layer.isBaseLayer) {
                flag = (layer == this.map.baseLayer) ? 'B' : '0';
            } else {
                flag = (layer.getVisibility()) ? 'T' : 'F';
            }
            result = this.setFlag(result, flag, id);
        }
        return result;
    },
    setFlag: function(config, flagValue, id) {
        // The layers can be in different order
        while (config.length < id) {
            // Fill with defaults
            config = config + 'F';
        }
        return config.substr(0, id-1) + flagValue + config.substr(id);
    }
});
*/


// Create a url from the app status
function getPermalink(otherParams = {}) {
    const zoom = parseInt(map.getView().getZoom(), 10);
    const [lon, lat] = ol.proj.toLonLat(map.getView().getCenter());

    const mapLayers = map.getLayers().getArray();
    const maxLayerId = mapLayers.reduce((maxLayerId, layer) => Math.max(maxLayerId, layer.get('layerId')), 0);
    let layers = "";
    for (var i = 1; i <= maxLayerId; i += 1) {
        const layer = mapLayers.find(l => l.get('layerId') === i);
        if (!layer?.getVisible()) {
            layers += 'F';
        } else if (layer.getVisible()) {
            layers += layer.get('isBaseLayer') ? 'B' : 'T';
        }
    }

    const params = new URLSearchParams({
        zoom,
        lon: lon.toFixed(5),
        lat: lat.toFixed(5),
        layers,
        ...otherParams
    });

    return window.location.href.split('?')[0] + `?${params.toString()}`
}


class Permalink extends ol.control.Control {
    constructor() { 
      super({
        element: document.createElement('div'),
      });
      this.timeout = null;
  
      this.aElement_ = document.createElement('a');
      this.aElement_.innerHTML = 'Permalink';
      this.element.className = 'ol-permalink';
      this.element.appendChild(this.aElement_);
    }

    render() {
        // We use a timeout to avoid unnecessary updates.
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            this.aElement_.href = getPermalink();
        }, 500);
    }
}