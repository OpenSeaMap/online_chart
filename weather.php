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

			// Wind layers
			var layer_weather_wind1;
			var layer_weather_wind2;
			var layer_weather_wind3;
			var layer_weather_wind4;
			var layer_weather_wind5;
			var layer_weather_wind6;
			var layer_weather_wind7;
			var layer_weather_wind8;
			// Air pressure layers
			var layer_weather_pressure1;
			var layer_weather_pressure2;
			var layer_weather_pressure3;
			var layer_weather_pressure4;
			var layer_weather_pressure5;
			var layer_weather_pressure6;
			var layer_weather_pressure7;
			var layer_weather_pressure8;
			// Temperature layers
			var layer_weather_air_temperature1;
			var layer_weather_air_temperature2;
			var layer_weather_air_temperature3;
			var layer_weather_air_temperature4;
			var layer_weather_air_temperature5;
			var layer_weather_air_temperature6;
			var layer_weather_air_temperature7;
			var layer_weather_air_temperature8;

			var layerNumber = 1;

			var showWindLayer = false;
			var showPressureLayer = false;
			var showAirTemperatureLayer = false;

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
				showWind();
				document.getElementById("timeLayer1").style.background = "#ADD8E6";
				document.getElementById("checkPressure").checked = false;
				document.getElementById("checkAirTemperature").checked = false;
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
						new OpenLayers.Control.Navigation({zoomWheelEnabled: false})],
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
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind2 = new OpenLayers.Layer.TMS("Wind18", "http://www.openportguide.org/tiles/actual/wind_vector/7/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind3 = new OpenLayers.Layer.TMS("Wind24", "http://www.openportguide.org/tiles/actual/wind_vector/9/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind4 = new OpenLayers.Layer.TMS("Wind30", "http://www.openportguide.org/tiles/actual/wind_vector/11/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind5 = new OpenLayers.Layer.TMS("Wind42", "http://www.openportguide.org/tiles/actual/wind_vector/15/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind6 = new OpenLayers.Layer.TMS("Wind54", "http://www.openportguide.org/tiles/actual/wind_vector/19/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind7 = new OpenLayers.Layer.TMS("Wind66", "http://www.openportguide.org/tiles/actual/wind_vector/23/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_wind8 = new OpenLayers.Layer.TMS("Wind78", "http://www.openportguide.org/tiles/actual/wind_vector/27/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				// Air pressure layers
				layer_weather_pressure1 = new OpenLayers.Layer.TMS("Wind12", "http://www.openportguide.org/tiles/actual/surface_pressure/5/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure2 = new OpenLayers.Layer.TMS("Wind18", "http://www.openportguide.org/tiles/actual/surface_pressure/7/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure3 = new OpenLayers.Layer.TMS("Wind24", "http://www.openportguide.org/tiles/actual/surface_pressure/9/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure4 = new OpenLayers.Layer.TMS("Wind30", "http://www.openportguide.org/tiles/actual/surface_pressure/11/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure5 = new OpenLayers.Layer.TMS("Wind42", "http://www.openportguide.org/tiles/actual/surface_pressure/15/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure6 = new OpenLayers.Layer.TMS("Wind54", "http://www.openportguide.org/tiles/actual/surface_pressure/19/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure7 = new OpenLayers.Layer.TMS("Wind66", "http://www.openportguide.org/tiles/actual/surface_pressure/23/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_pressure8 = new OpenLayers.Layer.TMS("Wind78", "http://www.openportguide.org/tiles/actual/surface_pressure/27/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				// Temperature layers
				layer_weather_air_temperature1 = new OpenLayers.Layer.TMS("Wind12", "http://www.openportguide.org/tiles/actual/air_temperature/5/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature2 = new OpenLayers.Layer.TMS("Wind18", "http://www.openportguide.org/tiles/actual/air_temperature/7/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature3 = new OpenLayers.Layer.TMS("Wind24", "http://www.openportguide.org/tiles/actual/air_temperature/9/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature4 = new OpenLayers.Layer.TMS("Wind30", "http://www.openportguide.org/tiles/actual/air_temperature/11/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature5 = new OpenLayers.Layer.TMS("Wind42", "http://www.openportguide.org/tiles/actual/air_temperature/15/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature6 = new OpenLayers.Layer.TMS("Wind54", "http://www.openportguide.org/tiles/actual/air_temperature/19/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature7 = new OpenLayers.Layer.TMS("Wind66", "http://www.openportguide.org/tiles/actual/air_temperature/23/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				layer_weather_air_temperature8 = new OpenLayers.Layer.TMS("Wind78", "http://www.openportguide.org/tiles/actual/air_temperature/27/",
				{ type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				map.addLayers([layer_mapnik, layer_weather_wind1, layer_weather_wind2, layer_weather_wind3, layer_weather_wind4, layer_weather_wind5, layer_weather_wind6, layer_weather_wind7, layer_weather_wind8, layer_weather_pressure1, layer_weather_pressure2, layer_weather_pressure3, layer_weather_pressure4, layer_weather_pressure5, layer_weather_pressure6, layer_weather_pressure7, layer_weather_pressure8, layer_weather_air_temperature1, layer_weather_air_temperature2, layer_weather_air_temperature3, layer_weather_air_temperature4, layer_weather_air_temperature5, layer_weather_air_temperature6, layer_weather_air_temperature7, layer_weather_air_temperature8]);

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
				if (zoom >= 8) {
					map.zoomTo(7)
				} else if (zoom <= 3) {
					map.zoomTo(4)
				}
				// Set cookie for remembering #zoomlevel
				setCookie("weather_zoom",zoom);
			}

			function zoomIn() {
				if (zoom <= 6) {
					map.zoomIn();
				}
			}

			function zoomOut() {
    			if (zoom >= 5) {
					map.zoomOut();
				}
			}

			function showWind() {
				if (!showWindLayer) {
					document.getElementById("checkWind").checked = true;
					document.getElementById("comment").style.visibility = "visible";
					document.getElementById("buttonWind").style.background = "#ADD8E6";
					setWindLayerVisible();
					showWindLayer = true;
				} else {
					document.getElementById("checkWind").checked = false;
					document.getElementById("comment").style.visibility = "hidden";
					clearWindLayerVisibility();
					showWindLayer = false;
				}
			}

			function showPressure() {
				if (!showPressureLayer) {
					document.getElementById("checkPressure").checked = true;
					document.getElementById("buttonPressure").style.background = "#ADD8E6";
					setPressureLayerVisible();
					showPressureLayer = true;
				} else {
					document.getElementById("checkPressure").checked = false;
					clearPressureLayerVisibility();
					showPressureLayer = false;
				}
			}

			function showAirTemperature() {
				if (!showAirTemperatureLayer) {
					document.getElementById("checkAirTemperature").checked = true;
					document.getElementById("buttonAirTemperature").style.background = "#ADD8E6";
					setAirTemperatureLayerVisible();
					showAirTemperatureLayer = true;
				} else {
					document.getElementById("checkAirTemperature").checked = false;
					clearAirTemperatureLayerVisibility();
					showAirTemperatureLayer = false;
				}
			}


			// Read time files from server and create the menu
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
				var html = "<b><?=$t->tr("time")?> (UTC)</b><br/><br/>";
				var layer = 1;

				for(i = 0; i < arrayTimeValues.length; i++) {
					var values = arrayTimeValues[i].split(" ");
					var date = values[0];
					var time = values[1];
					layer = i + 1;
					if (oldDate != date) {
						if (oldDate != "00") {
							html += "</ul>";
						}
						html += "<h2>" + date + "</h2>";
						html += "<ul>";
						oldDate = date;
					}
					html += "<li id = timeLayer" + layer + " onClick='setLayerVisible(" + layer + ")' onMouseover=\"this.style.background='#ADD8E6'\" onMouseout=\"if(layerNumber !=" + layer + ") {this.style.background='#FFFFFF'} else {this.style.background='#ADD8E6'}\">" + time + "</li>";
				}
				html += "</ul>";
				document.getElementById('timemenu').innerHTML = html;
			}

			function setLayerVisible(number) {
				document.getElementById("timeLayer" + layerNumber).style.background = "#FFFFFF";
				layerNumber = number;
				document.getElementById("timeLayer" + layerNumber).style.background = "#ADD8E6";
				if (showWindLayer) {
					setWindLayerVisible();
				}
				if (showPressureLayer) {
					setPressureLayerVisible();
				}
				if (showAirTemperatureLayer) {
					setAirTemperatureLayerVisible();
				}
			}

			function setWindLayerVisible() {
				clearWindLayerVisibility();
				switch (layerNumber) {
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

			function setPressureLayerVisible() {
				clearPressureLayerVisibility();
				switch (layerNumber) {
					case 1:
						layer_weather_pressure1.setVisibility(true);
						break;
					case 2:
						layer_weather_pressure2.setVisibility(true);
						break;
					case 3:
						layer_weather_pressure3.setVisibility(true);
						break;
					case 4:
						layer_weather_pressure4.setVisibility(true);
						break;
					case 5:
						layer_weather_pressure5.setVisibility(true);
						break;
					case 6:
						layer_weather_pressure6.setVisibility(true);
						break;
					case 7:
						layer_weather_pressure7.setVisibility(true);
						break;
					case 8:
						layer_weather_pressure8.setVisibility(true);
						break;
				}
			}

			function setAirTemperatureLayerVisible() {
				clearAirTemperatureLayerVisibility();
				switch (layerNumber) {
					case 1:
						layer_weather_air_temperature1.setVisibility(true);
						break;
					case 2:
						layer_weather_air_temperature2.setVisibility(true);
						break;
					case 3:
						layer_weather_air_temperature3.setVisibility(true);
						break;
					case 4:
						layer_weather_air_temperature4.setVisibility(true);
						break;
					case 5:
						layer_weather_air_temperature5.setVisibility(true);
						break;
					case 6:
						layer_weather_air_temperature6.setVisibility(true);
						break;
					case 7:
						layer_weather_air_temperature7.setVisibility(true);
						break;
					case 8:
						layer_weather_air_temperature8.setVisibility(true);
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

			function clearPressureLayerVisibility() {
				layer_weather_pressure1.setVisibility(false);
				layer_weather_pressure2.setVisibility(false);
				layer_weather_pressure3.setVisibility(false);
				layer_weather_pressure4.setVisibility(false);
				layer_weather_pressure5.setVisibility(false);
				layer_weather_pressure6.setVisibility(false);
				layer_weather_pressure7.setVisibility(false);
				layer_weather_pressure8.setVisibility(false);
			}

			function clearAirTemperatureLayerVisibility() {
				layer_weather_air_temperature1.setVisibility(false);
				layer_weather_air_temperature2.setVisibility(false);
				layer_weather_air_temperature3.setVisibility(false);
				layer_weather_air_temperature4.setVisibility(false);
				layer_weather_air_temperature5.setVisibility(false);
				layer_weather_air_temperature6.setVisibility(false);
				layer_weather_air_temperature7.setVisibility(false);
				layer_weather_air_temperature8.setVisibility(false);
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
				<li onClick="window.location.href='./index.php?lang=<?=$t->getCurrentLanguage()?>'"><IMG src="../resources/icons/OpenSeaMapLogo_88.png" width="24" height="24" align="center" border="0"><?=$t->tr("Seekarte")?></img></li>
				<li>&nbsp;|&nbsp;</li>
				<li id="buttonWind" onClick="showWind()" onMouseover="this.style.background='#ADD8E6'" onMouseout="if(!showWindLayer) {this.style.background='#FFFFFF'} else {this.style.background='#ADD8E6'}"><input type="checkbox" id="checkWind"/><IMG src="./resources/map/WindIcon.png" width="24" height="24" align="center" border="0"><?=$t->tr("wind")?>&nbsp;</img></li>
				<li>&nbsp;&nbsp;</li>
				<li id="buttonPressure" onClick="showPressure()" onMouseover="this.style.background='#ADD8E6'" onMouseout="if(!showPressureLayer) {this.style.background='#FFFFFF'} else {this.style.background='#ADD8E6'}"><input type="checkbox" id="checkPressure"/><IMG src="./resources/map/AirPressureIcon.png" width="24" height="24" align="center" border="0"><?=$t->tr("AirPressure")?>&nbsp;</img></li>
				<li>&nbsp;|&nbsp;</li>
				<li id="buttonAirTemperature" onClick="showAirTemperature()" onMouseover="this.style.background='#ADD8E6'" onMouseout="if(!showAirTemperatureLayer) {this.style.background='#FFFFFF'} else {this.style.background='#ADD8E6'}"><input type="checkbox" id="checkAirTemperature"/><IMG src="./resources/map/AirTemperatureIcon.png" width="24" height="24" align="center" border="0"><?=$t->tr("AirTemperature")?>&nbsp;</img></li>
				<li>&nbsp;|&nbsp;</li>
				<li onClick="zoomIn()"><IMG src="./resources/map/zoom-in.png" width="24" height="24" align="center" border="0">Zoom +</img></li>
				<li onClick="zoomOut()"><IMG src="./resources/map/zoom-out.png" width="24" height="24" align="center" border="0">Zoom -</img></li>
			</ul>
		</div>
		<div id="timemenu" style="position:absolute; top:55px; left:12px;">
			<h4>Time (UTC)</h4>
		</div>
		<div id="comment" style="position:absolute; top:10px; right:12px;  visibility:hidden;">
			<img src="./resources/map/WindScale.png"/>
		</div>
	</body>
</html>
