// -----------------------------------------------------------------------------
// Disaster Layer - OpenLayer class to display disaster positions
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
// Layer to display disasters. Tracks will be read from
// 'map/resources/disaster/disasters.txt'.
// -----------------------------------------------------------------------------


Disaster = OpenLayers.Class(Object,{
    initialize:function(map, select, options){
        this.map = map;
        this.select = select;
        this.initLayer(options);
        this.initSelect();
    },
    initLayer:function(options){
        this.layer = new OpenLayers.Layer.Vector('Disaster',
            OpenLayers.Util.applyDefaults(options,{
                visibility:false,
                projection:new OpenLayers.Projection('EPSG:4326'),
                strategies:[
                    new OpenLayers.Strategy.BBOX({ratio:2, resFactor:1, force:true})
                ],
                protocol:new OpenLayers.Protocol.HTTP({
                    url:'api/getDisaster.php',
                    format:new OpenLayers.Format.Text({
                        extractStyles:false,
                        read:function(text){
                            var lines = text.split('\n');
                            var features = [];
                            var attributes = {};
                            var trackPoints = [];
                            var actualName = '';
                            var link = '';
                            for (var i = 0; i < lines.length; i++) {
                                var line = lines[i];
                                var vals = line.split('\t');
                                if (vals.length <= 2) {
                                    // New vessel, but process previous vessel first
                                    if (trackPoints.length > 0) {
                                        // Track line
                                        lineArr = [];
                                        for (var j = 0; j < trackPoints.length; j++) {
                                            attributes = trackPoints[j];
                                            point = new OpenLayers.Geometry.Point(attributes.lon, attributes.lat);
                                            lineArr.push(point);
                                        }
                                        var trackLine = new OpenLayers.Geometry.LineString(lineArr);
                                        features.push(new OpenLayers.Feature.Vector(trackLine, {type:'line'}));
                                        // Track points (ignore first)
                                        for (var j = 1; j < trackPoints.length; j++) {
                                            attributes = trackPoints[j];
                                            attributes.type = 'point';
                                            point = new OpenLayers.Geometry.Point(attributes.lon, attributes.lat);
                                            features.push(new OpenLayers.Feature.Vector(point, attributes));
                                            lineArr.push(point.clone());
                                        }
                                        // Vessel
                                        attributes = trackPoints[0];
                                        attributes.type = 'actual';
                                        point = new OpenLayers.Geometry.Point(attributes.lon, attributes.lat);
                                        features.push(new OpenLayers.Feature.Vector(point, attributes));
                                    }
                                    actualName = vals[0];
                                    if (vals.length == 2) {
                                        link = vals[1];
                                    }
                                    trackPoints = [];
                                } else if (vals.length === 6) {
                                    // Tracks for vessel
                                    var attributes = {
                                        name            : actualName,
                                        link            : link,
                                        lat             : parseFloat(vals[0]),
                                        lon             : parseFloat(vals[1]),
                                        datum           : vals[2],
                                        uhrzeit         : vals[3],
                                        geschwindigkeit : vals[4],
                                        richtung        : vals[5]
                                    };
                                    trackPoints.push(attributes);
                                }
                            }
                            return features;
                        }
                    })
                }),
                styleMap:new OpenLayers.StyleMap({
                    'default':this.style,
                    'select':this.style
                })
            })
        );
    },
    style:new OpenLayers.Style({
        strokeWidth:3,
        strokeColor:'red',
    },{
        rules:[
            new OpenLayers.Rule({
                filter:new OpenLayers.Filter.Comparison({
                    type:OpenLayers.Filter.Comparison.EQUAL_TO,
                    property:'type',
                    value:'actual'
                }),
                symbolizer:{
                    externalGraphic:'resources/disaster/ship.png',
                    graphicHeight:24,
                    rotation:'${richtung}'
                }
            }),
            new OpenLayers.Rule({
                filter:new OpenLayers.Filter.Comparison({
                    type:OpenLayers.Filter.Comparison.EQUAL_TO,
                    property:'type',
                    value:'point'
                }),
                symbolizer:{
                    fillColor:'lightgray',
                    strokeWidth:1,
                    pointRadius:4
                }
            }),
            new OpenLayers.Rule({
                elseFilter:true
            })
        ]
    }),
    initSelect:function(){
        this.select.addLayer(this.layer);
        this.layer.events.on({
            featureselected:this.onFeatureSelect,
            scope:this
        });
    },
    getLayer:function(){
        return this.layer;
    },
    onFeatureSelect:function(event){
        var feature = event.feature;
        if (feature.attributes.type === 'line') {
            // Do not show popup on line
            return;
        }
        this.select.removePopup();
        var content = '<div class="disaster">';
        content += '<h2>' + feature.attributes.name + '</h2>';
        content += '<table>';
        content += '<tr><td>' + tr.date + '</td><td>' + this.formatAttribute(feature.attributes.datum, '') + '</td></tr>';
        content += '<tr><td>' + tr.time + '</td><td>' + this.formatAttribute(feature.attributes.uhrzeit, '') + '</td></tr>';
        content += '<tr><td>' + tr.latitude + '</td><td>' + this.formatLat(feature.attributes.lat) + '</td></tr>';
        content += '<tr><td>' + tr.longitude + '</td><td>' + this.formatLon(feature.attributes.lon) + '</td></tr>';
        content += '<tr><td>' + tr.speed + '</td><td>' + this.formatAttributeRound(feature.attributes.geschwindigkeit, ' kn', 1) + '</td></tr>';
        content += '<tr><td>' + tr.course + '</td><td>' + this.formatAttributeRound(feature.attributes.richtung, ' °', 0) + '</td></tr>';
        content += '<tr><td></td><td><a href="' + feature.attributes.link + '" target="_blank">Link</a></td></tr>';
        content += '</table>';
        content += '</div>';
        popup = new OpenLayers.Popup.FramedCloud('disaster',
            feature.geometry.getBounds().getCenterLonLat(),
            null,
            content,
            null, true, null
        );
        this.select.popup = popup;
        this.map.addPopup(popup);
        popup.updateSize();
    },
    formatAttribute:function(value, unit){
        if (value && value !== '#') {
            return value + unit;
        } else {
            return '--';
        }
    },
    formatAttributeRound:function(value, unit, digitsCount){
        if (value && value !== '#') {
            var prec = Math.pow(10, digitsCount);
            var value = Math.round(value * prec) / prec;
            return value.toFixed(digitsCount) + unit;
        } else {
            return '--';
        }
    },
    formatLat:function(value){
        if (!value || value == '#') {
            return '--';
        }
        return lat2DegreeMinute(value);
    },
    formatLon:function(value){
        if (!value || value == '#') {
            return '--';
        }
        return lon2DegreeMinute(value);
    }
});
