<!DOCTYPE HTML>
<html>
	<head>
		<meta name="AUTHOR" content="Olaf Hannemann">
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<link rel="SHORTCUT ICON" href="../resources/icons/OpenSeaMapLogo_16.png">
		<meta name="date" content="2012-10-18">
		<title>OpenSeaMap - Water depth demo</title>
		<style type="text/css">
			html, body, #basicMap {
				width: 100%;
				height: 100%;
				margin: 0;
				.olControlAttribution {bottom: 2px!important; right:5px!important; }
				div.olControlMousePosition {background-color:#efefef; color:#000000;bottom: 2px!important; left:5px!important;}
				div.olControlZoomStatus {background-color:#efefef; color:#000000;bottom: 2px!important; left:5px!important;}
			}
		</style>
		<style>
			.olControlAttribution {bottom: 20px!important; left:15px!important; }
			div.olControlMousePosition {color:#000000; font:bold .9em Times!important; bottom: 40px!important; left:15px!important;}
		</style>
		<script src="http://map.openseamap.org/map/javascript/openlayers/OpenLayers.js"></script>
		<script>
			function init() {
				// World Geodetic System 1984 projection
				var WGS84 = new OpenLayers.Projection("EPSG:4326");
				// WGS84 Google Mercator projection
				var WGS84_google_mercator = new OpenLayers.Projection("EPSG:900913");

				var world = new OpenLayers.Bounds(-180, -89, 180, 89).transform(
					WGS84, WGS84_google_mercator
				);

				var map = new OpenLayers.Map ("basicMap", {
					controls:[
						//koordinatengitter
						new OpenLayers.Control.Graticule(),
						//zeigt alle zoom level
						new OpenLayers.Control.PanZoomBar(),
						//new OpenLayers.Control.MouseToolbar(),
						new OpenLayers.Control.ArgParser(),
						new OpenLayers.Control.LayerSwitcher(),
						// zeigt cc-bx-sa osm an
						new OpenLayers.Control.Attribution(),
						new OpenLayers.Control.Navigation(),
						//new OpenLayers.Control.PanZoom(),
						new OpenLayers.Control.MousePosition(),
						new OpenLayers.Control.KeyboardDefaults()
					],
					projection: WGS84_google_mercator,
					displayProjection: WGS84,
					units: "m",
					maxExtent: world,
					maxResolution: 156543.0399
				} );
				var mapnik = new OpenLayers.Layer.OSM();
				var trackpoints = new OpenLayers.Layer.WMS("trackpoints",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:trackpoints_cor1", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
					{isBaseLayer: false, maxResolution: 76.44});
					// zoom 10 cor points 1
					var zoom_10_cor_points_1 = new OpenLayers.Layer.WMS("zoom_10_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_10_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 76.44, maxResolution: 152.88});
					// zoom 9 cor points 1
					var zoom_9_cor_points_1 = new OpenLayers.Layer.WMS("zoom_9_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_9_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 152.88, maxResolution: 305.75});
					// zoom 8 cor points 1
					var zoom_8_cor_points_1 = new OpenLayers.Layer.WMS("zoom_8_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_8_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 305.75, maxResolution: 611.5});
					// zoom 7 cor points 1
					var zoom_7_cor_points_1 = new OpenLayers.Layer.WMS("zoom_7_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_7_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 611.5, maxResolution: 1223.0});
					// zoom 6 cor points 1
					var zoom_6_cor_points_1 = new OpenLayers.Layer.WMS("zoom_6_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_6_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 1223.0, maxResolution: 2446.0});
					// zoom 5 cor points 1
					var zoom_5_cor_points_1 = new OpenLayers.Layer.WMS("zoom_5_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_5_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 2446.0, maxResolution: 4892.0});
					// zoom 4 cor points 1
					var zoom_4_cor_points_1 = new OpenLayers.Layer.WMS("zoom_4_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_4_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 4892.0, maxResolution: 9784.0});
					// zoom 3 cor points 1
					var zoom_3_cor_points_1 = new OpenLayers.Layer.WMS("zoom_3_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_3_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 9784.0, maxResolution: 19568.0});
					// zoom 2 cor points 1
					var zoom_2_cor_points_1 = new OpenLayers.Layer.WMS("zoom_2_cor_points_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:zoom_2_cor_1_points", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 19568.0, maxResolution: 39136.0});

					// contours
					var contours = new OpenLayers.Layer.WMS("contours",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_cor_1", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
					{isBaseLayer: false, maxResolution: 76.44});
					// contours 10 cor 1
					var contours_zoom_10_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_10_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_10_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 76.44, maxResolution: 152.88});
					// contours 9 cor 1
					var contours_zoom_9_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_9_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_9_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 152.88, maxResolution: 305.75});
					// contours 8 cor 1
					var contours_zoom_8_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_8_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_8_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 305.75, maxResolution: 611.5});
					// contours 7 cor 1
					var contours_zoom_7_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_7_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_7_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 611.5, maxResolution: 1223.0});
					// contours 6 cor 1
					var contours_zoom_6_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_6_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_6_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 1223.0, maxResolution: 2446.0});
					// contours 5 cor 1
					var contours_zoom_5_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_5_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_5_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 2446.0, maxResolution: 4892.0});
					// contours 4 cor 1
					var contours_zoom_4_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_4_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_4_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 4892.0, maxResolution: 9784.0});
					// contours 3 cor 1
					var contours_zoom_3_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_3_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_3_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 9784.0, maxResolution: 19568.0});
					// contours 2 cor 1
					var contours_zoom_2_cor_1 = new OpenLayers.Layer.WMS("contours_zoom_2_cor_1",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contours_zoom_2_cor_1", projection: new OpenLayers.Projection("EPSG:4326"), type: 'png', transparent: true},
					{isBaseLayer: false, minResolution: 19568.0, maxResolution: 39136.0});

					map.addLayers([ mapnik, trackpoints, zoom_2_cor_points_1,zoom_3_cor_points_1,zoom_4_cor_points_1,zoom_5_cor_points_1,zoom_6_cor_points_1,zoom_7_cor_points_1,zoom_8_cor_points_1,zoom_9_cor_points_1, zoom_10_cor_points_1, contours, contours_zoom_10_cor_1,contours_zoom_9_cor_1,contours_zoom_8_cor_1,contours_zoom_7_cor_1,contours_zoom_6_cor_1,contours_zoom_5_cor_1,contours_zoom_4_cor_1,contours_zoom_3_cor_1,contours_zoom_2_cor_1]);
					map.setCenter(new OpenLayers.LonLat(10.919, 49.131) // Center of the map
					.transform(
						new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
						new OpenLayers.Projection("EPSG:900913") // to Spherical Mercator Projection
					), 14 
				);
			}
		</script>
	</head>
	<body onload="init();">
		<div id="basicMap"></div>
	</body>
</html>
