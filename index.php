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
		<link rel="SHORTCUT ICON" href="../resources/icons/OpenSeaMapLogo_32.png"/>
		<link rel="stylesheet" type="text/css" href="map-full.css">
		<script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
		<script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
		<script type="text/javascript" src="./javascript/utilities.js"></script>
		<script type="text/javascript" src="./javascript/map_utils.js"></script>
		<script type="text/javascript" src="./javascript/harbours.js"></script>
		<script type="text/javascript">

			var map;

			// Position and zoomlevel of the map  (will be overriden with permalink parameters)
			var lon = 12.0915;
			var lat = 54.1878;
			var zoom = 15;
			
			function init() {
				var buffZoom = parseInt(getCookie("zoom"));
				var buffLat = parseFloat(getCookie("lat"));
				var buffLon = parseFloat(getCookie("lon"));
				if (buffZoom != -1) {
					zoom = buffZoom;
				}
				if (buffLat != -1 && buffLon != -1) {
					lat = buffLat;
					lon = buffLon;
				}
				drawmap();
				// Add harbour layer
				//init_haefen(map, ".");
			}
			
			// Set current language for internationalization
			OpenLayers.Lang.setCode("<?= $t->getCurrentLanguage() ?>");

			// Show popup window with the map key
			function showMapKey() {
				legendWindow = window.open("legend.php?lang=<?= $t->getCurrentLanguage() ?>", "MapKey", "width=880, height=680, status=no, scrollbars=yes, resizable=yes");
 				legendWindow.focus();
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
						new OpenLayers.Control.MousePosition(),
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
				var layer_seamark = new OpenLayers.Layer.TMS("<?=$t->tr("Seezeichen")?>", "http://tiles.openseamap.org/seamark/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
				// Sport
				var layer_sport = new OpenLayers.Layer.TMS("Sport", "http://tiles.openseamap.org/sport/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});

				map.addLayers([layer_mapnik, layer_tah, layer_sport, layer_seamark]);

				if (!map.getCenter()) {
					jumpTo(lon, lat, zoom);
				}
			}

			// Map event listener moved
			function mapEventMove(event) {
				setCookie("lat", y2lat(map.getCenter().lat).toFixed(5));
				setCookie("lon", x2lon(map.getCenter().lon).toFixed(5));
			}

			// Map event listener Zoomed
			function mapEventZoom(event) {
				var zoomLevel = map.getZoom();
				setCookie("zoom", zoomLevel);
			}

		</script>
	</head>
	<body onload=init();>
		<div id="map" style="position:absolute; bottom:0px; left:0px;">
		</div>
		<div style="position:absolute; bottom:48px; left:12px; width:700px;">
			<img src="../resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
		</div>
		<div id="map_key" onClick="showMapKey()" style="position:absolute; top:10px; left:60px;">
			<?=$t->tr("Legende")?>
		</div>
	</body>
</html>
