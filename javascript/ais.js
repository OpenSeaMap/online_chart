// -----------------------------------------------------------------------------
// AIS Layer - OpenLayer class to display AIS traffic
//
// Written in 2011 by Dominik Fässler
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
// This is an AIS layer. To get information about the data structure see
// 'api/getAIS.php'.
//
// If the requested range is to large, the remote service will return an empty
// result set. Zoom level 8 upwards will work.
//
// With the cluster strategy, more than one vessel within 16 pixels will
// be displayed as one object.
// -----------------------------------------------------------------------------


Ais = OpenLayers.Class(Object,{
    initialize:function(map, select, options){
        this.map = map;
        this.select = select;
        this.initLayer(options);
        this.initSelect();
    },
    initLayer:function(options){
        this.layer = new OpenLayers.Layer.Vector('Marine Traffic',
            OpenLayers.Util.applyDefaults(options,{
                visibility:false,
                maxResolution:1000,
                projection:new OpenLayers.Projection('EPSG:4326'),
                strategies:[
                    new OpenLayers.Strategy.BBOX({ratio:1}),
                    new OpenLayers.Strategy.Refresh({interval:60000, force:true}),
                    new OpenLayers.Strategy.Cluster({distance: 16, threshold: 2})
                ],
                protocol:new OpenLayers.Protocol.HTTP({
                    url:'api/getAIS.php',
                    format:new OpenLayers.Format.XML({
                        read:function(data){
                            var features = [];
                            var elements = data.getElementsByTagName('V_POS');
                            for (var i = 0; i < elements.length; i++) {
                                var node = elements[i];
                                features.push(new OpenLayers.Feature.Vector(
                                    new OpenLayers.Geometry.Point(
                                        node.getAttribute('LON'),
                                        node.getAttribute('LAT')
                                    ),{
                                        name:node.getAttribute('N'),
                                        type:node.getAttribute('T'),
                                        heading:node.getAttribute('H'),
                                        speed:node.getAttribute('S'),
                                        flag:node.getAttribute('F'),
                                        mmsi:node.getAttribute('M'),
                                        length:node.getAttribute('L'),
                                        submitted:node.getAttribute('E')
                                    }
                                ));
                            }
                            return features;
                        }
                    })
                }),
                styleMap:new OpenLayers.StyleMap({
                    'default':new OpenLayers.Style({
                        externalGraphic:'${graphic}',
                        graphicHeight:'${graphicSize}',
                        rotation:'${heading}'
                    },{
                        context:{
                            graphic:function(feature){
                                if (feature.cluster) {
                                    return 'resources/ais/cluster.png';
                                } else {
                                    if (feature.attributes.type == 1) {
                                        return 'resources/ais/navigationaids.png';
                                    } else if (feature.attributes.type > 1 || feature.attributes.speed > 0) {
                                        return 'resources/ais/ship.png';
                                    } else {
                                        return 'resources/ais/unknown.png';
                                    }
                                }
                            },
                            graphicSize:function(feature){
                                if (feature.cluster) {
                                    return 18;
                                } else {
                                    if (feature.attributes.type == 1) {
                                        return 18;
                                    } else if (feature.attributes.type > 1 || feature.attributes.speed > 0) {
                                        return 24;
                                    } else {
                                        return 16;
                                    }
                                }
                            }
                        }
                    }),
                    'select':{
                        cursor:'crosshair'
                    }
                })
            })
        );
    },
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
        this.select.removePopup();
        var feature = event.feature;
        var content = '<div class="ais">';
        if (feature.cluster) {
            content += '<h2>' + feature.attributes.count + ' ' + tr.vessels + '</h2>';
            content += '<ul>';
            for (i in feature.cluster) {
                if (feature.cluster[i].attributes === undefined) {
                    continue;
                }
                name = feature.cluster[i].attributes.name;
                if (name) {
                    content += '<li>' + name + '</li>';
                }
                if (i > 8) {
                    content += '<li>... ' + tr.andMore + '</li>';
                    break;
                }
            }
            content += '</ul>';
        } else {
            var type = '';
            switch (feature.attributes.type) {
                case '1':
                    // Navigations Aids
                    type = tr.aisType1;
                    break;
                case '3':
                    // Tug, Pilot, etc.
                    type = tr.aisType3;
                    break;
                case '4':
                    // High Speed Craft
                    type = tr.aisType4;
                    break;
                case '6':
                    // Passenger Vessel
                    type = tr.aisType6;
                    break;
                case '7':
                    // Cargo Vessel
                    type = tr.aisType7;
                    break;
                case '8':
                    // Tanker
                    type = tr.aisType8;
                    break;
                case '9':
                    // Yacht & Other
                    type = tr.aisType9;
                    break;
                default:
                    type = '--';
                    break;
            }
            var flag    = this.formatAttribute(feature.attributes.flag, '');
            var flagIco = Countries.getFlag(feature.attributes.flag);
            var flagTxt = Countries.getText(feature.attributes.flag);
            if (!flagTxt) {
                flag    = '--';
                flagTxt = '';
            } else {
                flagTxt = '(' + flagTxt + ')';
            }
            content += '<h2>' + feature.attributes.name + '</h2>';
            content += '<div><img src="resources/flags/' + flagIco + '"/>' + flag  + '&nbsp;' + flagTxt + '</div>';
            content += '<table>';
            content += '<tr><td>' + tr.type + '</td><td>' + type + '</td></tr>';
            content += '<tr><td>' + tr.course + '</td><td>' + this.formatAttribute(feature.attributes.heading, ' °') + '</td></tr>';
            content += '<tr><td>' + tr.speed + '</td><td>' + this.formatAttribute((feature.attributes.speed / 10), ' kn') + '</td></tr>';
            content += '<tr><td>MMSI</td><td>' + this.formatAttribute(feature.attributes.mmsi, '') + '</td></tr>';
            content += '<tr><td>' + tr.length + '</td><td>' + this.formatAttribute(feature.attributes.length, ' m') + '</td></tr>';
            content += '<tr><td>' + tr.submitted + '</td><td>' + this.formatAttribute(feature.attributes.submitted, tr.minBefore, true) + '</td></tr>';
            content += '</table>';
        }
        content += '</div>';
        popup = new OpenLayers.Popup.FramedCloud('ais',
            feature.geometry.getBounds().getCenterLonLat(),
            null,
            content,
            null, true, null
        );
        this.select.popup = popup;
        this.map.addPopup(popup);
        popup.updateSize();
    },
    formatAttribute:function(value, unit, replace){
        if (value) {
            if (replace) {
                return unit.replace('{0}', value);
            } else {
                return value + unit;
            }
        } else {
            return '--';
        }
    }
});
