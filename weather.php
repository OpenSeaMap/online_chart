<?php
	include("../classes/Translation.php");
	include("../classes/weather.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>OpenSeaMap - <?php echo $t->tr("weather")?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>" />
		<link rel="SHORTCUT ICON" href="../resources/icons/OpenSeaMapLogo_16.png"/>
		<link rel="stylesheet" type="text/css" href="weather.css">
		<script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
		<script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
		<script type="text/javascript" src="./javascript/utilities.js"></script>
		<script type="text/javascript" src="./javascript/map_utils.js"></script>
		<script type="text/javascript">

			var map;
			var arrayMaps = new Array();

			var layer_weather_wind1;
			var layer_weather_wind2;
			var layer_weather_wind3;
			var layer_weather_wind4;
			var layer_weather_wind5;
			var layer_weather_wind6;
			var layer_weather_wind7;
			var layer_weather_wind8;

			// Position and zoomlevel of the map  (will be overriden with permalink parameters or cookies)
			var lon = 11.6540;
			var lat = 54.1530;
			var zoom = 6;

			// Load map for the first time
			function init() {
				var buffZoom = parseInt(getCookie("weather_zoom"));
				var buffLat = parseFloat(getCookie("weather_lat"));
				var buffLon = parseFloat(getCookie("weather_lon"));
				if (buffZoom != -1) {
					zoom = buffZoom;
				}
				if (buffLat != -1 && buffLon != -1) {
					lat = buffLat;
					lon = buffLon;
				}
				fillTimeDiv();
				drawmap();
			}

			// Set current language for internationalization
			OpenLayers.Lang.setCode("<?= $t->getCurrentLanguage() ?>");

			// Show popup window with the map key
			function showMapKey() {
				legendWindow = window.open("legend.php?lang=<?= $t->getCurrentLanguage() ?>", "MapKey", "width=680, height=680, status=no, scrollbars=yes, resizable=yes");
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
						new OpenLayers.Control.OverviewMap(),
						new OpenLayers.Control.Navigation()],
						maxExtent:
						new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
					numZoomLevels: 3,
					maxResolution: 156543,
					units: 'meters'
				});

				// Add Layers to map-------------------------------------------------------------------------------------------------------
				// Mapnik
				var layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
				// Wind layers
				layer_weather_wind1 = new OpenLayers.Layer.TMS("Wind12", "http://www.openportguide.org/tiles/actual/wind_vector/5/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: true, displayOutsideMaxExtent:true});
				layer_weather_wind2 = new OpenLayers.Layer.TMS("Wind18", "http://www.openportguide.org/tiles/actual/wind_vector/7/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind3 = new OpenLayers.Layer.TMS("Wind24", "http://www.openportguide.org/tiles/actual/wind_vector/9/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind4 = new OpenLayers.Layer.TMS("Wind30", "http://www.openportguide.org/tiles/actual/wind_vector/11/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind5 = new OpenLayers.Layer.TMS("Wind42", "http://www.openportguide.org/tiles/actual/wind_vector/15/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind6 = new OpenLayers.Layer.TMS("Wind54", "http://www.openportguide.org/tiles/actual/wind_vector/19/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind7 = new OpenLayers.Layer.TMS("Wind66", "http://www.openportguide.org/tiles/actual/wind_vector/23/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind8 = new OpenLayers.Layer.TMS("Wind78", "http://www.openportguide.org/tiles/actual/wind_vector/27/",
				{ numZoomLevels: 18, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});

				map.addLayers([layer_mapnik, layer_weather_wind1, layer_weather_wind2, layer_weather_wind3, layer_weather_wind4, layer_weather_wind5, layer_weather_wind6, layer_weather_wind7, layer_weather_wind8]);

				if (!map.getCenter()) {
					jumpTo(lon, lat, zoom);
				}
			}

			// Map event listener moved
			function mapEventMove(event) {
				// Set cookie for remembering lat lon values
				setCookie("weather_lat", y2lat(map.getCenter().lat).toFixed(5));
				setCookie("weather_lon", x2lon(map.getCenter().lon).toFixed(5));
			}

			// Map event listener Zoomed
			function mapEventZoom(event) {
				zoom = map.getZoom();
				// Set cookie for remembering #zoomlevel
				setCookie("weather_zoom",zoom);
			}

			function fillTimeDiv() {
				var arrayTimeValues = new Array();

				arrayTimeValues[0] = "<?=$utc->getWeatherUtc('5')?>";
				arrayTimeValues[1] = "<?=$utc->getWeatherUtc('7')?>";
				arrayTimeValues[2] = "<?=$utc->getWeatherUtc('9')?>";
				arrayTimeValues[3] = "<?=$utc->getWeatherUtc('11')?>";
				arrayTimeValues[4] = "<?=$utc->getWeatherUtc('15')?>";
				arrayTimeValues[5] = "<?=$utc->getWeatherUtc('19')?>";
				arrayTimeValues[6] = "<?=$utc->getWeatherUtc('23')?>";
				arrayTimeValues[7] = "<?=$utc->getWeatherUtc('27')?>";

				var oldDate = "00";
				var html = "<b>Zeit (UTC)</b><br/><br/>";

				for(i = 0; i < arrayTimeValues.length; i++) {
					var values = arrayTimeValues[i].split(" ");
					var layer = i + 1;
					var date = values[0];
					var time = values[1];
					if (oldDate != date) {
						if (oldDate != "00") {
							html += "</ul>";
						}
						html += "<h2>" + date + "</h2>";
						html += "<ul>";
						oldDate = date;
					}
					html += "<li onClick='setWindLayerVisible(" + layer + ")'>" + time + "</li>";
				}
				html += "<ul>";
				document.getElementById('timemenu').innerHTML=""+ html +"";
			}

			function setWindLayerVisible(number) {
				clearWindLayerVisibility();
				switch (number) {
					case 1:
						layer_weather_wind1.setVisibility(true);
						break;
					case 2:
						layer_weather_wind2.setVisibility(true);
						break;
					case 3:
						layer_weather_wind3.setVisibility(true);
						break;
					case 4:
						layer_weather_wind4.setVisibility(true);
						break;
					case 5:
						layer_weather_wind5.setVisibility(true);
						break;
					case 6:
						layer_weather_wind6.setVisibility(true);
						break;
					case 7:
						layer_weather_wind7.setVisibility(true);
						break;
					case 8:
						layer_weather_wind8.setVisibility(true);
						break;
				}
			}

			function clearWindLayerVisibility() {
				layer_weather_wind1.setVisibility(false);
				layer_weather_wind2.setVisibility(false);
				layer_weather_wind3.setVisibility(false);
				layer_weather_wind4.setVisibility(false);
				layer_weather_wind5.setVisibility(false);
				layer_weather_wind6.setVisibility(false);
				layer_weather_wind7.setVisibility(false);
				layer_weather_wind8.setVisibility(false);
			}

		</script>
	</head>
	<body onload=init();>
		<div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
		<div style="position:absolute; bottom:10px; left:12px; width:700px;">
			<img src="../resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
		</div>
		<div id="topmenu" style="position:absolute; top:10px; left:12px;">
			<ul>
				<li onClick="window.location.href='./index.php'"><IMG src="../resources/icons/OpenSeaMapLogo_88.png" width="24" height="24" align="center" border="0"><?=$t->tr("Seekarte")?></img></li>
				<li><IMG src="./resources/map/WindIcon.jpg" width="24" height="24" align="center" border="0">Wind</img></li>
			</ul>
		</div>
		<div id="timemenu" style="position:absolute; top:95px; left:12px;">
			<h4>Zeit (UTC)</h4>

		</div>
		<div id="comment" style="position:absolute; top:10px; right:12px;">
			<img src="./resources/map/WindScale.png"/>
		</div>
	</body>
</html>
