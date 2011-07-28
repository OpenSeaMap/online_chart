<?php
	include("../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>OpenSeaMap - <?php echo $t->tr("dieFreieSeekarte")?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>" />
		<link rel="SHORTCUT ICON" href="../resources/icons/OpenSeaMapLogo_16.png"/>
		<link rel="stylesheet" type="text/css" href="map-full.css">
<<<<<<< .mine
		<link rel="stylesheet" type="text/css" href="topmenu.css">
		<link rel="stylesheet" type="text/css" href="./javascript/route/NauticalRoute.css" />
=======
		<link rel="stylesheet" type="text/css" href="topmenu.css">
>>>>>>> .r427
		<script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
		<script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
		<script type="text/javascript" src="./javascript/utilities.js"></script>
		<script type="text/javascript" src="./javascript/map_utils.js"></script>
		<script type="text/javascript" src="./javascript/harbours.js"></script>
<<<<<<< .mine
		<script type="text/javascript" src="./javascript/nominatim.js"></script>
		<script type="text/javascript" src="./javascript/tidal_scale.js"></script>
		<script type="text/javascript" src="./javascript/route/NauticalRoute.js"></script>
		<script type="text/javascript" src="./javascript/mouseposition_dm.js"></script>
		<script type="text/javascript" src="./javascript/grid_wgs.js"></script>
=======
<!--
		<script type="text/javascript" src="./javascript/jquery-1.5.2.min.js"></script>
		<script type="text/javascript">
			jQuery.noConflict();
		</script>
		<script type="text/javascript" src="./javascript/jquery.dropdownPlain.js"></script>
/-->
		<script type="text/javascript" src="./javascript/nominatim.js"></script>
		<!--<script type="text/javascript" src="./javascript/tidal_scale.js"></script>-->
>>>>>>> .r427
		<script type="text/javascript">

			var map;
			var nauticalRoute;
			var arrayMaps = new Array();

			// Position and zoomlevel of the map  (will be overriden with permalink parameters or cookies)
			var lon = 11.6540;
			var lat = 54.1530;
			var zoom = 10;

			// marker position
			var mlon = -1;
			var mlat = -1;

			//last zoomlevel of the map
			var oldZoom = 0;

			var downloadName;
			var downloadLink;
			var downloadLoaded = false;

			// FIXME: Work around for accessing translations from harbour.js
			var linkTextSkipperGuide = "<?=$t->tr('descrSkipperGuide')?>";
			var linkTextWeatherHarbour = "<?=$t->tr('descrOpenPortGuide')?>";
			// FIXME: Work around for accessing translations from tidal_scale.js
			var linkTextHydrographCurve = "<?=$t->tr('hydrographCurve')?>";
			var linkTextWikiHelp = "<?=$t->tr('wikiHelp')?>";
			var linkTextMeasuringValue = "<?=$t->tr('measuringValue')?>";
			var linkTextTendency = "<?=$t->tr('tendency')?>";

			// Set language
			var language = "<?=$t->getCurrentLanguage()?>";

			// Layers
			var layer_seamark;
			var layer_download;
			var layer_tidal_scale;
			var layer_sport;
			var layer_gebco_deepshade
			var layer_gebco_contours
			var layer_gebco_deeps_wms
			var layer_gebco_deeps_gwc

			// Load map for the first time
			function init() {
				var buffZoom = parseInt(getCookie("zoom"));
				var buffLat = parseFloat(getCookie("lat"));
				var buffLon = parseFloat(getCookie("lon"));
				mlat = getArgument("mlat");
				mlon = getArgument("mlon");

				if (buffZoom != -1) {
					zoom = buffZoom;
				}
				if (buffLat != -1 && buffLon != -1) {
					lat = buffLat;
					lon = buffLon;
				}
				drawmap();
				// Create Marker, if arguments are given
				if (mlat != -1 && mlon != -1) {
					var layer_marker = new OpenLayers.Layer.Markers("Marker");
					map.addLayer(layer_marker);
					addMarker(layer_marker, mlon, mlat, convert2Text(getArgument("mtext")));
				}
				// Set Layer visibility from cookie
				if (getCookie("SeamarkLayerVisible") == "false") {
					layer_seamark.setVisibility(false);
				} else {
					document.getElementById("checkLayerSeamark").checked = true;
				}
				if (getCookie("HarbourLayerVisible") == "false") {
					layer_harbours.setVisibility(false);
				} else {
					document.getElementById("checkLayerHarbour").checked = true;
				}
				if (layer_download.visibility) {
					addMapDownload();
				}
				if (getCookie("TidalScaleLayerVisible") == "true") {
					layer_tidal_scale.setVisibility(true);
					document.getElementById("checkLayerTidalScale").checked = true;
					refreshTidalScales();
				}
				if (getCookie("SportLayerVisible") == "true") {
					layer_sport.setVisibility(true);
					document.getElementById("checkLayerSport").checked = true;
				}
				if (getCookie("GridWGSLayerVisible") == "true") {
					layer_grid.setVisibility(true);
					document.getElementById("checkLayerGridWGS").checked = true;
				}
			}

			// Set current language for internationalization
			OpenLayers.Lang.setCode(language);

			// Show popup window for help
			function showMapKey(item) {
				legendWindow = window.open("legend.php?lang=" + language + "&page=" + item, "MapKey", "width=760, height=680, status=no, scrollbars=yes, resizable=yes");
 				legendWindow.focus();
			}

			function showSeamarks() {
				if (layer_seamark.visibility) {
					layer_seamark.setVisibility(false);
					document.getElementById("checkLayerSeamark").checked = false;
					setCookie("SeamarkLayerVisible", "false");
				} else {
					layer_seamark.setVisibility(true);
					document.getElementById("checkLayerSeamark").checked = true;
					setCookie("SeamarkLayerVisible", "true");
				}
			}

			function showHarbours() {
				if (layer_harbours.visibility) {
					layer_harbours.setVisibility(false);
					document.getElementById("checkLayerHarbour").checked = false;
					setCookie("HarbourLayerVisible", "false");
				} else {
					layer_harbours.setVisibility(true);
					document.getElementById("checkLayerHarbour").checked = true;
					setCookie("HarbourLayerVisible", "true");
				}
			}

			function showTidalScale() {
				if (layer_tidal_scale.visibility) {
					layer_tidal_scale.setVisibility(false);
					document.getElementById("checkLayerTidalScale").checked = false;
					setCookie("TidalScaleLayerVisible", "false");
				} else {
					layer_tidal_scale.setVisibility(true);
					document.getElementById("checkLayerTidalScale").checked = true;
					setCookie("TidalScaleLayerVisible", "true");
					refreshTidalScales();
				}
			}

			// Show route section
			function showNauticalRoute() {
				nauticalRoute.toggle();
				// FIXME hack for setting check in the menu. Better create a callback from nauticalRoute. 
				if (document.getElementById("checkNauticalRoute").checked) {
					document.getElementById("checkNauticalRoute").checked = false;
				} else {
					document.getElementById("checkNauticalRoute").checked = true;
				}
			}

			function showSport() {
				if (layer_sport.visibility) {
					layer_sport.setVisibility(false);
					document.getElementById("checkLayerSport").checked = false;
					setCookie("SportLayerVisible", "false");
				} else {
					layer_sport.setVisibility(true);
					document.getElementById("checkLayerSport").checked = true;
					setCookie("SportLayerVisible", "true");
				}
			}

			function showGridWGS() {
				if (layer_grid.visibility) {
					layer_grid.setVisibility(false);
					document.getElementById("checkLayerGridWGS").checked = false;
					setCookie("GridWGSLayerVisible", "false");
				} else {
					layer_grid.setVisibility(true);
					document.getElementById("checkLayerGridWGS").checked = true;
					setCookie("GridWGSLayerVisible", "true");
				}
			}

			function showGebcoDepth() {
				if (layer_gebco_deepshade.visibility) {
					layer_gebco_deepshade.setVisibility(false);
					/*layer_gebco_contours.setVisibility(false);
					layer_gebco_deeps_wms.setVisibility(false);
					layer_gebco_deeps_gwc.setVisibility(false);*/
					document.getElementById("checkLayerGebcoDepth").checked = false;
				} else {
					layer_gebco_deepshade.setVisibility(true);
					/*layer_gebco_contours.setVisibility(true);
					layer_gebco_deeps_wms.setVisibility(true);
					layer_gebco_deeps_gwc.setVisibility(true);*/
					document.getElementById("checkLayerGebcoDepth").checked = true;
				}
			}

			// Show Download section
			function showMapDownload() {
				if (!downloadLoaded) {
					addMapDownload();
					document.getElementById("checkDownload").checked = true;
				} else {
					closeMapDownload();
					document.getElementById("checkDownload").checked = false;
				}
			}

			function addMapDownload() {
				addDownloadlayer();

				layer_download.setVisibility(true);
				document.getElementById("downloadmenu").style.visibility = 'visible';
				downloadLoaded = true;
			}

			function closeMapDownload() {
				layer_download.setVisibility(false);
				layer_download.removeAllFeatures();
				document.getElementById("downloadmenu").style.visibility = 'hidden';
				downloadLoaded = false;
			}

			function downloadMap() {
				var format = document.getElementById("mapFormat").value;

				if (format == "unknown") {
					alert("Bitte wählen sie ein Format.");
					return;
				} else if (format == "cal") {
					format = "_png." + format
				} else {
					format = "." + format
				}
				var url = "http://sourceforge.net/projects/openseamap/files/Maps" + downloadLink + "OSeaM-" + downloadName + format + "/download";
				
				downloadWindow = window.open(url);
			}

			function selectedMap (evt) {
				var selectedMap = evt.feature.id.split(".");
				var buff = arrayMaps[selectedMap[2].split("_")[1]].split(":");

				downloadName = buff[0];
				downloadLink = buff[1];

				var mapName =downloadName;

				document.getElementById('info_dialog').innerHTML=""+ mapName +"";
				document.getElementById('buttonMapDownload').disabled=false;
			}

			function drawmap() {
				map = new OpenLayers.Map('map', {
					projection: projMerc,
					displayProjection: proj4326,
					eventListeners: {
						"moveend": mapEventMove,
						"zoomend": mapEventZoom
					},
					controls: [
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.Navigation(),
						new OpenLayers.Control.ScaleLine({topOutUnits : "nmi", bottomOutUnits: "km", topInUnits: 'nmi', bottomInUnits: 'km', maxWidth: '40'}),
						new OpenLayers.Control.LayerSwitcher(),
						new OpenLayers.Control.MousePositionDM(),
						new OpenLayers.Control.OverviewMap(),
						new OpenLayers.Control.PanZoomBar()],
						maxExtent:
						new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
					numZoomLevels: 18,
					maxResolution: 156543,
					units: 'meters'
				});

				// Add Layers to map-------------------------------------------------------------------------------------------------------
				// Mapnik
				var layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
				// Osmarender
				var layer_tah = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
				// Seamark
				layer_seamark = new OpenLayers.Layer.TMS("<?=$t->tr("Seezeichen")?>", "http://tiles.openseamap.org/seamark/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
				// Sport
				layer_sport = new OpenLayers.Layer.TMS("Sport", "http://tiles.openseamap.org/sport/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_gebco_deepshade = new OpenLayers.Layer.WMS("deepshade",
					"http://osm.franken.de:8080/geoserver/gwc/demo/gebco_new?",
					{layers: "gebco:deepshade", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
					{isBaseLayer: false, visibility: false, opacity: 0.2, minResolution: 38.22});
				/*layer_gebco_contours = new OpenLayers.Layer.WMS("contours",
					"http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:contour", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
					{isBaseLayer: false, visibility: false, maxResolution: 76.44});
				layer_gebco_deeps_wms = new OpenLayers.Layer.WMS(
					"deeps_wms",
					"http://osm.franken.de:8080/geoserver/gwc/service/wms",
					{'layers': 'gebco:deeps', 'format':'image/jpeg', 'transparent':'true'},
					{'opacity': 0.4, 'isBaseLayer': false, 'visibility': false, maxResolution: 76.44});
				layer_gebco_deepshade = new OpenLayers.Layer.WMS(
					"deeps_gwc",
					"http://osm.franken.de:8080/geoserver/gwc/demo/gebco_new?",
					{'layers': 'gebco:deeps', 'format':'image/jpeg'},
					{'opacity': 0.4, 'isBaseLayer': false, 'visibility': false, numZoomLevels: 11});*/
				// Harbours
				layer_harbours = new OpenLayers.Layer.Markers("<?=$t->tr("harbours")?>",
				{ projection: new OpenLayers.Projection("EPSG:4326"), visibility: true, displayOutsideMaxExtent:true});
				layer_harbours.setOpacity(0.8);
				// Tidal Scales
				layer_tidal_scale = new OpenLayers.Layer.Markers("Pegel",
				{ projection: new OpenLayers.Projection("EPSG:4326"), visibility: false, displayOutsideMaxExtent:true});
				layer_tidal_scale.setOpacity(0.8);
				// Map download
				layer_download = new OpenLayers.Layer.Vector("Map Download", {visibility: false});
				// Grid WGS
				layer_grid = new OpenLayers.Layer.GridWGS("<?=$t->tr("coordinateGrid")?>", {visibility: false, zoomUnits: zoomUnits})

				map.addLayers([layer_mapnik, layer_tah, layer_seamark, layer_gebco_deepshade, layer_grid, layer_harbours, layer_tidal_scale, layer_download, layer_sport]);

				if (!map.getCenter()) {
					jumpTo(lon, lat, zoom);
				}

				// Add nautical route tool
				nauticalRoute = new OpenLayers.Control.NauticalRoute({defaultHidden: true, exportUrl: 'export.php'});
				map.addControl(nauticalRoute);

				// Add download tool
				var selectDownload = new OpenLayers.Control.SelectFeature(layer_download);
				map.addControl(selectDownload);
				selectDownload.activate();
				layer_download.events.register("featureselected", layer_download, selectedMap);
			}

			// Map event listener moved
			function mapEventMove(event) {
				// Set cookie for remembering lat lon values
				setCookie("lat", y2lat(map.getCenter().lat).toFixed(5));
				setCookie("lon", x2lon(map.getCenter().lon).toFixed(5));
				// Update harbour layer
				if (layer_harbours.visibility) {
					refreshHarbours();
				}
				// Update tidal scale layer
				if (layer_tidal_scale.visibility) {
					refreshTidalScales();
				}
			}

			// Map event listener Zoomed
			function mapEventZoom(event) {
				zoom = map.getZoom();
				// Set cookie for remembering zoomlevel
				setCookie("zoom",zoom);

				if(oldZoom!=zoom) {
					document.getElementById('zoomlevel').innerHTML="Zoom: "+ zoom +"";
					ensureHarbourVisibility(zoom);
					oldZoom=zoom;
				}
				if (downloadLoaded) {
					closeMapDownload();
					addMapDownload();
				}
			}

			function addDownloadlayer() {

				var xmlDoc=loadXMLDoc("./gml/map_download.xml");
				try {
					var root = xmlDoc.getElementsByTagName("maps")[0];
					var items = root.getElementsByTagName("map");
				} catch(e) {
					alert("Error (root): "+ e);
					return -1;
				}
				for (var i=0; i < items.length; ++i) {
					//alert(i);
					var item = items[i];
					var load = false;
					var category =item.getElementsByTagName("category")[0].childNodes[0].nodeValue;

					if (zoom <= 7 && category >= 2) {
						load = true;
					} else if (zoom <= 10 && category >= 4) {
						load = true;
					} else if (zoom <= 13 && category >= 6) {
						load = true;
					} else if (zoom <= 18 && category >= 7) {
						load = true;
					}

					if (load) {
						try {
							var n = item.getElementsByTagName("north")[0].childNodes[0].nodeValue;
							var s = item.getElementsByTagName("south")[0].childNodes[0].nodeValue;
							var e = item.getElementsByTagName("east")[0].childNodes[0].nodeValue;
							var w = item.getElementsByTagName("west")[0].childNodes[0].nodeValue;
						} catch(e) {
							alert("Error (load): "+ e);
							return -1;
						}
						var bounds = new OpenLayers.Bounds(w, s, e, n);
						bounds.transform(new OpenLayers.Projection("EPSG:4326"), new
						OpenLayers.Projection("EPSG:900913"));
						var box = new OpenLayers.Feature.Vector(bounds.toGeometry());
						layer_download.addFeatures(box);
						var name = item.getElementsByTagName("name")[0].childNodes[0].nodeValue.trim();
						var link = item.getElementsByTagName("link")[0].childNodes[0].nodeValue.trim();
						arrayMaps[box.id.split("_")[1]] = name + ":" + link;
						//alert(link);
					}
				}
			}

		</script>
	</head>
	<body onload=init();>
		<div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
		<div id="layerswitcher"></div>
		<div id="zoomlevel" style="position:absolute; bottom:0px; right:130px; z-index:2;">Zoom: 15</div>
		<div style="position:absolute; bottom:48px; left:12px; width:700px;">
			<img src="../resources/icons/OSM-Logo-32px.png" height="32px" title="<?=$t->tr("SomeRights")?>" onClick="showMapKey('license')"/>
			<img src="../resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="showMapKey('license')"/>
		</div>
<<<<<<< .mine
		<? include('../classes/topmenu.php'); ?>
=======
<? include('topmenu.php'); ?>

>>>>>>> .r427
		<div id="downloadmenu" style="position:absolute; top:50px; left:60px; visibility:hidden;">
			<b><?=$t->tr("downloadChart")?></b><br/><br/>
			<table border="0" width="100%">
				<tr>
					<td>
						Name:
					</td>
					<td>
						<div id="info_dialog">&nbsp;<?=$t->tr("pleaseSelect")?><br/></div>
					</td>
				</tr>
				<tr>
					<td>
						Format:
					<td>
						<select id="mapFormat">
							<option value="unknown"/><?=$t->tr("unknown")?>
							<option value="png"/>png
							<option value="cal"/>cal
							<option value="kap"/>kap
							<option value="WCI"/>WCI
							<option value="kmz"/>kmz
							<option value="jpr"/>jpr
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<br/>
						<input type="button" id="buttonMapDownload" value="<?=$t->tr("download")?>" onclick="downloadMap()" disabled="true">
					</td>
					<td align="right">
						<br/>
						<input type="button" id="buttonMapClose" value="<?=$t->tr("close")?>" onclick="closeMapDownload()">
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
