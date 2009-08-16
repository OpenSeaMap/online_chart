<?php
include("../classes/Pages.php");
include("../classes/Translation.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>OpenSeaMap - <?php echo $t->tr("dieFreieSeekarte")?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="de" />
		<link rel="stylesheet" type="text/css" href="map-full.css">
		<script type="text/javascript" src="http://www.openlayers.org/api/OpenLayers.js"></script>
		<script type="text/javascript" src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
		<script type="text/javascript">
			var map;
			
			var layer_mapnik;
			var layer_tah;
			var layer_markers;
			
			// Position and zoomlevel of the map
			var lon = 12.0915;
			var lat = 54.1878;
			var zoom = 15;

			if (lon=="") lon=12.0915;
			if (lat=="") lat=54.1878;
			if (zoom=="") zoom=15

			function showLegend() {
				legendWindow = window.open("legend.php?lang=<?= $t->getCurrentLanguage() ?>", "Legende", "width=750, height=680, status=no, scrollbars=yes, resizable=yes");
 				legendWindow.focus();
			}
			
			function jumpTo(lon, lat, zoom) {
				var x = Lon2Merc(lon);
				var y = Lat2Merc(lat);
				if (!map.getCenter()) {
					map.setCenter(new OpenLayers.LonLat(x, y), zoom);
				}
				return false;
			}
 
			function Lon2Merc(lon) {
				return 20037508.34 * lon / 180;
			}
 
			function Lat2Merc(lat) {
				var PI = 3.14159265358979323846;
				lat = Math.log(Math.tan( (90 + lat) * PI / 360)) / (PI / 180);
				return 20037508.34 * lat / 180;
			}
 
			function addMarker(layer, lon, lat, popupContentHTML) {
			
				var ll = new OpenLayers.LonLat(Lon2Merc(lon), Lat2Merc(lat));
				var feature = new OpenLayers.Feature(layer, ll);
				feature.closeBox = true;
				feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {minSize: new OpenLayers.Size(260, 100) } );
				feature.data.popupContentHTML = popupContentHTML;
				feature.data.overflow = "hidden";
			
				var marker = new OpenLayers.Marker(ll);
				marker.feature = feature;
			
				var markerClick = function(evt) {
					if (this.popup == null) {
						this.popup = this.createPopup(this.closeBox);
						map.addPopup(this.popup);
						this.popup.show();
					} else {
						this.popup.toggle();
					}
					OpenLayers.Event.stop(evt);
				};
				marker.events.register("mousedown", feature, markerClick);
			
				layer.addMarker(marker);
				map.addPopup(feature.createPopup(feature.closeBox));
			}
 
			
			function getTileURL(bounds) {
				var res = this.map.getResolution();
				var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
				var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
				var z = this.map.getZoom();
				var limit = Math.pow(2, z);
				if (y < 0 || y >= limit) {
					return null;
				} else {
					x = ((x % limit) + limit) % limit;
					url = this.url;
					path= z + "/" + x + "/" + y + "." + this.type;
					if (url instanceof Array) {
						url = this.selectUrl(path, url);
					}
					return url+path;
				}
			}

			function drawmap() {
				// Popup und Popuptext
				var popuptext="<font color=\"black\"><b>OpenSource-Werkstatt</b></font>";
				OpenLayers.Lang.setCode('de');

				map = new OpenLayers.Map('map', {
					projection: new OpenLayers.Projection("EPSG:900913"),
					displayProjection: new OpenLayers.Projection("EPSG:4326"),
					controls: [
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.MouseDefaults(),
						new OpenLayers.Control.ScaleLine(),
						new OpenLayers.Control.LayerSwitcher(),
						new OpenLayers.Control.MousePosition(),
						new OpenLayers.Control.PanZoomBar()],
						maxExtent:
						new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
					numZoomLevels: 18,
					maxResolution: 156543,
					units: 'meters'
				});
				
				
				// Layer hinzufuegen
				
				// Mapnik
				layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
				// Osmarender
				layer_tah = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
				// Seamark
				layer_seamark = new OpenLayers.Layer.TMS("<?=$t->tr("Seezeichen")?>", "http://openseamap.org/tiles/",
				{ numZoomLevels: 18, type: 'png', getURL: getTileURL, isBaseLayer: false, displayOutsideMaxExtent: true});
				// Sport
				layer_sport = new OpenLayers.Layer.TMS("Sport", "http://openseamap.org/tiles/sport/",
				{ numZoomLevels: 18, type: 'png', getURL: getTileURL, isBaseLayer: false, displayOutsideMaxExtent: true});

				map.addLayers([layer_mapnik, layer_tah, layer_sport, layer_seamark]);
				jumpTo(lon, lat, zoom);
			}
			
		</script>
	</head>
	<body onload=drawmap();>
			<table style="width:100%; height:100%;" border="0" cellpadding="10">
				<tr>
					<td valign="top" class="normal">
						<div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
						<div style="position:absolute; bottom:10px; left:90px; width:700px;">
							<img src="../resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
						</div>
						<div id="map_key" onClick="showLegend()" style="position:absolute; top:10px; left:60px; background-color:darkblue;color:#FFF;padding: 4px;font-weight:bold;cursor:default;">
							<?=$t->tr("Legende")?>
						</div>
					</td>
				</tr>
			</table>
	</body>
</html>
