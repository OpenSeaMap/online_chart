<?php
	include("../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>OpenSeaMap - <?php echo $t->tr("dieFreieSeekarte")?></title>
		<meta name="AUTHOR" content="Olaf Hannemann">
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=9">
		<link rel="SHORTCUT ICON" href="../resources/icons/OpenSeaMapLogo_16.png">
		<link rel="stylesheet" type="text/css" href="map-full.css">
		<link rel="stylesheet" type="text/css" href="topmenu.css">
		<script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
		<script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
		<script type="text/javascript" src="./javascript/prototype.js"></script>
		<script type="text/javascript" src="./javascript/utilities.js"></script>
		<script type="text/javascript" src="./javascript/map_utils.js"></script>
		<script type="text/javascript" src="./javascript/harbours.js"></script>
		<script type="text/javascript" src="./javascript/nominatim.js"></script>
		<script type="text/javascript" src="./javascript/tidal_scale.js"></script>
		<script type="text/javascript" src="./javascript/route/NauticalRoute.js"></script>
		<script type="text/javascript" src="./javascript/mouseposition_dm.js"></script>
		<script type="text/javascript" src="./javascript/grid_wgs.js"></script>
		<script type="text/javascript">

			var map;
			var popup;
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
			var wikipediaThumbs = false;

			// FIXME: Work around for accessing translations from harbour.js
			var linkTextSkipperGuide = "<?=$t->tr('descrSkipperGuide')?>";
			var linkTextWeatherHarbour = "<?=$t->tr('descrOpenPortGuide')?>";
			// FIXME: Work around for accessing translations from tidal_scale.js
			var linkTextHydrographCurve = "<?=$t->tr('hydrographCurve')?>";
			var linkTextMeasuringValue = "<?=$t->tr('measuringValue')?>";
			var linkTextTendency = "<?=$t->tr('tendency')?>";
			// FIXME: Work around for accessing translations from NauticalRoute.js
			var tableTextNauticalRouteCoordinate = "<?=$t->tr('coordinate')?>";
			var tableTextNauticalRouteCourse = "<?=$t->tr('course')?>";
			var tableTextNauticalRouteDistance = "<?=$t->tr('distance')?>";

			// Set language
			var language = "<?=$t->getCurrentLanguage()?>";

			// Layers
			var layer_seamark;
			var layer_download;
			var layer_nautical_route;
			var layer_sport;
			var layer_pois;
			var layer_wikipedia;
			var layer_gebco_deepshade
			var layer_gebco_deeps_gwc

			// Select controls
			var selectDownload;
			var selectControlPois;

			// Controls
			var ZoomBar = new OpenLayers.Control.PanZoomBar();

			// Visibility
			var HarboursVisible = true;
			var TidalScalesVisible = false;

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
				if (getCookie("HarbourLayerVisible") == "false") {
					HarboursVisible = false;
				}
				if (getCookie("TidalScaleLayerVisible") == "true") {
					TidalScalesVisible = true;
				}
				drawmap();
				// Create Marker, if arguments are given
				if (mlat != -1 && mlon != -1) {
					var layer_marker = new OpenLayers.Layer.Markers("Marker");
					map.addLayer(layer_marker);
					addMarker(layer_marker, mlon, mlat, convert2Text(getArgument("mtext")));
				}
				// Dirty fix for Firefox cache problem
				clearCheckBoxes();
				// Set Layer visibility from cookie
				if (getCookie("SeamarkLayerVisible") == "false") {
					layer_seamark.setVisibility(false);
				} else {
					document.getElementById("checkLayerSeamark").checked = true;
				}
				if (!HarboursVisible) {
					layer_pois.setVisibility(false);
				} else {
					document.getElementById("checkLayerHarbour").checked = true;
				}
				if (TidalScalesVisible) {
					layer_pois.setVisibility(true);
					document.getElementById("checkLayerTidalScale").checked = true;
				}
				if (getCookie("SportLayerVisible") == "true") {
					layer_sport.setVisibility(true);
					document.getElementById("checkLayerSport").checked = true;
				}
				if (getCookie("GridWGSLayerVisible") == "true") {
					layer_grid.setVisibility(true);
					document.getElementById("checkLayerGridWGS").checked = true;
				}
				if (getCookie("GebcoDepthLayerVisible") == "true") {
					layer_gebco_deepshade.setVisibility(true);
					layer_gebco_deeps_gwc.setVisibility(true);
					document.getElementById("checkLayerGebcoDepth").checked = true;
				}
				if (getCookie("WikipediaLayerVisible") == "true") {
					showWikipediaLinks(true, false);
					document.getElementById("checkLayerWikipedia").checked = true;
					document.getElementById("checkLayerWikipediaMarker").checked = true;
				}
				// Set current language for internationalization
				OpenLayers.Lang.setCode(language);
			}

			function clearCheckBoxes() {
				document.getElementById("checkLayerSeamark").checked = false;
				document.getElementById("checkLayerHarbour").checked = false;
				document.getElementById("checkLayerTidalScale").checked = false;
				document.getElementById("checkLayerSport").checked = false;
				document.getElementById("checkLayerGridWGS").checked = false;
				document.getElementById("checkLayerGebcoDepth").checked = false;
				document.getElementById("checkDownload").checked = false;
				document.getElementById("checkNauticalRoute").checked = false;
				document.getElementById("checkLayerWikipedia").checked = false;
				document.getElementById("checkLayerWikipediaMarker").checked =false;
				document.getElementById("checkLayerWikipediaThumbnails").checked =false;
			}

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
				if (HarboursVisible) {
					clearPoiLayer();
					if (!TidalScalesVisible) {
						layer_pois.setVisibility(false);
					} else {
						refreshTidalScales();
					}
					HarboursVisible = false;
					document.getElementById("checkLayerHarbour").checked = false;
					setCookie("HarbourLayerVisible", "false");
				} else {
					layer_pois.setVisibility(true);
					HarboursVisible = true;
					document.getElementById("checkLayerHarbour").checked = true;
					setCookie("HarbourLayerVisible", "true");
					refreshHarbours();
				}
			}

			function showTidalScale() {
				if (TidalScalesVisible) {
					clearPoiLayer();
					if (!HarboursVisible) {
						layer_pois.setVisibility(false);
					} else {
						refreshHarbours();
					}
					TidalScalesVisible = false;
					document.getElementById("checkLayerTidalScale").checked = false;
					setCookie("TidalScaleLayerVisible", "false");
				} else {
					layer_pois.setVisibility(true);
					TidalScalesVisible = true;
					document.getElementById("checkLayerTidalScale").checked = true;
					setCookie("TidalScaleLayerVisible", "true");
					refreshTidalScales();
				}
			}

			// Show route section
			function showNauticalRoute() {
				if (layer_nautical_route.visibility) {
					closeNauticalRoute();
				} else {
					addNauticalRoute();
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
					layer_gebco_deeps_gwc.setVisibility(false);
					document.getElementById("checkLayerGebcoDepth").checked = false;
					setCookie("GebcoDepthLayerVisible", "false");
				} else {
					layer_gebco_deepshade.setVisibility(true);
					layer_gebco_deeps_gwc.setVisibility(true);
					document.getElementById("checkLayerGebcoDepth").checked = true;
					setCookie("GebcoDepthLayerVisible", "true");
				}
			}

			// Show Download section
			function showMapDownload() {
				if (!downloadLoaded) {
					addMapDownload();
					document.getElementById("checkDownload").checked = true;
					if (popup) {
						map.removePopup(popup);
					}
				} else {
					closeMapDownload();
				}
			}

			// Show Wikipedia layer
			function showWikipediaLinks(thumbs, sub) {
				if (sub) {
					if (thumbs) {
						var displayThumbs = 'yes';
						setCookie("WikipediaLayerThumbs", "true");
					} else {
						var displayThumbs = 'no';
						setCookie("WikipediaLayerThumbs", "false");
					}
					wikipediaThumbs = thumbs;
					if(document.getElementById("checkLayerWikipediaMarker").checked && thumbs) {
						document.getElementById("checkLayerWikipediaMarker").checked =false;
						document.getElementById("checkLayerWikipediaThumbnails").checked =true;
						layer_wikipedia.setVisibility(false);
					} else if (document.getElementById("checkLayerWikipediaThumbnails").checked && !thumbs) {
						document.getElementById("checkLayerWikipediaMarker").checked =true;
						document.getElementById("checkLayerWikipediaThumbnails").checked =false;
						layer_wikipedia.setVisibility(false);
					}
					var iconsProtocol = new OpenLayers.Protocol.HTTP({
						url: 'http://toolserver.org/~kolossos/geoworld/marks.php?',
						params: {
							'LANG' : language,
							'thumbs' : displayThumbs
						},
						format: new OpenLayers.Format.KML({
							extractStyles: true,
							extractAttributes: true
						})
					});
					layer_wikipedia.protocol = iconsProtocol;
				} else {
					if (layer_wikipedia.visibility) {
						layer_wikipedia.setVisibility(false);
						if (popup) {
							map.removePopup(popup);
						}
						//layer_wikipedia.destroyFeatures();
						document.getElementById("checkLayerWikipedia").checked = false;
						document.getElementById("checkLayerWikipediaMarker").checked =false;
						document.getElementById("checkLayerWikipediaThumbnails").checked =false;
						setCookie("WikipediaLayerVisible", "false");
					} else {
						layer_wikipedia.setVisibility(true);
						document.getElementById("checkLayerWikipedia").checked = true;
						if (wikipediaThumbs) {
							document.getElementById("checkLayerWikipediaThumbnails").checked =true;
							document.getElementById("checkLayerWikipediaMarker").checked =false;
						} else {
							document.getElementById("checkLayerWikipediaMarker").checked =true;
							document.getElementById("checkLayerWikipediaThumbnails").checked =false;
						}
						setCookie("WikipediaLayerVisible", "true");
					}
				}
			}

			// Show dialog window
			function showActionDialog(htmlText) {
				document.getElementById("actionDialog").style.visibility = 'visible';
				document.getElementById("actionDialog").innerHTML=""+ htmlText +"";
			}

			// Hide dialog window
			function closeActionDialog() {
				document.getElementById("actionDialog").style.visibility = 'hidden';
			}

			function addMapDownload() {
				addDownloadlayer();
				layer_download.setVisibility(true);
				var htmlText = "<div style=\"position:absolute; top:5px; right:5px; cursor:pointer;\"><img src=\"./resources/action/close.gif\" onClick=\"closeMapDownload();\"/></div>";
				htmlText += "<h3><?=$t->tr("downloadChart")?>:</h3><br/>";
				htmlText += "<table border=\"0\" width=\"240px\">";
				htmlText += "<tr><td>Name:</td><td><div id=\"info_dialog\">&nbsp;<?=$t->tr("pleaseSelect")?><br/></div></td></tr>";
				htmlText += "<tr><td><?=$t->tr("format")?>:</td><td><select id=\"mapFormat\"><option value=\"unknown\"/><?=$t->tr("unknown")?><option value=\"png\"/>png<option value=\"cal\"/>cal<option value=\"kap\"/>kap<option value=\"WCI\"/>WCI<option value=\"kmz\"/>kmz<option value=\"jpr\"/>jpr</select></td></tr>";
				htmlText += "<tr><td><br/><input type=\"button\" id=\"buttonMapDownload\" value=\"<?=$t->tr("download")?>\" onclick=\"downloadMap()\" disabled=\"true\"></td><td align=\"right\"><br/><input type=\"button\" id=\"buttonMapClose\" value=\"<?=$t->tr("close")?>\" onclick=\"closeMapDownload()\"></td></tr>";
				htmlText += "</table>";
				showActionDialog(htmlText);
				downloadLoaded = true;
				selectDownload.activate();
				selectControlPois.deactivate();
			}

			function closeMapDownload() {
				layer_download.setVisibility(false);
				layer_download.removeAllFeatures();
				closeActionDialog();
				downloadLoaded = false;
				document.getElementById("checkDownload").checked = false;
				selectDownload.deactivate();
				selectControlPois.activate();
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
				var url = "http://sourceforge.net/projects/opennautical/files/Maps" + downloadLink + "ONC-" + downloadName + format + "/download";
				
				downloadWindow = window.open(url);
			}

			function selectedMap (feature) {
				var selectedMap = feature.id.split(".");
				var buff = arrayMaps[selectedMap[2].split("_")[1]].split(":");

				downloadName = buff[0];
				downloadLink = buff[1];

				var mapName =downloadName;

				document.getElementById('info_dialog').innerHTML=""+ mapName +"";
				document.getElementById('buttonMapDownload').disabled=false;
			}

			function addNauticalRoute() {
				layer_nautical_route.setVisibility(true);
				var htmlText = "<div style=\"position:absolute; top:5px; right:5px; cursor:pointer;\">";
				htmlText += "<img src=\"./resources/action/info.png\"  width=\"17\" height=\"17\" onClick=\"showMapKey('help-trip-planner');\"/>&nbsp;";
				htmlText += "<img src=\"./resources/action/close.gif\" onClick=\"closeNauticalRoute();\"/></div>";
				htmlText += "<h3><?=$t->tr("tripPlanner")?>:</h3><br/>";
				htmlText += "<table border=\"0\" width=\"370px\">";
				htmlText += "<tr><td><?=$t->tr("start")?></td><td id=\"routeStart\">- - -</td></tr>";
				htmlText += "<tr><td><?=$t->tr("finish")?></td><td id=\"routeEnd\">- - -</td></tr>";
				htmlText += "<tr><td><?=$t->tr("distance")?></td><td id=\"routeDistance\">- - -</td></tr>";
				htmlText += "<tr><td><?=$t->tr("format")?></td><td><select id=\"routeFormat\"><option value=\"CSV\"/>CSV<option value=\"GML\"/>GML<option value=\"KML\"/>KML</select></td></tr>";
				htmlText += "<tr><td id=\"routePoints\" colspan = 2> </td></tr>";
				htmlText += "<tr><td><br/><input type=\"button\" id=\"buttonRouteDownloadTrack\" value=\"<?=$t->tr("download")?>\" onclick=\"NauticalRoute_DownloadTrack();\" disabled=\"true\"></td><td align=\"right\"><br/><input type=\"button\" id=\"buttonNauticalRouteClear\" value=\"Clear\" onclick=\"closeNauticalRoute();addNauticalRoute();\">&nbsp;<input type=\"button\" id=\"buttonActionDialogClose\" value=\"<?=$t->tr("close")?>\" onclick=\"closeNauticalRoute();\"></td></tr></table>";
				document.getElementById("checkNauticalRoute").checked = true;
				showActionDialog(htmlText);
				NauticalRoute_startEditMode();
			}

			function closeNauticalRoute() {
				layer_nautical_route.setVisibility(false);
				closeActionDialog();
				NauticalRoute_stopEditMode();
				document.getElementById("checkNauticalRoute").checked = false;
			}

			function addSearchResults(xmlHttp) {
				var items = xmlHttp.responseXML.getElementsByTagName("place");
				var placeName, description, placeLat, placeLon;
				var buff, pos;
				var htmlText = "<div style=\"position:absolute; top:5px; right:5px; cursor:pointer;\"><img src=\"./resources/action/close.gif\" onClick=\"closeActionDialog();\"/></div>";
				htmlText += "<h3><?=$t->tr("searchResults")?>:</h3><br/>"
				htmlText += "<table border=\"0\" width=\"370px\">"
				for(i = 0; i < items.length; i++) {
					buff = xmlHttp.responseXML.getElementsByTagName('place')[i].getAttribute('display_name');
					placeLat = xmlHttp.responseXML.getElementsByTagName('place')[i].getAttribute('lat');
					placeLon = xmlHttp.responseXML.getElementsByTagName('place')[i].getAttribute('lon');
					pos = buff.indexOf(",");
					placeName = buff.substring(0, pos);
					description = buff.substring(pos +1).trim();
					htmlText += "<tr style=\"cursor:pointer;\" onmouseover=\"this.style.backgroundColor = '#ADD8E6';\"onmouseout=\"this.style.backgroundColor = '#FFF';\" onclick=\"jumpTo(" + placeLon + ", " + placeLat + ", " + zoom + ");\"><td  valign=\"top\"><b>" + placeName + "</b></td><td>" + description + "</td></tr>";
				}
				htmlText += "<tr><td>&nbsp;</td><td align=\"right\"><br/><input type=\"button\" id=\"buttonMapClose\" value=\"<?=$t->tr("close")?>\" onclick=\"closeActionDialog();\"></td></tr></table>";
				showActionDialog(htmlText);
			}

			function drawmap() {
				map = new OpenLayers.Map('map', {
					projection: projMerc,
					displayProjection: proj4326,
					eventListeners: {
						moveend: mapEventMove,
						zoomend: mapEventZoom,
						click: mapEventClick
					},

					controls: [
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.Navigation(),
						//new OpenLayers.Control.LayerSwitcher(), //only for debugging
						new OpenLayers.Control.ScaleLine({topOutUnits : "nmi", bottomOutUnits: "km", topInUnits: 'nmi', bottomInUnits: 'km', maxWidth: '40'}),
						new OpenLayers.Control.MousePositionDM(),
						new OpenLayers.Control.OverviewMap(),
						ZoomBar
					],
					maxExtent:
					new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
					numZoomLevels: 19,
					maxResolution: 156543,
					units: 'meters'
				});

				// Set proxy url for accessing cross side domains
				OpenLayers.ProxyHost = './api/wikipedia-proxy/index.php?q=';

				var bboxStrategyWikipedia = new OpenLayers.Strategy.BBOX( {
					ratio : 1.1,
					resFactor: 1
				});

				var poiLayerWikipediaHttp = new OpenLayers.Protocol.HTTP({
					url: 'http://toolserver.org/~kolossos/geoworld/marks.php?',
					params: {
						'LANG' : language,
						'thumbs' : 'no'
					},
					format: new OpenLayers.Format.KML({
						extractStyles: true,
						extractAttributes: true
					})
				});

				// Add Layers to map-------------------------------------------------------------------------------------------------------
				// Mapnik (Base map)
				var layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
				// Seamark
				layer_seamark = new OpenLayers.Layer.TMS("seamarks", "http://tiles.openseamap.org/seamark/",
					{ numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
				// Sport
				layer_sport = new OpenLayers.Layer.TMS("Sport", "http://tiles.openseamap.org/sport/",
					{ numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
				//GebcoDepth
				layer_gebco_deepshade = new OpenLayers.Layer.WMS("deepshade", "http:///osm.franken.de:8080/geoserver/wms",
					{layers: "gebco:deepshade", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
					{isBaseLayer: false, visibility: false, opacity: 0.2, minResolution: 38.22});
				layer_gebco_deeps_gwc = new OpenLayers.Layer.WMS("deeps_gwc", "http://osm.franken.de:8080/geoserver/gwc/service/wms",
					{layers: "gebco_new", format:"image/jpeg"},
					{isBaseLayer: false, visibility: false, opacity: 0.4});
				// POI-Layer for harbours and tidal scales
				layer_pois = new OpenLayers.Layer.Vector("pois", { 
					visibility: true,
					projection: proj4326, 
					displayOutsideMaxExtent:true
				});
				layer_download = new OpenLayers.Layer.Vector("Map Download", {
					visibility: false
				});
				// Trip planner
				layer_nautical_route = new OpenLayers.Layer.Vector("Trip Planner", 
					{styleMap: routeStyle, visibility: false, eventListeners: {"featuresadded": NauticalRoute_routeAdded, "featuremodified": NauticalRoute_routeModified}});
				// Grid WGS
				layer_grid = new OpenLayers.Layer.GridWGS("coordinateGrid", {
					visibility: false, 
					zoomUnits: zoomUnits
				});
				layer_wikipedia = new OpenLayers.Layer.Vector("Wikipedia World", {
					visibility: false,
					projection: proj4326,
					strategies: [bboxStrategyWikipedia],
					protocol: poiLayerWikipediaHttp
				});
				map.addLayers([layer_mapnik, layer_gebco_deepshade, layer_gebco_deeps_gwc, layer_seamark, layer_grid, layer_pois, layer_wikipedia, layer_nautical_route, layer_sport, layer_download]);
				if (!map.getCenter()) {
					jumpTo(lon, lat, zoom);
				}
				// Add download tool
				selectDownload = new OpenLayers.Control.SelectFeature(layer_download, {onSelect: selectedMap});
				map.addControl(selectDownload);

				// Add select tool for poi layers
				selectControlPois = new OpenLayers.Control.SelectFeature([layer_nautical_route, layer_pois, layer_wikipedia], {onSelect: onFeatureSelectPoiLayers, hover: true});
				map.addControl(selectControlPois);
				selectControlPois.activate();

//testZoom.divEvents.register("mouseover", '', test_mouseover);
//OpenLayers.Control.PanZoomBar.divEvents.register("mouseout", feature,  test_mouseout);

			}

/*function test_mouseover(ev) {
alert(ev.getMousePosition()); //4=welt 8=Übersicht 12=Umgebung 18=details
} 
function test_mouseout() {

}*/
			function clearPoiLayer() {
				harbours.clear();
				arrayTidalScales.clear();
				layer_pois.removeAllFeatures();
			}

			function onFeatureSelectPoiLayers(feature) {
				if (feature.layer == layer_nautical_route) {
					feature.style = style_edit;
				} else {
					if (popup) {
						map.removePopup(popup);
					}
					if (feature.data.popupContentHTML) {
						var buff = feature.data.popupContentHTML;
					} else { 
						var buff = '<b>'+feature.attributes.name +'</b><br>'+ feature.attributes.description;
					}
					popup = new OpenLayers.Popup.FramedCloud("chicken", 
						feature.geometry.getBounds().getCenterLonLat(),
						null,
						buff,
						null, 
						true
					);
					map.addPopup(popup);
				}
			}

			// Map event listener moved
			function mapEventMove(event) {
				// Set cookie for remembering lat lon values
				setCookie("lat", y2lat(map.getCenter().lat).toFixed(5));
				setCookie("lon", x2lon(map.getCenter().lon).toFixed(5));
				// Update harbour layer
				if (HarboursVisible) {
					refreshHarbours();
				}
				// Update tidal scale layer
				if (TidalScalesVisible) {
					refreshTidalScales();
				}
			}

			// Map event listener Zoomed
			function mapEventZoom(event) {
				zoom = map.getZoom();
				// Set cookie for remembering zoomlevel
				setCookie("zoom",zoom);
				// Clear POI layer
				clearPoiLayer();
				if(oldZoom!=zoom) {
					oldZoom=zoom;
				}
				if (downloadLoaded) {
					closeMapDownload();
					addMapDownload();
				}
			}

			function mapEventClick(event) {
				if (popup) {
					map.removePopup(popup);
				}
			}

			function addDownloadlayer(xmlMaps) {
				var xmlDoc=loadXMLDoc("./gml/map_download.xml");

				try {
					var root = xmlDoc.getElementsByTagName("maps")[0];
					var items = root.getElementsByTagName("map");
				} catch(e) {
					alert("Error (root): "+ e);
					return -1;
				}
				for (var i=0; i < items.length; ++i) {
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
							alert("Error (load): " + e);
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
					}
				}
			}

		</script>
	</head>
	<body onload=init();>
		<div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
		<div style="position:absolute; bottom:48px; left:12px; cursor:pointer;">
			<img src="../resources/icons/OSM-Logo-32px.png" height="32px" title="<?=$t->tr("SomeRights")?>" onClick="showMapKey('license')"/>
			<img src="../resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="showMapKey('license')"/>
		</div>
		<div id="actionDialog">
			<br/>&nbsp;not found&nbsp;<br/>&nbsp;
		</div>
		<? include('../classes/topmenu.inc'); ?>
	</body>
</html>
