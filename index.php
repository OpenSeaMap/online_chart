<?php
    include("classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>OpenSeaMap - <?php echo $t->tr("dieFreieSeekarte")?></title>
        <meta name="AUTHOR" content="Olaf Hannemann">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta http-equiv="content-language" content="<?= $t->getCurrentLanguage()?>">
        <meta http-equiv="X-UA-Compatible" content="IE=9">
        <meta name="date" content="2012-06-02">
        <link rel="SHORTCUT ICON" href="resources/icons/OpenSeaMapLogo_16.png">
        <link rel="stylesheet" type="text/css" href="map-full.css">
        <link rel="stylesheet" type="text/css" href="topmenu.css">
        <script type="text/javascript" src="./javascript/lib/jquery.js"></script>
        <script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
        <script type="text/javascript" src="./javascript/translation-<?=$t->getCurrentLanguageSafe()?>.js"></script>
        <script type="text/javascript" src="./javascript/permalink.js"></script>
        <script type="text/javascript" src="./javascript/utilities.js"></script>
        <script type="text/javascript" src="./javascript/countries.js"></script>
        <script type="text/javascript" src="./javascript/map_utils.js"></script>
        <script type="text/javascript" src="./javascript/harbours.js"></script>
        <script type="text/javascript" src="./javascript/nominatim.js"></script>
        <script type="text/javascript" src="./javascript/tidal_scale.js"></script>
        <script type="text/javascript" src="./javascript/route/NauticalRoute.js"></script>
        <script type="text/javascript" src="./javascript/mouseposition_dm.js"></script>
        <script type="text/javascript" src="./javascript/grid_wgs.js"></script>
        <script type="text/javascript" src="./javascript/bing.js"></script>
        <script type="text/javascript" src="./javascript/ais.js"></script>
        <script type="text/javascript" src="./javascript/satpro.js"></script>
        <script type="text/javascript" src="./javascript/lib/he.js"></script>
        <script type="text/javascript" src="./javascript/waterdepth-trackpoints.js"></script>
        <script type="text/javascript">

            var map;

            // Position and zoomlevel of the map (will be overriden with permalink parameters or cookies)
            var lon = 11.6540;
            var lat = 54.1530;
            var zoom = 10;

            // marker position
            var mLat = -1;
            var mLon = -1;

            //last zoomlevel of the map
            var oldZoom = 0;

            var downloadName;
            var downloadLink;
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
            var layer_mapnik;                      // 1
            var layer_marker;                      // 2
            var layer_seamark;                     // 3
            var layer_sport;                       // 4
            var layer_gebco_deepshade;             // 5
            var layer_gebco_deeps_gwc;             // 6
            var layer_pois;                        // 7
            var layer_download;                    // 8
            var layer_nautical_route;              // 9
            var layer_grid;                        // 10
            var layer_wikipedia;                   // 11
            var layer_bing_aerial;                 // 12
            var layer_ais;                         // 13
            var layer_satpro;                      // 14
            // layer_disaster                      // 15
            var layer_tidalscale;                  // 16
            var layer_permalink;                   // 17
            var layer_waterdepth_trackpoints_100m;      // 18
            var layer_elevation_profile_contours;  // 19
            var layer_elevation_profile_hillshade; // 20
            var layer_waterdepth_trackpoints_10m;      // 21
            var layer_waterdepth_contours;        // 22

            // To not change the permalink layer order, every removed
            // layer keeps its number. The ArgParser compares the
            // count of layers with the layers argument length. So we
            // have to let him know, how many layers are removed.
            var ignoredLayers = 1;

            // Select controls
            var selectControl;

            // Controls
            var ZoomBar          = new OpenLayers.Control.PanZoomBar();
            var permalinkControl = new OpenSeaMap.Control.Permalink(null, null, {
                ignoredLayers : ignoredLayers
            });

            // Load map for the first time
            function init() {
                var buffZoom = parseInt(getCookie("zoom"));
                var buffLat = parseFloat(getCookie("lat"));
                var buffLon = parseFloat(getCookie("lon"));
                mLat  = getArgument("mlat");
                mLon  = getArgument("mlon");
                mZoom = getArgument("zoom");

                if (buffZoom != -1) {
                    zoom = buffZoom;
                }
                if (mZoom != -1) {
                    zoom = mZoom;
                }
                if (buffLat != -1 && buffLon != -1) {
                    lat = buffLat;
                    lon = buffLon;
                }
                if (mLat != -1 && mLon != -1) {
                    lat = mLat;
                    lon = mLon;
                }
                drawmap();
                try{
                  // Create Marker, if arguments are given
                  if (mLat != -1 && mLon != -1) {
                      layer_marker = new OpenLayers.Layer.Markers("Marker",{
                          layerId: 2
                      });
                      map.addLayer(layer_marker);
                      var mtext = he.encode(decodeURIComponent(getArgument("mtext"))).replace(/\n/g, '<br/>')
console.log("mtext: "+mtext)
                      addMarker(layer_marker, mLon, mLat, mtext);
                  }
                }catch(err) {
                    console.log(err)
                }
                readLayerCookies();
                resetLayerCheckboxes();
                initMenuTools();
                // Set current language for internationalization
                OpenLayers.Lang.setCode(language);
            }

            function readLayerCookies() {
                if (getArgument('layers') != -1) {
                    // There is a 'layers' url param -> ignore cookies

                    // activate checkbox for deth points if one sublayer is selected
                      if (layer_waterdepth_trackpoints_10m.visibility ||
                          layer_waterdepth_trackpoints_100m.visibility)
                          document.getElementById('checkLayerWaterDepthTrackPoints').checked = true

                    return;
                }
                // Set Layer visibility from cookie
                var seamarkVisible = getCookie("SeamarkLayerVisible") === "true"
                layer_seamark.setVisibility(seamarkVisible);

                var poisVisible = getCookie("HarbourLayerVisible") === "true"
                layer_pois.setVisibility(poisVisible);

                var tidalVisible = getCookie("TidalScaleLayerVisible") === "true"
                layer_tidalscale.setVisibility(tidalVisible);
                if(layer_tidalscale.visibility)
                  refreshTidalScales();

                var sportVisible = getCookie("SportLayerVisible") === "true"
                layer_sport.setVisibility(sportVisible);

                var gridVisible = getCookie("GridWGSLayerVisible") === "true"
                layer_grid.setVisibility(gridVisible);

                var gebcoVisible = getCookie("GebcoDepthLayerVisible") === "true"
                layer_gebco_deepshade.setVisibility(gebcoVisible);
                layer_gebco_deeps_gwc.setVisibility(gebcoVisible);

                var wikiLayerVisible = getCookie("WikipediaLayerVisible") === "true"
                var wikiThumbsVisible = getCookie("WikipediaLayerThumbs") === "true"
                setWikiThumbs(wikiThumbsVisible)
                setWikiLayer(wikiLayerVisible)

                if (getCookie("BingAerialLayerVisible") == "true") {
                    map.setBaseLayer(layer_bing_aerial);
                }
                if (getCookie("AisLayerVisible") == "true") {
                    showAis();
                }

                var depth10mVisible = getCookie("WaterDepthTrackPointsLayerVisible10m") === "true"
                layer_waterdepth_trackpoints_10m.setVisibility(depth10mVisible);

                var depth100mVisible = getCookie("WaterDepthTrackPointsLayerVisible100m") === "true"
                layer_waterdepth_trackpoints_100m.setVisibility(depth100mVisible);

                var contoursVisible = getCookie("WaterDepthContoursVisible") === "true"
                layer_waterdepth_contours.setVisibility(contoursVisible);

                document.getElementById('checkLayerWaterDepthTrackPoints').checked = depth10mVisible || depth100mVisible
                showWaterDepthTrackPoints();

                var profileVisible = getCookie("ElevationProfileLayerVisible") === "true"
                layer_elevation_profile_contours.setVisibility(profileVisible);
                layer_elevation_profile_hillshade.setVisibility(profileVisible);
            }

            function resetLayerCheckboxes()
            {
                // This method is separated from readLayerCookies because
                // the permalink control also will set the visibility of
                // layers.
                document.getElementById("checkLayerSeamark").checked                = (layer_seamark.getVisibility() === true);
                document.getElementById("checkLayerHarbour").checked                = (layer_pois.getVisibility() === true);
                document.getElementById("checkLayerTidalScale").checked             = (layer_tidalscale.getVisibility() === true);
                document.getElementById("checkLayerSport").checked                  = (layer_sport.getVisibility() === true);
                document.getElementById("checkLayerGridWGS").checked                = (layer_grid.getVisibility() === true);
                document.getElementById("checkLayerGebcoDepth").checked             = (layer_gebco_deepshade.getVisibility() === true || layer_gebco_deeps_gwc.getVisibility() === true);
                document.getElementById("checkDownload").checked                    = (layer_download.getVisibility() === true);
                document.getElementById("checkNauticalRoute").checked               = (layer_nautical_route.getVisibility() === true);
                document.getElementById("checkLayerWikipedia").checked              = (layer_wikipedia.getVisibility() === true);
                document.getElementById("checkLayerWikipediaThumbnails").checked    = (layer_wikipedia.getVisibility() === true && wikipediaThumbs === true);
                document.getElementById("checkLayerBingAerial").checked             = (map.baseLayer == layer_bing_aerial);
                document.getElementById("checkLayerAis").checked                    = (layer_ais.getVisibility() === true);
                document.getElementById("checkPermalink").checked                   = (layer_permalink.getVisibility() === true);
                //document.getElementById("checkLayerSatPro").checked                = (layer_satpro.getVisibility() === true);
                setWaterDepthBoxes();
                document.getElementById("checkDepthContours").checked                   = (layer_waterdepth_contours.getVisibility() === true);
                document.getElementById("checkLayerElevationProfile").checked       = (layer_elevation_profile_contours.getVisibility() === true || layer_elevation_profile_hillshade.getVisibility() === true);

                createPermaLink();
            }

            // Show popup window for help
            function showMapKey(item) {
                legendWindow = window.open("legend.php?lang=" + language + "&page=" + item, "MapKey", "width=760, height=680, status=no, scrollbars=yes, resizable=yes");
                legendWindow.focus();
            }

            function showSeamarks() {
                if (layer_seamark.visibility) {
                    layer_seamark.setVisibility(false);
                    setCookie("SeamarkLayerVisible", "false");
                } else {
                    layer_seamark.setVisibility(true);
                    setCookie("SeamarkLayerVisible", "true");
                }
            }

            function showHarbours() {
                if (layer_pois.visibility) {
                    clearPoiLayer();
                    layer_pois.setVisibility(false);
                    setCookie("HarbourLayerVisible", "false");
                } else {
                    layer_pois.setVisibility(true);
                    setCookie("HarbourLayerVisible", "true");
                    refreshHarbours();
                }
            }

            function showTidalScale() {
                if (layer_tidalscale.visibility) {
                    clearTidalScaleLayer();
                    layer_tidalscale.setVisibility(false);
                    setCookie("TidalScaleLayerVisible", "false");
                } else {
                    layer_tidalscale.setVisibility(true);
                    setCookie("TidalScaleLayerVisible", "true");
                    refreshTidalScales();
                }
            }

            function toggleNauticalRoute(show) {
                if (show) {
                    addNauticalRoute();
                } else {
                    closeNauticalRoute();
                }
            }

            function togglePermalink(show) {
                if (show) {
                    addPermalink();
                } else {
                    closePermalink();
                }
            }

            function showSport() {
                if (layer_sport.visibility) {
                    layer_sport.setVisibility(false);
                    setCookie("SportLayerVisible", "false");
                } else {
                    layer_sport.setVisibility(true);
                    setCookie("SportLayerVisible", "true");
                }
            }

            function showGridWGS() {
                if (layer_grid.visibility) {
                    layer_grid.setVisibility(false);
                    setCookie("GridWGSLayerVisible", "false");
                } else {
                    layer_grid.setVisibility(true);
                    setCookie("GridWGSLayerVisible", "true");
                }
            }

            function showGebcoDepth() {
                if (layer_gebco_deepshade.visibility) {
                    layer_gebco_deepshade.setVisibility(false);
                    layer_gebco_deeps_gwc.setVisibility(false);
                    setCookie("GebcoDepthLayerVisible", "false");
                } else {
                    layer_gebco_deepshade.setVisibility(true);
                    layer_gebco_deeps_gwc.setVisibility(true);
                    setCookie("GebcoDepthLayerVisible", "true");
                }
            }

            function showBingAerial() {
                if (map.baseLayer == layer_bing_aerial) {
                    map.setBaseLayer(layer_mapnik);
                    setCookie("BingAerialLayerVisible", "false");
                } else {
                    map.setBaseLayer(layer_bing_aerial);
                    setCookie("BingAerialLayerVisible", "true");
                }
                correctBingVisibility();
            }

            function correctBingVisibility() {
                if (map.baseLayer == layer_bing_aerial) {
                    document.getElementById("license_bing").style.display = 'inline';
                    layer_bing_aerial.redraw();
                } else {
                    document.getElementById("license_bing").style.display = 'none';
                }
            }

            function showAis() {
                if (layer_ais.visibility) {
                    layer_ais.setVisibility(false);
                    document.getElementById("license_marine_traffic").style.display = 'none';
                    setCookie("AisLayerVisible", "false");
                } else {
                    layer_ais.setVisibility(true);
                    document.getElementById("license_marine_traffic").style.display = 'inline';
                    setCookie("AisLayerVisible", "true");
                }
            }

            function showSatPro() {
                if (layer_satpro.visibility) {
                    layer_satpro.setVisibility(false);
                    setCookie("SatProLayerVisible", "false");
                } else {
                    layer_satpro.setVisibility(true);
                    setCookie("SatProLayerVisible", "true");
                }
            }

            function showDisaster() {
                if (layer_disaster.visibility) {
                    layer_disaster.setVisibility(false);
                    setCookie("DisasterLayerVisible", "false");
                } else {
                    layer_disaster.setVisibility(true);
                    setCookie("DisasterLayerVisible", "true");
                }
            }

            function toggleMapDownload(show) {
                if (show) {
                    addMapDownload();
                    selectControl.removePopup();
                } else {
                    closeMapDownload();
                }
            }

            /// update visual elements based onlayer visibility
            function setWaterDepthBoxes(fromClick){
              // overwrite checkbox.checked if not comming from an mouse click
              if(fromClick !== true){
                document.getElementById('checkLayerWaterDepthTrackPoints').checked = layer_waterdepth_trackpoints_10m.visibility ||                          layer_waterdepth_trackpoints_100m.visibility
              }

              var checked = document.getElementById("checkLayerWaterDepthTrackPoints").checked;

              if(!checked){
                layer_waterdepth_trackpoints_10m.setVisibility(false);
                layer_waterdepth_trackpoints_100m.setVisibility(false);
                document.getElementById("license_waterdepth").style.display = 'none';
              }else{
                if (!layer_waterdepth_trackpoints_10m.visibility &&
                    !layer_waterdepth_trackpoints_100m.visibility)
                    layer_waterdepth_trackpoints_10m.setVisibility(true);

                document.getElementById("license_waterdepth").style.display = 'inline';
              }

              document.getElementById("checkLayerWaterDepthTrackPoints10m").checked = layer_waterdepth_trackpoints_10m.visibility
              document.getElementById("checkLayerWaterDepthTrackPoints100m").checked = layer_waterdepth_trackpoints_100m.visibility
            }

            function showWaterDepthTrackPoints(fromClick) {
              setWaterDepthBoxes(fromClick)
              showWaterDepthTrackPoints10m();
              showWaterDepthTrackPoints100m();
            }

            function showWaterDepthTrackPoints10m() {
                if (!layer_waterdepth_trackpoints_10m.visibility) {
                    layer_waterdepth_trackpoints_10m.setVisibility(false);
                    setCookie("WaterDepthTrackPointsLayerVisible10m", "false");
                } else {
                    layer_waterdepth_trackpoints_10m.setVisibility(true);
                    setCookie("WaterDepthTrackPointsLayerVisible10m", "true");
                }
            }
            function showWaterDepthTrackPoints100m() {
                if (!layer_waterdepth_trackpoints_100m.visibility) {
                    layer_waterdepth_trackpoints_100m.setVisibility(false);
                    setCookie("WaterDepthTrackPointsLayerVisible100m", "false");
                } else {
                    layer_waterdepth_trackpoints_100m.setVisibility(true);
                    setCookie("WaterDepthTrackPointsLayerVisible100m", "true");
                }
            }

            function showContours() {
              var visibleNew = !layer_waterdepth_contours.visibility
              layer_waterdepth_contours.setVisibility(visibleNew);
              setCookie("WaterDepthContoursVisible", visibleNew);
            }

            function showElevationProfile() {
                if (layer_elevation_profile_contours.visibility) {
                    layer_elevation_profile_contours.setVisibility(false);
                    layer_elevation_profile_hillshade.setVisibility(false);
                    setCookie("ElevationProfileLayerVisible", "false");
                } else {
                    layer_elevation_profile_contours.setVisibility(true);
                    layer_elevation_profile_hillshade.setVisibility(true);
                    setCookie("ElevationProfileLayerVisible", "true");
                }
            }

            // Show Wikipedia layer
            function showWikipediaLinks(sub) {
                if (sub) {
                  var checked = document.getElementById("checkLayerWikipediaThumbnails").checked
                  setWikiLayer(false); // will be toggled by parent <li onClick>
                  // toggle thumb display
                  setWikiThumbs(!checked)

                } else {
                  // toggle wiki layer
                  setWikiLayer(!layer_wikipedia.getVisibility())
                }
            }

            function setWikiThumbs(active){
              wikipediaThumbs = active
              setWikiProtocol();
            }

            function setWikiLayer(visible){
              layer_wikipedia.setVisibility(visible);
              setCookie("WikipediaLayerVisible", visible);
              if(!visible) {
                selectControl.removePopup();
              }else{
                setWikiProtocol()
              }

            }

            function setWikiProtocol(){
              setCookie("WikipediaLayerThumbs", wikipediaThumbs);
              var displayThumbs = wikipediaThumbs ? 'yes' : 'no';
              var iconsProtocol = new OpenLayers.Protocol.HTTP({
                        url: 'api/proxy-wikipedia.php?',
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
                selectControl.hover = false;
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
            }

            function closeMapDownload() {
                selectControl.hover = true;
                layer_download.setVisibility(false);
                layer_download.removeAllFeatures();
                closeActionDialog();
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

            function selectedMap (event) {
                var feature = event.feature;

                downloadName = feature.attributes.name;
                downloadLink = feature.attributes.link;

                var mapName = downloadName;

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
                showActionDialog(htmlText);
                NauticalRoute_startEditMode();
            }

            function closeNauticalRoute() {
                layer_nautical_route.setVisibility(false);
                closeActionDialog();
                NauticalRoute_stopEditMode();
            }

            function onAddMarker(e) {
                // Marker Init
                var size = new OpenLayers.Size(32, 32); // size of the marker
                var offset = new OpenLayers.Pixel(-(size.w/2), -size.h); // offset to get the pinpoint of the needle to mouse pos
                var icon = new OpenLayers.Icon('./resources/icons/Needle_Red_32.png', size, offset); // Init of icon

                // Adding of Marker
                layer_permalink.clearMarkers(); // clear all markers to only keep one marker at a time on the map
                var position = this.events.getMousePosition(e); // get position of mouse click
                var lonlat = map.getLonLatFromLayerPx(position); // get Lon/Lat from position
                layer_permalink.addMarker(new OpenLayers.Marker(lonlat, icon)); // add maker

                // Display Marker Position
                lonlat.transform(projMerc, proj4326);
                // Code from mousepostion_dm.js - redundant, try to reuse
                var ns = lonlat.lat >= 0 ? 'N' : 'S';
                var we = lonlat.lon >= 0 ? 'E' : 'W';
                var lon_m = Math.abs(lonlat.lon*60).toFixed(3);
                var lat_m = Math.abs(lonlat.lat*60).toFixed(3);
                var lon_d = Math.floor(lon_m/60);
                var lat_d = Math.floor(lat_m/60);
                lon_m -= lon_d*60;
                lat_m -= lat_d*60;
                // Write the specified content inside
                OpenLayers.Util.getElement("markerpos").innerHTML = ns + lat_d + "°" + format2FixedLenght(lat_m,6,3) + "'" + " " + we + lon_d + "°" + format2FixedLenght(lon_m,6,3) + "'";

                $("#markerpos").data("lat", lonlat.lat.toFixed(5))
                $("#markerpos").data("lon", lonlat.lon.toFixed(5))

                createPermaLink();
              }

              function createPermaLink(){
                if(!layer_permalink.visibility)
                  return;
                if(!OpenLayers.Util.getElement("permalinkDialog"))
                  return

                // Create Permalink for Layers
                var layersPermalink = permalinkControl.getLayerString();
                layersPermalink = permalinkControl.setFlag(layersPermalink, 'F', layer_permalink.layerId);

                // Generate Permalink for copy and paste
                var url = window.location.href;
                var userURL = url.substr(0, url.lastIndexOf('/')+1)
                userURL += "?zoom=" + map.getZoom(); // add map zoom to string
                userURL += "&lat=" + y2lat(map.getCenter().lat).toFixed(5); // add map zoom to string
                userURL += "&lon=" + x2lon(map.getCenter().lon).toFixed(5); // add map zoom to string

                var lat = $("#markerpos").data("lat")
                if(lat)
                  userURL += "&mlat=" + lat; // add latitude

                var lon = $("#markerpos").data("lon")
                if(lon)
                  userURL += "&mlon=" + $("#markerpos").data("lon"); // add longitude

                var mText = encodeURIComponent(document.getElementById("markerText").value)
                if(mText != "")
                  userURL += "&mtext=" + mText; // add marker text; if empty OSM-permalink JS will ignore the '&mtext'

                userURL += "&layers=" + layersPermalink; // add encoded layers
                OpenLayers.Util.getElement("userURL").innerHTML = userURL; // write contents of userURL to textarea
            }

            function addPermalink() {
                layer_permalink.setVisibility(true);
                var htmlText = "<div id='permalinkDialog' style=\"position:absolute; top:5px; right:5px; cursor:pointer;\">";
                htmlText += "<img src=\"./resources/action/close.gif\" onClick=\"closePermalink();\"/></div>";
                htmlText += "<h3><?=$t->tr("permalinks")?>:</h3><br/>"; // reference to translation.php
                htmlText += "<p><?=$t->tr("markset")?></p>"
                htmlText += "<br /><hr /><br />"

                // Übersetzungen in die PHP-Files reinschreiben; kein Text sollte ohne die Möglichkeit der Bearbeitung hier drin stehen

                htmlText += "<table border=\"0\" width=\"370px\">";
                htmlText += "<tr><td><?=$t->tr("position")?>:</td><td id=\"markerpos\">- - -</td></tr>"; // Lat/Lon of the user's click
                htmlText += "<tr><td><?=$t->tr("description")?>:</td><td><textarea cols=\"25\" rows=\"5\" id=\"markerText\"></textarea></td></tr>"; // userInput
                htmlText += "</td></tr></table>";
                htmlText += "<br /><hr /><br />"
                htmlText += "<?=$t->tr("copynpaste")?>:<br /><textarea onclick=\"this.select();\" cols=\"50\" rows=\"3\" id=\"userURL\"></textarea>"; // secure & convient onlick-solution for copy and paste

                showActionDialog(htmlText);

                $('#markerText').on('keyup', function(evt) {
                  createPermaLink()
                });

                map.events.register("click", layer_permalink, onAddMarker);
                createPermaLink();
            }

            function closePermalink() {
                map.events.unregister("click", layer_permalink, onAddMarker);
                layer_permalink.setVisibility(false);
                closeActionDialog();
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
                    numZoomLevels     : 19,
                    projection        : projMerc,
                    displayProjection : proj4326,
                    eventListeners: {
                        moveend     : mapEventMove,
                        zoomend     : mapEventZoom,
                        click       : mapEventClick,
                        changelayer : mapChangeLayer
                    },
                    controls: [
                        permalinkControl,
                        new OpenLayers.Control.Navigation(),
                        //new OpenLayers.Control.LayerSwitcher(), //only for debugging
                        new OpenLayers.Control.ScaleLine({topOutUnits : "nmi", bottomOutUnits: "km", topInUnits: 'nmi', bottomInUnits: 'km', maxWidth: '40'}),
                        new OpenLayers.Control.MousePositionDM(),
                        new OpenLayers.Control.OverviewMap(),
                        ZoomBar
                    ]
                });

                var bboxStrategyWikipedia = new OpenLayers.Strategy.BBOX( {
                    ratio : 1.1,
                    resFactor: 1
                });

                var poiLayerWikipediaHttp = new OpenLayers.Protocol.HTTP({
                    url: 'api/proxy-wikipedia.php?',
                    params: {
                        'LANG' : language,
                        'thumbs' : 'no'
                    },
                    format: new OpenLayers.Format.KML({
                        extractStyles: true,
                        extractAttributes: true
                    })
                });

                // Select feature ---------------------------------------------------------------------------------------------------------
                // (only one SelectFeature per map is allowed)
                selectControl = new OpenLayers.Control.SelectFeature([],{
                    hover:true,
                    popup:null,
                    addLayer:function(layer){
                        var layers = this.layers;
                        if (layers) {
                            layers.push(layer);
                        } else {
                            layers = [
                                layer
                            ];
                        }
                        this.setLayer(layers);
                    },
                    removePopup:function(){
                        if (this.popup) {
                            this.map.removePopup(this.popup);
                            this.popup.destroy();
                            this.popup = null;
                        }
                    }
                });

                // Add Layers to map-------------------------------------------------------------------------------------------------------
                // Mapnik (Base map)
                layer_mapnik = new OpenLayers.Layer.XYZ('Mapnik', [
                    'http://a.tile.openstreetmap.org/${z}/${x}/${y}.png',
                    'http://b.tile.openstreetmap.org/${z}/${x}/${y}.png',
                    'http://c.tile.openstreetmap.org/${z}/${x}/${y}.png'
                ],{
                    layerId      : 1,
                    wrapDateLine : true
                });
                // Seamark
                layer_seamark = new OpenLayers.Layer.TMS("seamarks", "http://t1.openseamap.org/seamark/",
                    { layerId: 3, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
                // Sport
                layer_sport = new OpenLayers.Layer.TMS("Sport", "http://t1.openseamap.org/sport/",
                    { layerId: 4, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
                //GebcoDepth
                layer_gebco_deepshade = new OpenLayers.Layer.WMS("deepshade", "http:///osm.franken.de:8080/geoserver/wms",
                    {layers: "gebco:deepshade_2014", projection: new OpenLayers.Projection("EPSG:900913"), format:"image/png", transparent:"true"},
                    { layerId: 5, isBaseLayer: false, visibility: false, opacity: 0.6, minResolution: 38.22});
                layer_gebco_deeps_gwc = new OpenLayers.Layer.WMS("deeps_gwc", "http://osm.franken.de:8080/geoserver/gwc/service/wms",
                    {layers: "gebco_new", format:"image/png"},
                    { layerId: 6, isBaseLayer: false, visibility: false, opacity: 0.4});
                // POI-Layer for harbours
                layer_pois = new OpenLayers.Layer.Vector("pois", {
                    layerId: 7,
                    visibility: true,
                    projection: proj4326,
                    displayOutsideMaxExtent:true
                });
                // Bing
                layer_bing_aerial = new Bing({
                    layerId: 12,
                    name: 'Aerial photo',
                    key: 'AuA1b41REXrEohfokJjbHgCSp1EmwTcW8PEx_miJUvZERC0kbRnpotPTzGsPjGqa',
                    type: 'Aerial',
                    isBaseLayer: true,
                    displayOutsideMaxExtent: true,
                    wrapDateLine: true
                });
                // Map download
                layer_download = new OpenLayers.Layer.Vector("Map Download", {
                    layerId: 8,
                    visibility: false
                });
                // Trip planner
                layer_nautical_route = new OpenLayers.Layer.Vector("Trip Planner",
                    { layerId: 9, styleMap: routeStyle, visibility: false, eventListeners: {"featuresadded": NauticalRoute_routeAdded, "featuremodified": NauticalRoute_routeModified}});
                // Grid WGS
                layer_grid = new OpenLayers.Layer.GridWGS("coordinateGrid", {
                    layerId: 10,
                    visibility: true,
                    zoomUnits: zoomUnits
                });
                layer_wikipedia = new OpenLayers.Layer.Vector("Wikipedia World", {
                    layerId: 11,
                    visibility: false,
                    projection: proj4326,
                    strategies: [bboxStrategyWikipedia],
                    protocol: poiLayerWikipediaHttp
                });
                // AIS (from zoom level 8 upwards)
                ais = new Ais(map, selectControl, {
                    layerId: 13
                });
                layer_ais = ais.getLayer();
                // SatPro
                satPro = new SatPro(map, selectControl, {
                    layerId: 14
                });
                layer_satpro = satPro.getLayer();
                // Disaster (15)
                // POI-Layer for tidal scales
                layer_tidalscale = new OpenLayers.Layer.Vector("tidalscale", {
                    layerId: 16,
                    visibility: false,
                    projection: proj4326,
                    displayOutsideMaxExtent:true
                });
                // Permalink
                layer_permalink = new OpenLayers.Layer.Markers("Permalink", {
                    layerId: 17,
                    visibility: false,
                    projection: proj4326
                });
                // Water Depth
                waterDepthTrackPoints10m = new WaterDepthTrackPoints10m(map, selectControl, {
                    layerId: 21
                });
                layer_waterdepth_trackpoints_10m = waterDepthTrackPoints10m.getLayer();

                waterDepthTrackPoints100m = new WaterDepthTrackPoints100m(map, selectControl, {
                    layerId: 18
                });
                layer_waterdepth_trackpoints_100m = waterDepthTrackPoints100m.getLayer();

                // Elevation Profile
                layer_elevation_profile_contours = new OpenLayers.Layer.TMS(
                    'ASTER GDEM Contour Lines (zoom 13-17)',
                    'http://129.206.74.245:8006/tms_il.ashx?',
                    {
                        layerId                 : 19,
                        type                    : 'png',
                        getURL                  : getTileURLAsParams,
                        displayOutsideMaxExtent : true,
                        isBaseLayer             : false,
                        maxResolution           : 19.109257068634033,
                        minResolution           : 1.194328566789627,
                        visibility              : false
                    }
                );
                layer_elevation_profile_hillshade = new OpenLayers.Layer.TMS(
                    'ASTER GDEM & SRTM Hillshade (experimental)',
                    'http://129.206.74.245:8004/tms_hs.ashx?',
                    {
                        layerId                 : 20,
                        type                    : 'png',
                        getURL                  : getTileURLAsParams,
                        displayOutsideMaxExtent : true,
                        isBaseLayer             : false,
                        minResolution           : 19.109257068634033,
                        visibility              : false
                    }
                );

                layer_waterdepth_contours = new OpenLayers.Layer.WMS("Contours", "http:///osm.franken.de/cgi-bin/mapserv.fcgi?",
                    {
                      layers: ['contour','contour2'],
                      numZoomLevels: 22,
                      projection: this.projectionMercator,
                      type: 'png',

                      transparent: true},
                    { layerId: 22, isBaseLayer: false, visibility: false,tileSize: new OpenLayers.Size(1024,1024), });

                map.addLayers([
                    layer_mapnik,
                    layer_bing_aerial,
                    layer_elevation_profile_contours,
                    layer_elevation_profile_hillshade,
                    layer_gebco_deepshade,
                    layer_gebco_deeps_gwc,
                    layer_seamark,
                    layer_grid,
                    layer_pois,
                    layer_tidalscale,
                    layer_wikipedia,
                    layer_nautical_route,
                    layer_sport,
                    layer_ais,
                    layer_satpro,
                    layer_download,
                    layer_permalink,
                    layer_waterdepth_trackpoints_10m,
                    layer_waterdepth_trackpoints_100m,
                    layer_waterdepth_contours,
                ]);

                layer_mapnik.events.register("loadend", null, function(evt) {
                    // The Bing layer will only be displayed correctly after the
                    // base layer is loaded.
                    window.setTimeout(correctBingVisibility, 10);
                });

                if (!map.getCenter()) {
                    jumpTo(lon, lat, zoom);
                }
                // Register featureselect for download tool
                selectControl.addLayer(layer_download);
                layer_download.events.register("featureselected", layer_download, selectedMap);
                // Register featureselect for poi layers
                selectControl.addLayer(layer_nautical_route);
                layer_nautical_route.events.register("featureselected", layer_nautical_route, onFeatureSelectPoiLayers);
                selectControl.addLayer(layer_pois);
                layer_pois.events.register("featureselected", layer_pois, onFeatureSelectPoiLayers);
                selectControl.addLayer(layer_tidalscale);
                layer_tidalscale.events.register("featureselected", layer_tidalscale, onFeatureSelectPoiLayers);
                selectControl.addLayer(layer_wikipedia);
                layer_wikipedia.events.register("featureselected", layer_wikipedia, onFeatureSelectPoiLayers);
                // Activate select control
                map.addControl(selectControl);
                selectControl.activate();
            }

            function clearPoiLayer() {
                harbours = [];
                layer_pois.removeAllFeatures();
            }

            function clearTidalScaleLayer() {
                arrayTidalScales = [];
                layer_tidalscale.removeAllFeatures();
            }

            function onFeatureSelectPoiLayers(event) {
                feature = event.feature;
                if (feature.layer == layer_nautical_route) {
                    feature.style = style_edit;
                } else {
                    selectControl.removePopup();
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
                    selectControl.popup = popup;
                    map.addPopup(popup);
                }
            }

            // Map event listener moved
            function mapEventMove(event) {
                // Set cookie for remembering lat lon values
                setCookie("lat", y2lat(map.getCenter().lat).toFixed(5));
                setCookie("lon", x2lon(map.getCenter().lon).toFixed(5));
                // Update harbour layer
                if (layer_pois.getVisibility() === true) {
                    refreshHarbours();
                }
                // Update tidal scale layer
                if (layer_tidalscale.getVisibility() === true) {
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
                clearTidalScaleLayer();
                if(oldZoom!=zoom) {
                    oldZoom=zoom;
                }
                if (layer_download.getVisibility() === true) {
                    closeMapDownload();
                    addMapDownload();
                }
            }

            function mapEventClick(event) {
                selectControl.removePopup();
            }

            // Map event listener changelayer
            function mapChangeLayer(event) {
                resetLayerCheckboxes();
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
                        var name = item.getElementsByTagName("name")[0].childNodes[0].nodeValue.trim();
                        var link = item.getElementsByTagName("link")[0].childNodes[0].nodeValue.trim();
                        var box  = new OpenLayers.Feature.Vector(bounds.toGeometry(), {
                            name: name,
                            link: link
                        });
                        layer_download.addFeatures(box);
                    }
                }
            }

            function switchMenuTools(toolName, activate) {
                switch (toolName) {
                    case 'download':
                        toggleNauticalRoute(false);
                        togglePermalink(false);
                        toggleMapDownload(activate);
                        break;
                    case 'nautical_route':
                        toggleMapDownload(false);
                        togglePermalink(false);
                        toggleNauticalRoute(activate);
                        break;
                    case 'permalink':
                        toggleMapDownload(false);
                        toggleNauticalRoute(false);
                        togglePermalink(activate);
                        break;
                    default:
                        break;
                }
            }

            function initMenuTools() {
                // The layers will be displayed based on permalink
                // settings. Unfortunately the action dialog will not
                // be generated. This workaround guarantees, that the
                // corresponding action dialog will be generated.
                if (layer_download.getVisibility() === true) {
                    switchMenuTools('download', true);
                }
                if (layer_nautical_route.getVisibility() === true) {
                    switchMenuTools('nautical_route', true);
                }
                if (layer_permalink.getVisibility() === true) {
                    switchMenuTools('permalink', true);
                }

                $('#topmenu2').find('[data-tools]').click(function(evt) {
                    var layerName       = $(evt.currentTarget).data('tools');
                    var checked         = $(evt.currentTarget).find('input').is(':checked');
                    var checkboxClicked = $(evt.target).is('input');

                    if (checkboxClicked) {
                        switchMenuTools(layerName, checked);
                    } else {
                        switchMenuTools(layerName, !checked);
                    }
                });
            }

        </script>
    </head>
    <body onload="init();">
        <div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
        <div style="position:absolute; bottom:48px; left:12px; cursor:pointer;">
            <a id="license_osm"  onClick="showMapKey('license')"><img alt="OSM-Logo" src="resources/icons/OSM-Logo-32px.png" height="32px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_ccbysa" onClick="showMapKey('license')"><img alt="CC by SA" src="resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_bing" href="http://wiki.openseamap.org/wiki/Bing" target="_blank" style="display:none"><img alt="bing" src="resources/icons/bing.png" height="29px"></a>
            <a id="license_marine_traffic" onClick="showMapKey('license')" style="display:none"><img alt="Marine Traffic" src="resources/icons/MarineTrafficLogo.png" height="30px"></a>
            <a id="license_waterdepth" onClick="showMapKey('license')" style="display:none"><img alt="Water Depth" src="resources/icons/depth.jpg" height="32px"></a>
        </div>
        <div id="actionDialog">
            <br>&nbsp;not found&nbsp;<br>&nbsp;
        </div>
        <?php include('classes/topmenu.inc'); ?>
    </body>
</html>
