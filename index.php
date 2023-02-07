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
        <link rel="stylesheet" type="text/css" href="javascript/route/NauticalRoute.css">
        <!-- <script type="text/javascript" src="./javascript/lib/jquery.js"></script> -->
        <!-- <script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/ol@v7.2.2/dist/ol.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.2.2/ol.css">
        <script type="text/javascript" src="./javascript/translation-<?=$t->getCurrentLanguageSafe()?>.js"></script>
        <!-- <script type="text/javascript" src="./javascript/permalink.js"></script> -->
        <script type="text/javascript" src="./javascript/utilities.js"></script>
        <script type="text/javascript" src="./javascript/countries.js"></script>
        <script type="text/javascript" src="./javascript/map_utils.js"></script>
        <script type="text/javascript" src="./javascript/harbours.js"></script>
        <script type="text/javascript" src="./javascript/nominatim.js"></script>
        <script type="text/javascript" src="./javascript/tidal_scale.js"></script>
        <script type="text/javascript" src="./javascript/route/NauticalRoute.js"></script>
        <!--script type="text/javascript" src="./javascript/mouseposition_dm.js"></script>
        <script type="text/javascript" src="./javascript/grid_wgs.js"></script>
        <script type="text/javascript" src="./javascript/bing.js"></script>
        <script type="text/javascript" src="./javascript/ais.js"></script-->
        <!-- <script type="text/javascript" src="./javascript/satpro.js"></script> -->
        <!--script type="text/javascript" src="./javascript/lib/he.js"></script>
        <script type="text/javascript" src="./javascript/waterdepth-trackpoints.js"></script-->
        <script type="text/javascript" src="./javascript/geomagjs/cof2Obj.js"></script>
        <script type="text/javascript" src="./javascript/geomagjs/geomag.js"></script>
        <script type="text/javascript" src="./javascript/mag_deviation.js"></script>
        <script type="text/javascript">

            <?php
                $tempval = parse_ini_file("/var/lib/online-chart/online_chart.ini");
                $osm_server = $tempval['osmserver'];
                echo "var OsmTileServer = ", "\"", $osm_server, "\""
            ?>

            var map;

            // popup
            var popup;
            var content;
            var closer;
            var overlay;

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
//            var layer_gebco_deepshade;             // 5
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
            // var layer_elevation_profile_contours;  // 19
            // var layer_elevation_profile_hillshade;  //20
            var layer_waterdepth_trackpoints_10m;      // 21
            var layer_waterdepth_contours;        // 22

            // To not change the permalink layer order, every removed
            // layer keeps its number. The ArgParser compares the
            // count of layers with the layers argument length. So we
            // have to let him know, how many layers are removed.
            var ignoredLayers = 4;

            // Select controls
            var selectControl;

            // TODO
            // var permalinkControl = new OpenSeaMap.Control.Permalink(null, null, {
            //     ignoredLayers : ignoredLayers
            // });

            // Marker that is pinned at the last location the user searched for and selected.
            var searchedLocationMarker = null;


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

                      var mtext = he.encode(decodeURIComponent(getArgument("mtext")))
                                    .replace(/\n/g, '<br/>');
                      mtext = mtext.replace('&#x3C;b&#x3E;', '<b>')
                                    .replace('&#x3C;%2Fb&#x3E;', '</b>')
                                    .replace('&#x3C;/b&#x3E;', '</b>');
                      addMarker(layer_marker, mLon, mLat, mtext);
                  }
                } catch(err) {
                    console.log(err)
                }
                readLayerCookies();
                resetLayerCheckboxes();
                initMenuTools();
                // Set current language for internationalization
                // OpenLayers.Lang.setCode(language);
            }

            function readLayerCookies() {
                if (getArgument('layers') != -1) {
                    // There is a 'layers' url param -> ignore cookies

                    return;
                }
                // Set Layer visibility from cookie
                var seamarkVisible = getCookie("SeamarkLayerVisible") === "true"
                if(getCookie("SeamarkLayerVisible") === "-1")
                  seamarkVisible = true; // default to visible
                layer_seamark.setVisible(seamarkVisible);

                var poisVisible = getCookie("HarbourLayerVisible") === "true"
                layer_pois.setVisible(poisVisible);

                var tidalVisible = getCookie("TidalScaleLayerVisible") === "true"
                layer_tidalscale.setVisible(tidalVisible);
                if(layer_tidalscale.getVisible()) {
                  refreshTidalScales();
                }

                var sportVisible = getCookie("SportLayerVisible") === "true"
                layer_sport.setVisible(sportVisible);

                var gridVisible = getCookie("GridWGSLayerVisible") === "true"
                if(getCookie("GridWGSLayerVisible") === "-1")
                  gridVisible = true; // default to visible

                layer_grid.setVisible(gridVisible);

                var gebcoVisible = getCookie("GebcoDepthLayerVisible") === "true";
//                layer_gebco_deepshade.setVisible(gebcoVisible);
                layer_gebco_deeps_gwc.setVisible(gebcoVisible);

                var wikiLayerVisible = getCookie("WikipediaLayerVisible") === "true";
                var wikiThumbsVisible = getCookie("WikipediaLayerThumbs") === "true";
                wikipediaThumbs = wikiThumbsVisible;
                setWikiLayer(wikiLayerVisible);

                if (getCookie("BingAerialLayerVisible") == "true") {
                    layer_bing_aerial.setVisible(true);
                    layer_mapnik.setVisible(false);
                }
                var aisVisible = getCookie("AisLayerVisible") === "true"
                layer_ais.setVisible(aisVisible)

                var depth10mVisible = getCookie("WaterDepthTrackPointsLayerVisible10m") === "true"
                layer_waterdepth_trackpoints_10m.setVisible(depth10mVisible);

                var depth100mVisible = getCookie("WaterDepthTrackPointsLayerVisible100m") === "true"
                layer_waterdepth_trackpoints_100m.setVisible(depth100mVisible);

                var contoursVisible = getCookie("WaterDepthContoursVisible") === "true"
                layer_waterdepth_contours.setVisible(contoursVisible);

                showWaterDepthTrackPoints();

                if (getCookie("CompassroseVisible") === "true") {
                    document.getElementById("checkCompassrose").checked = true
                    toggleCompassrose();
                }
            }

            function resetLayerCheckboxes()
            {
                // This method is separated from readLayerCookies because
                // the permalink control also will set the visibility of
                // layers.
                document.getElementById("checkLayerSeamark").checked                = (layer_seamark.getVisible() === true);
                document.getElementById("checkLayerHarbour").checked                = (layer_pois.getVisible() === true);
                document.getElementById("checkLayerTidalScale").checked             = (layer_tidalscale.getVisible() === true);
                document.getElementById("checkLayerSport").checked                  = (layer_sport.getVisible() === true);
                document.getElementById("checkLayerGridWGS").checked                = (layer_grid.getVisible() === true);
                document.getElementById("checkLayerGebcoDepth").checked             = (/*layer_gebco_deepshade.getVisible() === true ||*/ layer_gebco_deeps_gwc.getVisible() === true);
                //document.getElementById("checkDownload").checked                    = (layer_download.getVisible() === true);
                document.getElementById("checkNauticalRoute").checked               = (layer_nautical_route.getVisible() === true);
                document.getElementById("checkLayerWikipedia").checked              = (layer_wikipedia.getVisible() === true);
                document.getElementById("checkLayerWikipediaThumbnails").checked    = (layer_wikipedia.getVisible() === true && wikipediaThumbs === true);
                document.getElementById("checkLayerBingAerial").checked             = (layer_bing_aerial.getVisible() === true);
                document.getElementById("checkLayerAis").checked                    = (layer_ais.getVisible() === true);
                document.getElementById("checkPermalink").checked                   = (layer_permalink.getVisible() === true);
                
                // document.getElementById("checkLayerSatPro").checked                = (layer_satpro.getVisible() === true);
                setWaterDepthBoxes();
                document.getElementById("checkDepthContours").checked                   = (layer_waterdepth_contours.getVisible() === true);
                document.getElementById("checkCompassrose").checked                 = (document.getElementById("compassRose").style.visibility === 'visible');

                // TODO oli
                //createPermaLink();
            }

            // Show popup window for help
            function showMapKey(item) {
                legendWindow = window.open("legend.php?lang=" + language + "&page=" + item, "MapKey", "width=760, height=680, status=no, scrollbars=yes, resizable=yes");
                legendWindow.focus();
            }

            function toggleCompassrose() {
                if (document.getElementById("checkCompassrose").checked) {
                    refreshMagdev();
                    document.getElementById("compassRose").style.visibility = 'visible';
                    setCookie("CompassroseVisible", "true");
                } else {
                    document.getElementById("compassRose").style.visibility = 'hidden';
                    setCookie("CompassroseVisible", "false");
                }
            }

            function showSeamarks() {
                layer_seamark.setVisible(!layer_seamark.getVisible());
            }

            function showHarbours() {
                layer_pois.setVisible(!layer_pois.getVisible());
            }

            function showTidalScale() {
                layer_tidalscale.setVisible(!layer_tidalscale.getVisible());
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
                layer_sport.setVisible(!layer_sport.getVisible());
            }

            function showGridWGS() {
                layer_grid.setVisible(!layer_grid.getVisible());
            }

            function showGebcoDepth() {
                layer_gebco_deeps_gwc.setVisible(!layer_gebco_deeps_gwc.getVisible());
                setCookie("GebcoDepthLayerVisible", layer_gebco_deeps_gwc.getVisible());
            }

            function showBingAerial() {
                if (layer_bing_aerial.getVisible()) {
                    layer_bing_aerial.setVisible(false);
                    layer_mapnik.setVisible(true);
                } else {
                    layer_mapnik.setVisible(false);
                    layer_bing_aerial.setVisible(true);
                }
            }

            function correctBingVisibility() {
                document.getElementById("license_bing").style.display = layer_bing_aerial.getVisible() ? 'inline' : 'none';
                setCookie("BingAerialLayerVisible", '' + layer_bing_aerial.getVisible());
            }

            function showAis() {
                layer_ais.setVisible(!layer_ais.getVisible());
            }

            function showSatPro() {
                layer_satpro.setVisible(!layer_satpro.getVisible());
            }

            function showDisaster() {
                layer_disaster.setVisible(!layer_disaster.getVisible());
           
                if (!layer_disaster.getVisible()) {
                    setCookie("DisasterLayerVisible", "false");
                } else {
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

              var checked = document.getElementById("checkLayerWaterDepthTrackPoints").checked;

              if(!checked){ 
                layer_waterdepth_trackpoints_10m.setVisible(false);
                layer_waterdepth_trackpoints_100m.setVisible(false);
              } else if (
                !layer_waterdepth_trackpoints_10m.getVisible() &&
                !layer_waterdepth_trackpoints_100m.getVisible()
              ) {
                layer_waterdepth_trackpoints_10m.setVisible(true);
              }

             }

            function showWaterDepthTrackPoints(fromClick) {
              setWaterDepthBoxes(fromClick);
            }

            function showWaterDepthTrackPoints10m() {
                layer_waterdepth_trackpoints_10m.setVisible(!layer_waterdepth_trackpoints_10m.getVisible());
            }

            function showWaterDepthTrackPoints100m() {
               layer_waterdepth_trackpoints_100m.setVisible(!layer_waterdepth_trackpoints_100m.getVisible());
            }

            function showContours() {
              layer_waterdepth_contours.setVisible(!layer_waterdepth_contours.getVisible());
            }

            // Show Wikipedia layer
            function showWikipediaLinks(sub) {
                if (sub) {
                  var checked = document.getElementById("checkLayerWikipediaThumbnails").checked

                  if (checked) {
                   setWikiLayer(checked);
                  } 

                  wikipediaThumbs = checked;
                  layer_wikipedia.getSource().refresh();

                } else {
                  // toggle wiki layer
                  setWikiLayer(!layer_wikipedia.getVisible());
                  layer_wikipedia.getSource().refresh();
                }
            }

            function setWikiLayer(visible){
              layer_wikipedia.setVisible(visible);
              if (!visible) {
                selectControl?.getFeatures().clear();
              }
              layer_wikipedia.getSource().refresh();
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
                layer_download.setVisible(true);
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
                layer_download.setVisible(false);
                layer_download.getSource().clear();
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
                var url = "https://sourceforge.net/projects/opennautical/files/Maps" + downloadLink + "ONC-" + downloadName + format + "/download";

                downloadWindow = window.open(url);
            }

            function selectedMap (event) {
                var feature = event.feature;

                downloadName = feature.get('name');
                downloadLink = feature.get('link');

                var mapName = downloadName;

                document.getElementById('info_dialog').innerHTML=""+ mapName +"";
                document.getElementById('buttonMapDownload').disabled=false;
            }

            function addNauticalRoute() {
                layer_nautical_route.setVisible(true);
                var htmlText = "<div style=\"position:absolute; top:5px; right:5px; cursor:pointer;\">";
                htmlText += "<img src=\"./resources/action/delete.png\"  width=\"17\" height=\"17\" onclick=\"if (!routeChanged || confirm('<?=$t->tr("confirmDeleteRoute")?>')) {closeNauticalRoute();addNauticalRoute();}\"/>&nbsp;";
                htmlText += "<img src=\"./resources/action/info.png\"  width=\"17\" height=\"17\" onClick=\"showMapKey('help-trip-planner');\"/>&nbsp;";
                htmlText += "<img src=\"./resources/action/close.gif\" onClick=\"if (!routeChanged || confirm('<?=$t->tr("confirmCloseRoute")?>')) {closeNauticalRoute();}\"/></div>";
                htmlText += "<h3><?=$t->tr("tripPlanner")?>: <input type=\"text\" id=\"tripName\" size=\"20\"></h3><br/>";
                htmlText += "<table border=\"0\" width=\"370px\">";
                htmlText += "<tr><td><?=$t->tr("start")?></td><td id=\"routeStart\">- - -</td></tr>";
                htmlText += "<tr><td><?=$t->tr("finish")?></td><td id=\"routeEnd\">- - -</td></tr>";
                htmlText += "<tr><td><?=$t->tr("distance")?></td><td id=\"routeDistance\">- - -</td></tr>";
                htmlText += "<tr><td id=\"routePoints\" colspan = 2> </td></tr>";
                htmlText += "</table>";
                htmlText += "<input type=\"button\" id=\"buttonRouteDownloadTrack\" value=\"<?=$t->tr("download")?>\" onclick=\"NauticalRoute_DownloadTrack();\" disabled=\"true\">";
                htmlText += "<select id=\"routeFormat\"><option value=\"CSV\"/>CSV<option value=\"GML\"/>GML<option value=\"KML\"/>KML<option value=\"GPX\"/>GPX</select>&nbsp;";
                htmlText += "<select id=\"coordFormat\" onchange=\"NauticalRoute_getPoints(routeTrack);\"><option value=\"coordFormatdms\"/>ggg°mm.mmm'<option value=\"coordFormatd_dec\"/>ggg.gggggg</select>&nbsp;";
                htmlText += "<select id=\"distUnits\" onchange=\"NauticalRoute_getPoints(routeTrack);\"><option value=\"nm\"/>[nm]<option value=\"km\"/>[km]</select>";

                showActionDialog(htmlText);
                NauticalRoute_startEditMode();
            }

            function closeNauticalRoute() {
                layer_nautical_route.setVisible(false);
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
                const markerpos = document.getElementById('markerpos');
                markerpos.innerHTML = ns + lat_d + "°" + format2FixedLenght(lat_m,6,3) + "'" + " " + we + lon_d + "°" + format2FixedLenght(lon_m,6,3) + "'";
                markerpos.lat = lonlat.lat.toFixed(5);
                markerpos.lon = lonlat.lon.toFixed(5);

                createPermaLink();
              }

              function createPermaLink(){
                if(!layer_permalink.getVisible())
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

                const markerpos = document.getElementById('markerpos');
                var lat = markerpos.lat;
                if(lat)
                  userURL += "&mlat=" + lat; // add latitude

                var lon = markerpos.lon;
                if(lon) {
                  userURL += "&mlon=" + lon; // add longitude
                }
                var mText = encodeURIComponent(document.getElementById("markerText").value)
                if(mText != "")
                  userURL += "&mtext=" + mText; // add marker text; if empty OSM-permalink JS will ignore the '&mtext'

                userURL += "&layers=" + layersPermalink; // add encoded layers
                OpenLayers.Util.getElement("userURL").innerHTML = userURL; // write contents of userURL to textarea
            }

            function addPermalink() {
                layer_permalink.setVisible(true);
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

                document.getElementById('markerText').addEventListener("keyup",function(evt) {
                  createPermaLink()
                });

                map.events.register("click", layer_permalink, onAddMarker);
                createPermaLink();
            }

            function closePermalink() {
                map.events.unregister("click", layer_permalink, onAddMarker);
                layer_permalink.setVisible(false);
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
                    htmlText += "<tr style=\"cursor:pointer;\" onmouseover=\"this.style.backgroundColor = '#ADD8E6';\"onmouseout=\"this.style.backgroundColor = '#FFF';\" onclick=\"jumpToSearchedLocation(" + placeLon + ", " + placeLat + ");\"><td  valign=\"top\"><b>" + placeName + "</b></td><td>" + description + "</td></tr>";
                }
                htmlText += "<tr><td>&nbsp;</td><td align=\"right\"><br/><input type=\"button\" id=\"buttonMapClose\" value=\"<?=$t->tr("close")?>\" onclick=\"closeActionDialog();\"></td></tr></table>";
                showActionDialog(htmlText);
            }

            function drawmap() {
                popup = document.getElementById('popup');
                content = document.getElementById('popup-content');
                closer = document.getElementById('popup-closer');
                overlay = new ol.Overlay({
                    element: popup,
                    autoPan: {
                        animation: {
                            duration: 250,
                        },
                    },  
                });
                // close the popup
                closer.onclick = function () {
                    overlay.setPosition(undefined);
                    closer.blur();
                    return false;
                };

                map = new ol.Map({
                    target: 'map',
                    overlays: [overlay],
                    view: new ol.View({
                        maxZoom     : 19,
                        // displayProjection : proj4326,
                        // eventListeners: {
                        //     moveend     : mapEventMove,
                        //     zoomend     : mapEventZoom,
                        //     click       : mapEventClick,
                        //     changelayer : mapChangeLayer
                        // },
                        // controls: [
                            // permalinkControl,
                            // new OpenLayers.Control.Navigation(),
                            // //new OpenLayers.Control.LayerSwitcher(), //only for debugging
                            // new OpenLayers.Control.ScaleLine({
                            //     topOutUnits : "nmi",
                            //     bottomOutUnits: "km",
                            //     topInUnits: 'nmi', 
                            //     bottomInUnits: 'km', 
                            //     maxWidth: Math.max(0.2*$(window).width(),0.2*$(window).height()).toFixed(0), 
                            //     geodesic: true
                            // }),
                            // new OpenLayers.Control.MousePositionDM(),
                            // new OpenLayers.Control.OverviewMap(),
                            // ZoomBar
                        // ]
                    }),
                });

                map.addControl(new ol.control.ScaleLine({
                    className: 'ol-scale-line-metric'
                }));
                map.addControl(new ol.control.ScaleLine({
                    className: 'ol-scale-line-nautical',
                    units: "nautical",
                }));
                map.addControl(new ol.control.ZoomSlider());
                map.addControl(new ol.control.MousePosition({
                    coordinateFormat: (coordinate) => {
                        const [lon, lat] = ol.proj.toLonLat(coordinate);
                        var ns = lat >= 0 ? 'N' : 'S';
                        var we = lon >= 0 ? 'E' : 'W';
                        var lon_m = Math.abs(lon*60).toFixed(3);
                        var lat_m = Math.abs(lat*60).toFixed(3);
                        var lon_d = Math.floor (lon_m/60);
                        var lat_d = Math.floor (lat_m/60);
                        lon_m -= lon_d*60;
                        lat_m -= lat_d*60;
                        return "Zoom:" + map.getView().getZoom().toFixed(0) + " " + ns + lat_d + "&#176;" + format2FixedLenght(lat_m,6,3) + "'" + "&#160;" +
                            we + lon_d + "&#176;" + format2FixedLenght(lon_m,6,3) + "'" ;
                    },
                }));
                map.on('moveend', mapEventMove);
                map.on('moveend', mapEventZoom);
                map.on('pointermove', mapEventPointerMove);
                map.on('singleclick', mapEventClick);

                // TODO oli
                // var bboxStrategyWikipedia = new OpenLayers.Strategy.BBOX( {
                //     ratio : 1.1,
                //     resFactor: 1
                // });

                // Select feature ---------------------------------------------------------------------------------------------------------
                // (only one SelectFeature per map is allowed)
                // TODO oli
                // selectControl = new OpenLayers.Control.SelectFeature([],{
                //     hover:true,
                //     popup:null,
                //     addLayer:function(layer){
                //         var layers = this.layers;
                //         if (layers) {
                //             layers.push(layer);
                //         } else {
                //             layers = [
                //                 layer
                //             ];
                //         }
                //         this.setLayer(layers);
                //     },
                //     removePopup:function(){
                //         if (this.popup) {
                //             this.map.removePopup(this.popup);
                //             this.popup.destroy();
                //             this.popup = null;
                //         }
                //     }
                // });
                selectControl = new ol.interaction.Select();

                // Add Layers to map-------------------------------------------------------------------------------------------------------

                // Mapnik (Base map)
                // TODO oli
                // layer_mapnik = new OpenLayers.Layer.XYZ('Mapnik',
                //                                         GetOsmServer(),
                //                                         { layerId      : 1,
                //                                           wrapDateLine : true
                //                                         });
                const osmUrl = OsmTileServer == "BRAVO" ? 'https://t2.openseamap.org/tile/{z}/{x}/{y}.png' : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
                layer_mapnik = new ol.layer.Tile({
                    source: new ol.source.OSM({
                        url: osmUrl
                    }),
                    properties: {
                        name: 'Mapnik',
                        layerId: 1,
                        wrapDateLine:true
                    }
                });

                

                // Seamark
                // TODO oli
                // layer_seamark = new OpenLayers.Layer.TMS("seamarks",
                // "https://tiles.openseamap.org/seamark/",
                //     { layerId: 3, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
                layer_seamark = new ol.layer.Tile({
                    visible: true,
                    minZoom: 9,
                    maxZom: 19,
                    source: new ol.source.XYZ({
                        // url: "https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png"
                        tileUrlFunction: function(coordinate) {
                            return getTileUrlFunction("https://tiles.openseamap.org/seamark/", 'png', coordinate);
                            // return "https://tiles.openseamap.org/seamark/" + coordinate[0] + '/' +
                            //     coordinate[1] + '/' + (-coordinate[2] - 1) + '.png';
                        }
                    }),
                    properties: {
                        name: 'seamarks',
                        layerId: 3,
                        wrapDateLine:true,
                        numZoomLevels: 19, 
                        type: 'png', 
                        getURL:getTileURL, 
                        isBaseLayer:false, 
                        displayOutsideMaxExtent:true
                    }
                });

                layer_seamark.on("change:visible", (evt) => {
                    document.getElementById("checkLayerSeamark").checked = evt.target.getVisible();
                    setCookie("SeamarkLayerVisible", evt.target.getVisible());
                });

                // Sport
                // TODO oli
                // layer_sport = new OpenLayers.Layer.TMS("Sport", "https://tiles.openseamap.org/sport/",
                //     { layerId: 4, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
                layer_sport = new ol.layer.Tile({
                    visible: false,
                    maxZom: 19,
                    properties: {
                        name: 'Sport',
                        layerId: 4,
                        numZoomLevels: 19,
                        isBaseLayer:false,
                        displayOutsideMaxExtent:true
                    },
                    source: new ol.source.XYZ({
                        // url: "https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png"
                        tileUrlFunction: function(coordinate) {
                            return getTileUrlFunction("https://tiles.openseamap.org/sport/", 'png', coordinate);
                            // return "https://tiles.openseamap.org/seamark/" + coordinate[0] + '/' +
                            //     coordinate[1] + '/' + (-coordinate[2] - 1) + '.png';
                        }
                    }),
                });
                layer_sport.on("change:visible", (evt) => {
                    document.getElementById("checkLayerSport").checked = evt.target.getVisible();
                    setCookie("SportLayerVisible", evt.target.getVisible());
                });

                //GebcoDepth
                // TODO oli
                // layer_gebco_deeps_gwc = new OpenLayers.Layer.WMS("gebco_2021", "https://depth.openseamap.org/geoserver/gwc/service/wms",
                //     {layers: "gebco2021:gebco_2021", format:"image/png"},
                //     { layerId: 6, isBaseLayer: false, visibility: false, opacity: 0.8});
                layer_gebco_deeps_gwc = new ol.layer.Tile({
                    visible: false,
                    opacity: 0.8,
                    properties: {
                        name:"gebco_2021",
                        layerId: 6,
                        isBaseLayer: false,
                    },
                    source: new ol.source.TileWMS({
                        url: 'https://depth.openseamap.org/geoserver/gwc/service/wms',
                        params: {'LAYERS': 'gebco2021:gebco_2021', 'VERSION':'1.1.1'},
                        ratio: 1,
                        serverType: 'geoserver',
                    }),
                }),
                layer_gebco_deeps_gwc.on("change:visible", (evt) => {
                    document.getElementById("checkLayerGebcoDepth").checked = evt.target.getVisible();
                    setCookie("GebcoDepthLayerVisible", evt.target.getVisible());
                });
                
                // POI-Layer for harbours
                // TODO oli
                // layer_pois = new OpenLayers.Layer.Vector("pois", {
                //     layerId: 7,
                //     visibility: true,
                //     projection: proj4326,
                //     displayOutsideMaxExtent:true
                // });
                layer_pois = new ol.layer.Vector({
                    visible: true,
                    properties: {
                        name: "pois",
                        layerId: 7,
                    },
                    source: new ol.source.Vector({
                        features: [],
                    }),
                });
                layer_pois.on("change:visible", (evt) => {
                    document.getElementById("checkLayerHarbour").checked = evt.target.getVisible();
                    setCookie("HarbourLayerVisible", evt.target.getVisible());

                    if (!evt.target.getVisible()) {
                        clearPoiLayer();
                    } else {
                        refreshHarbours();
                    }
                });
                
                // Bing
                // TODO oli
                // layer_bing_aerial = new Bing({
                //     layerId: 12,
                //     name: 'Aerial photo',
                //     key: 'AuA1b41REXrEohfokJjbHgCSp1EmwTcW8PEx_miJUvZERC0kbRnpotPTzGsPjGqa',
                //     type: 'Aerial',
                //     isBaseLayer: true,
                //     displayOutsideMaxExtent: true,
                //     wrapDateLine: true
                // });
                layer_bing_aerial = new ol.layer.Tile({
                    visible: false,
                    preload: Infinity,
                    properties: {
                        name: 'Aerial photo',
                        layerId: 12,
                        isBaseLayer: true,
                    },
                    source: new ol.source.BingMaps({
                        key: 'AuA1b41REXrEohfokJjbHgCSp1EmwTcW8PEx_miJUvZERC0kbRnpotPTzGsPjGqa',
                        imagerySet: 'Aerial',
                        // use maxZoom 19 to see stretched tiles instead of the BingMaps
                        // "no photos at this zoom level" tiles
                        maxZoom: 19
                    }),
                });

                layer_bing_aerial.on('change:visible', (evt) => {
                    correctBingVisibility();
                    document.getElementById("checkLayerBingAerial").checked = evt.target.getVisible();
                    setCookie("BingAerialLayerVisible", evt.target.getVisible());
                
                });
                
                // Map download
                // TODO oli
                // layer_download = new OpenLayers.Layer.Vector("Map Download", {
                //     layerId: 8,
                //     visibility: false
                // });
                layer_download = new ol.layer.Vector({
                    visible: false,
                    properties: {
                        name: 'Map Download',
                        layerId: 8,
                    },
                    source: new ol.source.Vector({features:[]}),
                });

                // Trip planner
                // TODO oli
                // layer_nautical_route = new OpenLayers.Layer.Vector("Trip Planner",
                //     { layerId: 9, styleMap: routeStyle, visibility: false, eventListeners: {"featuresadded": NauticalRoute_routeAdded, "featuremodified": NauticalRoute_routeModified}});
                
                layer_nautical_route = new ol.layer.Vector({
                    visible: false,
                    properties: {
                        name: 'Trip Planner',
                        layerId: 9,
                    },
                    source: new ol.source.Vector({features:[]}),
                });
                layer_nautical_route.on("change:visible", (evt) => {
                    document.getElementById("checkNauticalRoute").checked = evt.target.getVisible();
                    setCookie("NauticalRouteLayerVisible", evt.target.getVisible());
                });

                // Grid WGS
                // TODO oli
                // layer_grid = new OpenLayers.Layer.GridWGS("coordinateGrid", {
                //     layerId: 10,
                //     visibility: true,
                //     zoomUnits: zoomUnits
                // });
                layer_grid = new ol.layer.Graticule({
                    visible: true,
                    properties: {
                        name: "coordinateGrid",
                        layerId: 10,
                    },
                    // the style to use for the lines, optional.
                    strokeStyle: new ol.style.Stroke({
                        color: 'rgba(0,0,0,1)',
                        width: 1,
                        // lineDash: [0.5, 4],
                    }),
                    showLabels: true,
                    wrapX: true,
                });
                layer_grid.on("change:visible", (evt) => {
                    document.getElementById("checkLayerGridWGS").checked = evt.target.getVisible();
                    setCookie("GridWGSLayerVisible", evt.target.getVisible());
                });

                // TODO oli
                // var poiLayerWikipediaHttp = new OpenLayers.Protocol.HTTP({
                //     url: 'api/proxy-wikipedia.php?',
                //     params: {
                //         'LANG' : language,
                //         'thumbs' : 'no'
                //     },
                //     format: new OpenLayers.Format.KML({
                //         extractStyles: true,
                //         extractAttributes: true
                //     })
                // });
                // layer_wikipedia = new OpenLayers.Layer.Vector("Wikipedia World", {
                //     layerId: 11,
                //     visibility: false,
                //     projection: proj4326,
                //     strategies: [bboxStrategyWikipedia],
                //     protocol: poiLayerWikipediaHttp
                // });
                layer_wikipedia = new ol.layer.Vector({
                    visible: false,
                    properties: {
                        name: "Wikipedia World",
                        layerId: 11,
                    },
                    source: new ol.source.Vector({
                        features: [],
                        strategy: ol.loadingstrategy.bbox,
                        format: new ol.format.KML({
                            extractStyles: true,
                        }),
                        loader: function(extent, resolution, projection, success, failure) {
                            document.getElementById("checkLayerWikipediaThumbnails").checked = wikipediaThumbs;
                            setCookie("WikipediaLayerThumbs", wikipediaThumbs);
               
                            const proj = projection.getCode();
                            const bbox = ol.proj.transformExtent(extent, map.getView().getProjection(), 'EPSG:4326');
                            // Beforee it used the api/prox-wikipedia.php but i seems to work without the proxy
                            const url = 'https://wp-world.toolforge.org/marks.php?' + 'LANG=' + language + '&thumbs=' + (wikipediaThumbs ? 'yes' : 'no') +'&bbox=' + bbox.join(',');
                            const xhr = new XMLHttpRequest();
                            xhr.open('GET', url);
                            const vectorSource = this;
                            const onError = function() {
                                vectorSource.removeLoadedExtent(extent);
                                failure();
                            };
                            xhr.onerror = onError;
                            xhr.onload = function() {
                                if (xhr.status == 200) {
                                    const features = vectorSource.getFormat().readFeatures(xhr.responseText, {featureProjection: "EPSG:3857"});
                                    vectorSource.addFeatures(features);
                                    success(features);
                                } else {
                                    onError();
                                }
                            }
                            xhr.send();
                        },
                    }),
                });
                layer_wikipedia.on("change:visible", (evt) => {
                    document.getElementById("checkLayerWikipedia").checked = evt.target.getVisible();
                    setCookie("WikipediaLayerVisible", evt.target.getVisible());

                    if (!evt.target.getVisible()) {
                        document.getElementById("checkLayerWikipediaThumbnails").checked = false;
                        setCookie("WikipediaLayerThumbs", false);               
                    }
                });

                // TODO oli
                // layer_ais = new OpenLayers.Layer.TMS("Marinetraffic", "https://tiles.marinetraffic.com/ais_helpers/shiptilesingle.aspx?output=png&sat=1&grouping=shiptype&tile_size=512&legends=1&zoom=${z}&X=${x}&Y=${y}",
                //     { layerId: 13, numZoomLevels: 19, type: 'png', getURL:getTileURLMarine, isBaseLayer:false, displayOutsideMaxExtent:true, tileSize    : new OpenLayers.Size(512,512)
                //   });
                layer_ais = new ol.layer.Tile({
                    visible: false,
                    maxZom: 19,
                    properties: {
                        name: 'Marinetraffic',
                        layerId: 13,
                    },
                    source: new ol.source.XYZ({
                       tileUrlFunction: function(coordinate) {
                            return getTileURLMarine("https://tiles.marinetraffic.com/ais_helpers/shiptilesingle.aspx?output=png&sat=1&grouping=shiptype&tile_size=256&legends=1&zoom=${z}&X=${x}&Y=${y}", coordinate);
                        },
                    }),
                });
                layer_ais.on("change:visible", (evt) => {
                    document.getElementById("checkLayerAis").checked = evt.target.getVisible();
                    setCookie("AisLayerVisible", evt.target.getVisible());
                });

                // SatPro
                // TODO oli
                // satPro = new SatPro(map, selectControl, {
                //     layerId: 14
                // });
                // layer_satpro = satPro.getLayer();                
                // Disaster (15)
                layer_satpro = new ol.layer.Vector({
                    visible: false,
                    properties: {
                        name: "SatPro",
                        layerId: 14,
                    },
                    source: new ol.source.Vector({
                        features: [],
                        strategy: ol.loadingstrategy.bbox,
                        loader: function(extent, resolution, projection, success, failure) {               
                            const proj = projection.getCode();
                            const bbox = ol.proj.transformExtent(extent, map.getView().getProjection(), 'EPSG:4326');
                            // Beforee it used the api/prox-wikipedia.php but i seems to work without the proxy
                            const url = 'api/getSatPro.php?' + 'bbox=' + bbox.join(',');
                            const xhr = new XMLHttpRequest();
                            xhr.open('GET', url);
                            const vectorSource = this;
                            const onError = function() {
                                vectorSource.removeLoadedExtent(extent);
                                failure();
                            };
                            xhr.onerror = onError;
                            xhr.onload = function() {
                                if (xhr.status == 200) {
                                    var lines = xhr.responseText.split('\n');
                                    var features = [];
                                    var trackPoints = [];
                                    var actualName = '';
                                    for (var i = 0; i < lines.length; i++) {
                                        var line = lines[i];
                                        var vals = line.split('\t');
                                        if (vals.length === 1) {
                                            // New vessel, but process previous vessel first
                                            if (trackPoints.length > 1) {
                                                // Track line
                                                const lineArr = trackPoints.map((trackPoint)=> {
                                                    return ol.proj.fromLonLat([trackPoint.lon, trackPoint.lat]);
                                                });
                                                const lineFeature = new ol.Feature(new ol.geom.LineString(lineArr));
                                                lineFeature.set('type','line');                                                
                                                features.push(lineFeature);

                                                // Track points (ignore first)
                                                for (var j = 1; j < trackPoints.length; j++) {
                                                    const point = new ol.geom.Point(ol.proj.fromLonLat([trackPoints[j].lon, trackPoints[j].lat]));
                                                    const pointFeature = new ol.Feature(point);
                                                    pointFeature.setProperties(trackPoints[j]);
                                                    pointFeature.set('type', 'point');
                                                    features.push(pointFeature);
                                                    lineArr.push(point.clone());
                                                }

                                                // Vessel                                             
                                                const vessel = new ol.Feature(new ol.geom.Point(ol.proj.fromLonLat([trackPoints[0].lon, trackPoints[0].lat])));
                                                vessel.setProperties(attributes);
                                                vessel.set('type', 'actual');
                                                features.push(vessel);
                                            }
                                            actualName = vals[0];
                                            trackPoints = [];
                                        } else if (vals.length === 10) {
                                            // Tracks for vessel
                                            var attributes = {
                                                name            : actualName,
                                                lat             : parseFloat(vals[0]),
                                                lon             : parseFloat(vals[1]),
                                                terminal        : vals[2],
                                                datum           : vals[3],
                                                uhrzeit         : vals[4],
                                                breite          : parseFloat(vals[0]),
                                                laenge          : parseFloat(vals[1]),
                                                hoehe           : vals[5],
                                                temperatur      : vals[6],
                                                batterie        : vals[7],
                                                geschwindigkeit : vals[8],
                                                richtung        : vals[9]
                                            };
                                            trackPoints.push(attributes);
                                        }
                                    }
                                    vectorSource.addFeatures(features);
                                    success(features);
                                } else {
                                    onError();
                                }
                            }
                            xhr.send();
                        },
                    }),
                });
                layer_satpro.on("change:visible", (evt) => {
                    document.getElementById("checkLayerSatPro").checked = evt.target.getVisible();
                    setCookie("SatProLayerVisible", evt.target.getVisible());
                });


                // POI-Layer for tidal scales
                // TODO oli
                // layer_tidalscale = new OpenLayers.Layer.Vector("tidalscale", {
                //     layerId: 16,
                //     visibility: false,
                //     projection: proj4326,
                //     displayOutsideMaxExtent:true
                // });
                layer_tidalscale = new ol.layer.Vector({
                    visible: false,
                    properties: {
                        name: 'tidalscale',
                        layerId: 16,
                    },
                    source: new ol.source.Vector({features:[]}),
                });
                layer_tidalscale.on("change:visible", (evt) => {
                    document.getElementById("checkLayerTidalScale").checked = evt.target.getVisible();
                    setCookie("TidalScaleLayerVisible", evt.target.getVisible());
                    if (!evt.target.getVisible()) {
                        clearTidalScaleLayer();
                    } else {
                        refreshTidalScales();
                    }
                });


                // Permalink
                // TODO oli
                // layer_permalink = new OpenLayers.Layer.Markers("Permalink", {
                //     layerId: 17,
                //     visibility: false,
                //     projection: proj4326
                // });
                layer_permalink = new ol.layer.Vector({
                    visible: false,
                    source: new ol.source.Vector(),
                    properties:{
                        name: "Permalink",
                        layerId: 17 // invalid layerId -> will be ignored by layer visibility setup
                    }
                });

                // Water Depth
                // TODO oli
                // waterDepthTrackPoints10m = new WaterDepthTrackPoints10m(map, selectControl, {
                //     layerId: 21
                // });
                // layer_waterdepth_trackpoints_10m = waterDepthTrackPoints10m.getLayer();
                layer_waterdepth_trackpoints_10m = new ol.layer.Tile({
                    visible: false,
                    properties:{
                        name: 'Water Depth Track Points',
                        layerId: 21,
                    },
                    source: new ol.source.TileWMS({
                        url: 'http://osm.franken.de/cgi-bin/mapserv.fcgi',
                        params: {
                            'TRANSPARENT': 'TRUE',
                            'LAYERS': [
                                'trackpoints_cor1_test_dbs_10',
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
                            ].join(','), 'VERSION':'1.1.1'},
                        ratio: 1,
                        serverType: 'mapserver',
                        tileLoadFunction:(imageTile, src) => {
                            imageTile.getImage().src = src.replace('3857', '900913');
                        }
                    }),
                });

                layer_waterdepth_trackpoints_10m.on('change:visible', (evt) => {
                    if (evt.target.getVisible()) {
                        layer_waterdepth_trackpoints_100m.setVisible(false);
                        const parentCheckbox = document.getElementById('checkLayerWaterDepthTrackPoints');
                        if (!parentCheckbox.checked) {
                          parentCheckbox.checked = true;
                        }
                    }
                    document.getElementById("checkLayerWaterDepthTrackPoints10m").checked = evt.target.getVisible();
                    setCookie("WaterDepthTrackPointsLayerVisible10m", evt.target.getVisible());
                });

                // waterDepthTrackPoints100m = new WaterDepthTrackPoints100m(map, selectControl, {
                //     layerId: 18
                // });
                // layer_waterdepth_trackpoints_100m = waterDepthTrackPoints100m.getLayer();
                layer_waterdepth_trackpoints_100m = new ol.layer.Tile({
                    visible: false,
                    properties:{
                        name: 'Water Depth Track Points',
                        layerId: 18,
                    },
                    source: new ol.source.TileWMS({
                        url: 'http://osm.franken.de/cgi-bin/mapserv.fcgi?SRS=EPSG:900913&',
                        params: {
                            'TRANSPARENT': 'TRUE',
                            'LAYERS': [
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
                            ].join(','),
                            'VERSION':'1.3.0'},
                        ratio: 1,
                        serverType: 'mapserver',
                        tileLoadFunction:(imageTile, src) => {
                            imageTile.getImage().src = src.replace('3857', '900913');
                        }
                    }),
                });
                layer_waterdepth_trackpoints_100m.on('change:visible', (evt)=>{
                    if (evt.target.getVisible()) {
                        layer_waterdepth_trackpoints_10m.setVisible(false);
                        const parentCheckbox = document.getElementById('checkLayerWaterDepthTrackPoints');
                        if (!parentCheckbox.checked) {
                          parentCheckbox.checked = true;
                        }
                    }
                    document.getElementById("checkLayerWaterDepthTrackPoints100m").checked = evt.target.getVisible();
                    setCookie("WaterDepthTrackPointsLayerVisible100m", evt.target.getVisible());
                });
               
                // layer_waterdepth_contours = new OpenLayers.Layer.WMS("Contours", "http:///osm.franken.de/cgi-bin/mapserv.fcgi?",
                //     {
                //       layers: ['contour','contour2'],
                //       numZoomLevels: 22,
                //       projection: this.projectionMercator,
                //       type: 'png',

                //       transparent: true},
                //     { layerId: 22, isBaseLayer: false, visibility: false,tileSize: new OpenLayers.Size(1024,1024), });
                layer_waterdepth_contours =new ol.layer.Tile({
                    visible: false,
                    maxZoom: 22,
                    properties:{
                        name: 'Contours',
                        layerId: 22,
                    },
                    source: new ol.source.TileWMS({
                        url: 'http://osm.franken.de/cgi-bin/mapserv.fcgi?SRS=EPSG:900913&',
                        params: {
                            'TRANSPARENT': 'TRUE',
                            'LAYERS': [
                                'contour','contour2'
                            ].join(','),
                            'VERSION':'1.3.0'},
                        ratio: 1,
                        serverType: 'mapserver',
                        tileLoadFunction:(imageTile, src) => {
                            imageTile.getImage().src = src.replace('3857', '900913');
                        }
                    }),
                });
                layer_waterdepth_contours.on("change:visible", (evt) => {
                    document.getElementById("checkDepthContours").checked = evt.target.getVisible();
                    setCookie("WaterDepthContoursVisible", evt.target.getVisible());
                })

                const waterDepthLayers = [layer_waterdepth_trackpoints_100m, layer_waterdepth_trackpoints_10m,layer_waterdepth_contours];
                waterDepthLayers.forEach((layer)=> {
                    layer.on('change:visible', () => {
                        const showCopyright = waterDepthLayers.find((l) => l.getVisible());
                        document.getElementById("license_waterdepth").style.display = showCopyright ? 'inline' : 'none';
                        
                    })
                });

                layer_marker = new ol.layer.Vector({
                    source: new ol.source.Vector(),
                    properties:{
                        name: "Marker",
                        layerId: -2 // invalid layerId -> will be ignored by layer visibility setup
                    }
                });

                [
                    layer_mapnik,
                    layer_bing_aerial,
//                    layer_gebco_deepshade,
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
                    layer_marker,
                ].forEach((layer)=> {
                    map.addLayer(layer);
                });

                // TODO oli
                // layer_mapnik.events.register("loadend", null, function(evt) {
                //     // The Bing layer will only be displayed correctly after the
                //     // base layer is loaded.
                //     window.setTimeout(correctBingVisibility, 10);
                // });

                if (!map.getView().getCenter()) {
                    jumpTo(lon, lat, zoom);
                }

                // // TODO oli
                // // Register featureselect for download tool
                // selectControl.addLayer(layer_download);
                // layer_download.events.register("featureselected", layer_download, selectedMap);
                // // Register featureselect for poi layers
                // selectControl.addLayer(layer_nautical_route);
                // layer_nautical_route.events.register("featureselected", layer_nautical_route, onFeatureSelectPoiLayers);
                // selectControl.addLayer(layer_pois);
                // layer_pois.events.register("featureselected", layer_pois, onFeatureSelectPoiLayers);
                // selectControl.addLayer(layer_tidalscale);
                // layer_tidalscale.events.register("featureselected", layer_tidalscale, onFeatureSelectPoiLayers);
                // selectControl.addLayer(layer_wikipedia);
                // layer_wikipedia.events.register("featureselected", layer_wikipedia, onFeatureSelectPoiLayers);
                // // Activate select control
                // map.addControl(selectControl);
                // selectControl.activate();
            }

            function clearPoiLayer() {
                harbours = [];
                if (layer_pois) {
                  layer_pois.getSource().clear();
                }
            }

            function clearTidalScaleLayer() {
                arrayTidalScales = [];
                if (layer_tidalscale) {
                  layer_tidalscale.getSource().clear();
                }
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
                setCookie("lat", y2lat(map.getView().getCenter()[1]).toFixed(5));
                setCookie("lon", x2lon(map.getView().getCenter()[0]).toFixed(5));

                // Update harbour layer
                if (layer_pois && layer_pois.getVisible() === true) {
                    refreshHarbours();
                }
                // Update tidal scale layer
                if (layer_tidalscale && layer_tidalscale.getVisible() === true) {
                    refreshTidalScales();
                }
                // Update magnetic deviation
                if (document.getElementById("compassRose").style.visibility === 'visible') {
                    refreshMagdev();
                }
            }

            // Map event listener Zoomed
            function mapEventZoom(event) {
                zoom = map.getView().getZoom();
                // Set cookie for remembering zoomlevel
                setCookie("zoom",zoom);
                if(oldZoom != zoom) {
                    oldZoom = zoom;
                }
                // Clear POI layer
                clearPoiLayer();
                clearTidalScaleLayer();
                if (layer_download && layer_download.getVisible() === true) {
                    closeMapDownload();
                    addMapDownload();
                }
            }

            function openPopup(feature, coordinate) {
                let html = feature.get('popupContentHTML');

                if (feature.get('name')) {
                    content = '<b>'+feature.get('name') +'</b><br>'+ feature.get('description');
                }

                content.innerHTML = html;
                overlay.setPosition(coordinate);
            }

            function getFeaturesAtPixel(pixel) {
                const features = map.getFeaturesAtPixel(pixel, {
                    layerFilter: (layer) => layer.getSource()?.getFeaturesAtCoordinate,
                    hitTolerance: 5,
                }).filter((feature)=> {
                    return feature.get('popupContentHTML') || feature.get('name');
                });
                
                return features;
            }

            function mapEventPointerMove(event) {
                const features = getFeaturesAtPixel(event.pixel);
                map.getTargetElement().style.cursor = features.length ? 'pointer' : 'default';
            }

            function mapEventClick(event) {
                const features = getFeaturesAtPixel(event.pixel);

                if (features.length) {   
                    openPopup(features[features.length-1], event.coordinate);
                } else {
                    overlay.setPosition();
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
                        var bounds = ol.proj.transformExtent([w, s, e, n], "EPSG:4326", map.getView().getProjection());
                        var name = item.getElementsByTagName("name")[0].childNodes[0].nodeValue.trim();
                        var link = item.getElementsByTagName("link")[0].childNodes[0].nodeValue.trim();
                        const [minX, minY, maxX, maxY] = bounds;
                        var box  = new ol.Feature(new ol.geom.Polygon([[
                            [minX, minY],
                            [minX, maxY],
                            [maxX, maxY],
                            [maxX, minY],
                            [minX, minY],
                        ]]));
                        box.setProperties({
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
                        toggleCompassrose(false);
                        toggleMapDownload(activate);
                        break;
                    case 'nautical_route':
                        // toggleMapDownload(false);
                        // togglePermalink(false);
                        // toggleCompassrose(false);
                        toggleNauticalRoute(activate);
                        break;
                    case 'permalink':
                        toggleMapDownload(false);
                        toggleNauticalRoute(false);
                        toggleCompassrose(false);
                        togglePermalink(activate);
                        break;
                    case 'compassRose':
                        toggleMapDownload(false);
                        toggleNauticalRoute(false);
                        togglePermalink(false);
                        toggleCompassrose(activate);
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
                if (layer_download.getVisible()) {
                    switchMenuTools('download', true);
                }
                console.log(layer_nautical_route.getVisible());
                if (layer_nautical_route.getVisible()) {
                    switchMenuTools('nautical_route', true);
                }
                if (layer_permalink.getVisible()) {
                    switchMenuTools('permalink', true);
                }

                document.querySelectorAll('#topmenu2 [data-tools]').forEach((elt) => {
                    elt.addEventListener('click', function(evt) {
                        var layerName       = evt.currentTarget.getAttribute('data-tools');
                        var checked         = evt.currentTarget.querySelector('input').checked;
                        var checkboxClicked = evt.target.nodeName === 'INPUT';

                        if (checkboxClicked) {
                            switchMenuTools(layerName, checked);
                        } else {
                            switchMenuTools(layerName, !checked);
                        }
                    });
                });
            }

        </script>
    </head>
    <body onload="init();">
        <div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
        <noscript>
            <p id="noJavascript"><?=$t->tr("noJavascript")?></p>
        </noscript>
        <div style="position:absolute; bottom:48px; left:5px; cursor:pointer;">
            <a id="license_osm"  onClick="showMapKey('license')"><img alt="OSM-Logo" src="resources/icons/OSM-Logo-32px.png" height="32px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_ccbysa" onClick="showMapKey('license')"><img alt="CC by SA" src="resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_bing" href="https://wiki.openseamap.org/wiki/Bing" target="_blank" style="display:none"><img alt="bing" src="resources/icons/bing.png" height="29px"></a>
            <a id="license_marine_traffic" onClick="showMapKey('license')" style="display:none"><img alt="Marine Traffic" src="resources/icons/MarineTrafficLogo.png" height="30px"></a>
            <a id="license_waterdepth" onClick="showMapKey('license')" style="display:none"><img alt="Water Depth" src="resources/icons/depth.jpg" height="32px"></a>
        </div>
        <div id="actionDialog">
            <br>&nbsp;not found&nbsp;<br>&nbsp;
        </div>
        <div class="unselectable" draggable="false" unselectable="on" id="compassRose">
            <img id="geoCompassRose" draggable="false" unselectable="on" src="./resources/map/nautical_compass_rose_geo_north.svg"/>
            <img id="magCompassRose" draggable="false" unselectable="on" src="./resources/map/nautical_compass_rose_mag_north.svg"/>
            <div id="magCompassText">
                <p id="magCompassTextTop">VAR 3.5°5'E (2015)</p>
                <p id="magCompassTextBottom">ANNUAL DECREASE 8'</p>
            </div>
        </div>
        <div id="popup" class="ol-popup">
            <a href="#" id="popup-closer" class="ol-popup-closer"></a>
            <div id="popup-content"></div>
        </div>
        <?php include('classes/topmenu.inc'); ?>
        <?php include('classes/footer.inc'); ?>
    </body>
</html>
