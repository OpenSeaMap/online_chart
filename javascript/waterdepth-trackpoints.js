// -----------------------------------------------------------------------------
// WaterDepth Layer - OpenLayer class to display water depths
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
// Displays depth data track points from different sources.
// -----------------------------------------------------------------------------


WaterDepthTrackPoints100m = OpenLayers.Class(Object,{
    initialize:function(map, select, options){
        this.map = map;
        this.select = select;
        this.initLayer(options);
    },
    initLayer:function(options){
        this.layer = new OpenLayers.Layer.WMS('Water Depth Track Points',
            'http://osm.franken.de/cgi-bin/mapserv.fcgi',
            {
                projection  : new OpenLayers.Projection('EPSG:900913'),
                type        : 'png',
                transparent : true,
                layers: [
                    'trackpoints_cor1_test_dbs',
                    'trackpoints_cor1_test',
                    'test_zoom_10_cor_1_points',
                    'test_zoom_9_cor_1_points',
                    'test_zoom_8_cor_1_points',
                    'test_zoom_7_cor_1_points',
                    'test_zoom_6_cor_1_points',
                    'test_zoom_5_cor_1_points',
                    'test_zoom_4_cor_1_points',
                    'test_zoom_3_cor_1_points',
                    'test_zoom_2_cor_1_points'
                ]
            },
            OpenLayers.Util.applyDefaults(options, {
                visibility  : false,
                isBaseLayer : false,
                tileSize    : new OpenLayers.Size(1024,1024)
            })
        );
    },
    getLayer:function(){
        return this.layer;
    }
});

WaterDepthTrackPoints10m = OpenLayers.Class(Object,{
    initialize:function(map, select, options){
        this.map = map;
        this.select = select;
        this.initLayer(options);
    },
    initLayer:function(options){
        this.layer = new OpenLayers.Layer.WMS('Water Depth Track Points',
            'http://osm.franken.de/cgi-bin/mapserv.fcgi',
            {
                projection  : new OpenLayers.Projection('EPSG:900913'),
                type        : 'png',
                transparent : true,
                layers: [ 'trackpoints_cor1_test_dbs_10',
                'trackpoints_cor1_test_10',
                'test_zoom_10_cor_1_points_10',
                'test_zoom_9_cor_1_points_10',
                'test_zoom_8_cor_1_points_10',
                'test_zoom_7_cor_1_points_10',
                'test_zoom_6_cor_1_points_10',
                'test_zoom_5_cor_1_points_10',
                'test_zoom_4_cor_1_points_10',
                'test_zoom_3_cor_1_points_10',
                'test_zoom_2_cor_1_points_10',
                ]
            },
            OpenLayers.Util.applyDefaults(options, {
                visibility  : false,
                isBaseLayer : false,
                tileSize    : new OpenLayers.Size(1024,1024)
            })
        );
    },
    getLayer:function(){
        return this.layer;
    }
});
