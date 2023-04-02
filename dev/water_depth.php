<!DOCTYPE HTML>
<html>
    <head>
        <meta name="AUTHOR" content="Olaf Hannemann">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <link rel="SHORTCUT ICON" href="resources/icons/OpenSeaMapLogo_16.png">
        <meta name="date" content="2012-10-18">
        <title>OpenSeaMap - Water depth demo</title>
        <link rel="stylesheet" href="../javascript/ol@v7.3.0/ol.css">
        <style type="text/css">
            html, body, #map {
                position: absolute;
                top: 0;
                left :0;
                width: 100%;
                height: 100%;
                margin: 0;
            }
        </style>
        <script src="../javascript/ol@v7.3.0/ol.js"></script>
        <script>
            function init() {
                window.map = new ol.Map({
                    target: 'map',
                    view: new ol.View({
                        center: ol.proj.fromLonLat([10.919, 49.131]),
                        zoom: 0,
                    }),
                });
                var osm = new ol.layer.Tile({
                    source: new ol.source.OSM({
                        url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    }),
                });
                map.addLayer(osm);
                [{
                    layers: "gebco:trackpoints_cor1",
                    minZoom: 11
                },{
                    layers: "gebco:zoom_10_cor_1_points",
                    minZoom: 10, maxZoom:11,
                },{
                    layers: "gebco:zoom_9_cor_1_points",
                    minZoom: 9, maxZoom:10,
                },{
                    layers: "gebco:zoom_8_cor_1_points",
                    minZoom: 8, maxZoom: 9,
                },{
                    layers: "gebco:zoom_7_cor_1_points",
                    minZoom: 7, maxZoom: 8,
                },{
                    layers: "gebco:zoom_6_cor_1_points",
                    minZoom: 6, maxZoom: 7,
                },{
                    layers: "gebco:zoom_5_cor_1_points",
                    minZoom: 5, maxZoom: 6,
                },{
                    layers: "gebco:zoom_4_cor_1_points",
                    minZoom: 4, maxZoom: 5,
                },{
                    layers: "gebco:zoom_3_cor_1_points",
                    minZoom: 3, maxZoom: 4,
                },{
                    layers: "gebco:zoom_2_cor_1_points",
                    minZoom: 2, maxZoom: 3,
                },{
                    layers: "gebco:contours_cor_1",
                    minZoom: 11
                },{
                    layers: "gebco:contours_zoom_10_cor_1",
                    minZoom: 10, maxZoom:11,
                },{
                    layers: "gebco:contours_zoom_9_cor_1",
                    minZoom: 9, maxZoom:10,
                },{
                    layers: "gebco:contours_zoom_8_cor_1",
                    minZoom: 8, maxZoom: 9,
                },{
                    layers: "gebco:contours_zoom_7_cor_1",
                    minZoom: 7, maxZoom: 8,
                },{
                    layers: "gebco:contours_zoom_6_cor_1",
                    minZoom: 6, maxZoom: 7,
                },{
                    layers: "gebco:contours_zoom_5_cor_1",
                    minZoom: 5, maxZoom: 6,
                },{
                    layers: "gebco:contours_zoom_4_cor_1",
                    minZoom: 4, maxZoom: 5,
                },{
                    layers: "gebco:contours_zoom_3_cor_1",
                    minZoom: 3, maxZoom: 4,
                },{
                    layers: "gebco:contours_zoom_2_cor_1",
                    minZoom: 2, maxZoom: 3,
                },].forEach(({minZoom, maxZoom, layers})=> {
                    const layer = new ol.layer.Image({
                        // maxResolution: conf.maxResolution,
                        minZoom,
                        maxZoom,
                        source: new ol.source.ImageWMS({
                            url: 'https://depth.openseamap.org:8080/geoserver/wms',
                            params: {'LAYERS': layers},
                            ratio: 1,
                            serverType: 'geoserver',
                        }),
                    });

                    map.addLayer(layer);
                })
            }
        </script>
    </head>
    <body onload="init();">
        <div id="map"></div>
    </body>
</html>
