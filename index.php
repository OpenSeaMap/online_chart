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
        <script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
        <script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
        <script type="text/javascript" src="./javascript/prototype.js"></script>
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
        <!-- Actual bing class from devel tree (can be removed on next OpenLayers release) -->
        <script type="text/javascript" src="./javascript/bing.js"></script>
        <script type="text/javascript" src="./javascript/ais.js"></script>
        <script type="text/javascript" src="./javascript/satpro.js"></script>
        <link rel="stylesheet" href="./classes/jquery-ui-1.10.3.custom.css" type="text/css">
        <script src="./classes/jquery-1.9.1.js"></script>
        <script src="./classes/jquery-ui-1.10.3.custom.min.js"></script>



        <script type="text/javascript">

            var map;
            var arrayMaps = new Array();

            // Position and zoomlevel of the map (will be overriden with permalink parameters or cookies)
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
            var layer_mapnik;          // 1
            var layer_marker;          // 2
            var layer_seamark;         // 3
            var layer_sport;           // 4
            var layer_gebco_deepshade; // 5
            var layer_gebco_deeps_gwc; // 6
            var layer_pois;            // 7
            var layer_download;        // 8
            var layer_nautical_route;  // 9
            var layer_grid;            // 10
            var layer_wikipedia;       // 11
            var layer_bing_aerial;     // 12
            var layer_ais;             // 13
            var layer_satpro;          // 14
            // layer_disaster          // 15
            var layer_tidalscale;      // 16
            var layer_permalink;       // 17
            var layer_water_depth;     // 18

            // Select controls
            var selectControl;

            // Controls
            var ZoomBar = new OpenLayers.Control.PanZoomBar();

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
                    layer_marker = new OpenLayers.Layer.Markers("Marker",{
                        layerId: 2
                    });
                    map.addLayer(layer_marker);
                    addMarker(layer_marker, mlon, mlat, convert2Text(getArgument("mtext")));
                }
                readLayerCookies();
                resetLayerCheckboxes();
                // Set current language for internationalization
                OpenLayers.Lang.setCode(language);
            }
          
            function readLayerCookies() {
                if (getArgument('layers') != -1) {
                    // There is a 'layers' url param -> ignore cookies
                    return;
                }
                // Set Layer visibility from cookie
                if (getCookie("SeamarkLayerVisible") == "false") {
                    layer_seamark.setVisibility(false);
                }
                if (getCookie("HarbourLayerVisible") == "false") {
                    layer_pois.setVisibility(false);
                }
                if (getCookie("TidalScaleLayerVisible") == "true") {
                    layer_tidalscale.setVisibility(true);
                    refreshTidalScales();
                }
                if (getCookie("SportLayerVisible") == "true") {
                    layer_sport.setVisibility(true);
                }
                if (getCookie("GridWGSLayerVisible") == "true") {
                    layer_grid.setVisibility(true);
                }
                if (getCookie("GebcoDepthLayerVisible") == "true") {
                    layer_gebco_deepshade.setVisibility(true);
                    layer_gebco_deeps_gwc.setVisibility(true);
                }
                if (getCookie("checkLayerWaterDepth") == "true") {
                    layer_water_depth.setVisibility(true);
                }
                if (getCookie("WikipediaLayerVisible") == "true") {
                    showWikipediaLinks(true, false);
                }
                if (getCookie("BingAerialLayerVisible") == "true") {
                    map.setBaseLayer(layer_bing_aerial);
                }
                if (getCookie("AisLayerVisible") == "true") {
                    showAis();
                }
            }

            function resetLayerCheckboxes()
            {
                // This method is separated from readLayerCookies because
                // the permalink control also will set the visibility of
                // layers.
                document.getElementById("checkLayerSeamark").checked              = (layer_seamark.getVisibility() === true);
                document.getElementById("checkLayerHarbour").checked              = (layer_pois.getVisibility() === true);
                document.getElementById("checkLayerTidalScale").checked           = (layer_tidalscale.getVisibility() === true);
                document.getElementById("checkLayerSport").checked                = (layer_sport.getVisibility() === true);
                document.getElementById("checkLayerGridWGS").checked              = (layer_grid.getVisibility() === true);
                document.getElementById("checkLayerGebcoDepth").checked           = (layer_gebco_deepshade.getVisibility() === true || layer_gebco_deeps_gwc.getVisibility() === true);
                document.getElementById("checkDownload").checked                  = (layer_download.getVisibility() === true);
                document.getElementById("checkNauticalRoute").checked             = (layer_nautical_route.getVisibility() === true);
                document.getElementById("checkLayerWikipedia").checked            = (layer_wikipedia.getVisibility() === true);
                document.getElementById("checkLayerWikipediaMarker").checked      = (layer_wikipedia.getVisibility() === true && wikipediaThumbs === false);
                document.getElementById("checkLayerWikipediaThumbnails").checked  = (layer_wikipedia.getVisibility() === true && wikipediaThumbs === true);
                document.getElementById("checkLayerBingAerial").checked           = (map.baseLayer == layer_bing_aerial);
                document.getElementById("checkLayerAis").checked                  = (layer_ais.getVisibility() === true);
                document.getElementById("checkPermalink").checked                 = (layer_permalink.getVisibility() === true);
                document.getElementById("checkLayerWaterDepth").checked           = (layer_water_depth.getVisibility() === true);
                document.getElementById("checkSlider").checked                    = false;
                //document.getElementById("checkLayerSatPro").checked               = (layer_satpro.getVisibility() === true);
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

            // Show route section
            function showNauticalRoute() {
                if (layer_nautical_route.visibility) {
                    closeNauticalRoute();
                } else {
                    addNauticalRoute();
                }
            }
            function showPermalink() {
                if (layer_permalink.visibility) {
                    closePermalink();
                } else {
                    addPermalink();
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

            function showWaterDepth() {
                if (layer_water_depth.visibility) {
                    layer_water_depth.setVisibility(false);
                    setCookie("WaterDepthLayerVisible", "false");
                } else {
                    layer_water_depth.setVisibility(true);
                    setCookie("WaterDepthLayerVisible", "true");
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

            // Show Download section
            function showMapDownload() {
                if (!downloadLoaded) {
                    addMapDownload();
                    selectControl.removePopup();
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
                    if (wikipediaThumbs === false && thumbs === true) {
                        wikipediaThumbs = true;
                        layer_wikipedia.setVisibility(false);
                    } else if (wikipediaThumbs === true && thumbs === false) {
                        wikipediaThumbs = false;
                        layer_wikipedia.setVisibility(false);
                    } else {
                        wikipediaThumbs = thumbs;
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
                    if (layer_wikipedia.getVisibility() === true) {
                        layer_wikipedia.setVisibility(false);
                        selectControl.removePopup();
                        setCookie("WikipediaLayerVisible", "false");
                    } else {
                        layer_wikipedia.setVisibility(true);
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
                downloadLoaded = true;
            }

            function closeMapDownload() {
                selectControl.hover = true;
                layer_download.setVisibility(false);
                layer_download.removeAllFeatures();
                closeActionDialog();
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
                var url = "http://sourceforge.net/projects/opennautical/files/Maps" + downloadLink + "ONC-" + downloadName + format + "/download";

                downloadWindow = window.open(url);
            }

            function selectedMap (event) {
                var feature = event.feature;
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
                showActionDialog(htmlText);
                NauticalRoute_startEditMode();
            }
            
            function boldText(textAreaId, link) { // taken from http://jsfiddle.net/HXnru/
                var browser=navigator.appName
                var b_version=navigator.appVersion

                if (browser=="Microsoft Internet Explorer" && b_version>='4')
                {
                var str = document.selection.createRange().text;
                document.getElementById(textAreaId).focus();
                var sel = document.selection.createRange();
                sel.text = "<b>" + str + "</b>";
                return;
                }

                field = document.getElementById(textAreaId);
                startPos = field.selectionStart;
                endPos = field.selectionEnd;
                before = field.value.substr(0, startPos);
                selected = field.value.substr(field.selectionStart, (field.selectionEnd - field.selectionStart));
                after = field.value.substr(field.selectionEnd, (field.value.length - field.selectionEnd));
                field.value = before + "<b>" + selected + "</b>" + after;
            }

            function addPermalink() {
                layer_permalink.setVisibility(true);
                var htmlText = "<div style=\"position:absolute; top:5px; right:5px; cursor:pointer;\">";
                htmlText += "<img src=\"./resources/action/close.gif\" onClick=\"closePermalink();\"/></div>";
                htmlText += "<h3><?=$t->tr("permalinks")?>:</h3><br/>"; // reference to translation.php
                htmlText += "<p><?=$t->tr("markset")?></p>" 
                htmlText += "<br /><hr /><br />"
                
                htmlText += "<table border=\"0\" width=\"370px\">";
                htmlText += "<tr><td><?=$t->tr("position")?>:</td><td id=\"markerpos\">- - -<br />- - -</td></tr>"; // Lat/Lon of the user's click
                htmlText += "<tr><td><?=$t->tr("description")?>:</td><td><textarea cols=\"25\" rows=\"5\" id=\"markerText\"></textarea><br /><input type=\"button\" value=\"Bold\" onclick=\"boldText('markerText')\" /></td></tr>"; // userInput
                htmlText += "<tr><td><?=$t->tr("actLayers")?>:</td><td id=\"actLayers\"></td></tr>"; //list of active layers
                htmlText += "</td></tr></table>";
                htmlText += "<br /><hr /><br />"
                htmlText += "<?=$t->tr("copynpaste")?>:<br /><textarea onclick=\"this.select();\" cols=\"50\" rows=\"2\" id=\"userURL\"></textarea>"; // secure & convient onlick-solution for copy and paste
            
                // Marker Init
                var lonlat = [0.0,0.0]; // Init of lon/lat 
                var size = new OpenLayers.Size(32,32); // size of the marker
                var offset = new OpenLayers.Pixel(-(size.w/2), -size.h); // offset to get the pinpoint of the needle to mouse pos
                var icon = new OpenLayers.Icon('http://map.openseamap.org/resources/icons/Needle_Red_32.png', size, offset); // Init of icon  

                //### Onclick behaviour
                map.events.register("click", layer_permalink, function(e) {
                
                // Adding of Marker 
                layer_permalink.clearMarkers(); // clear all markers to only keep one marker at a time on the map
                var position = this.events.getMousePosition(e); // get position of mouse click
                var lonlat = map.getLonLatFromPixel(position); // get Lon/Lat from position
                var lonlatTransf = lonlat.transform(map.getProjectionObject(), proj4326); // transform Lon/Lat into suitable projection
                var lonlat = lonlatTransf.transform(proj4326, map.getProjectionObject()); 
                layer_permalink.addMarker(new OpenLayers.Marker(lonlat, icon)); // add maker

                // Display Marker Position
                lonlat.transform(projMerc, proj4326);
                    // code from mousepostion_dm.js - redundant, try to reuse
                var ns = lonlat.lat >= 0 ? 'N' : 'S';
                var we = lonlat.lon >= 0 ? 'E' : 'W';
                var lon_m = Math.abs(lonlat.lon*60).toFixed(3);
                var lat_m = Math.abs(lonlat.lat*60).toFixed(3);
                var lon_d = Math.floor (lon_m/60);
                var lat_d = Math.floor (lat_m/60);
                lon_m -= lon_d*60;
                lat_m -= lat_d*60;
                


                // Layers - used for display in dialog and creation of permalink 
                // Display in dialog:
                var layersQuery = [];
                for(var i = 0; i < map.layers.length; i++) {
                    if (map.layers[i].layerId === void(0)) { 
                        continue; // disregard layer if it has no ID
                    }
                    else if (map.layers[i].name === "Marker") {
                        continue; // disregard Marker-Layer since it is only created when a permalink-url is called; user who open the map without any parameters will not invoke the creation the markers-layer
                    }
                    else {
                        layersQuery[i] = [map.layers[i].layerId, map.layers[i].name, map.layers[i].visibility]; // create array with layerId, layer name and layer visibility (boolean)
                    };
                };
                layersQuery.splice(1,0, ["2", "Marker Layer Placeholder", true]); // order of the layers is important for permalink, marker-layer is always disregarded (see above); placeholder with ID "2" will take it's place
                layersQuery = layersQuery.sort(function(a,b) { // sort layers by ID
                    return a[0] > b[0]; 
                });
                var layerList = ""; // variable for display in dialog
                for (var i = 0; i < layersQuery.length; i++) {
                    if (layersQuery[i][2] === true && layersQuery[i][1] != "Marker Layer Placeholder" && layersQuery[i][1] != "Permalink") {
                        layerList += layersQuery[i][1] + ", "; // get all visible layers which are not a placeholder or permalink (both not relevant for user)
                    };
                };                
                layerList = layerList.substring(0, layerList.length - 2); // cut the space and comma at the end of the string
                OpenLayers.Util.getElement("actLayers").innerHTML = layerList; // check if "actLayers" exists, if so write contents of layerList inside
               
                // Create Permalink for Layers
                var layersPermalink = "";
                for (var i = 0; i < layersQuery.length; i++) {
                    if (layersQuery[i][2] === false && layersQuery[i][1] === "Mapnik") {
                        layersPermalink += "0"; // if mapnik isn't visible, write 0 (won't show)
                    }
                    else if (layersQuery[i][2] === true && layersQuery[i][1] === "Mapnik") {
                        layersPermalink += "B"; // if mapnik is visible, write B (for baselayer)
                    }
                    else if (layersQuery[i][2] === false && layersQuery[i][1] === "Aerial photo") {
                         layersPermalink += "0"; // if aerial photo isn't visible, write 0
                    }
                    else if (layersQuery[i][2] === true && layersQuery[i][1] === "Aerial photo") {
                        layersPermalink += "B"; // if aerial photo is visible, write B
                    }
                    else if (layersQuery[i][2] === false) {
                        layersPermalink += "F"; // if layer isn't visible, write F (false, won't show)
                    }
                    else {
                        layersPermalink += "T"; // if layer is visible, write T (true, will show)
                    }

                };
                
                // Permalink coordinates
                var permPosition = this.events.getMousePosition(e); // get the mouse position from click-event
                var permLonLat = map.getLonLatFromPixel(permPosition); // get LonLat from pixel coordinates
                permLonLat.transform(projMerc, proj4326); // get the lonlat coordinates to the correct projection
                permLonLat.lat = Math.round(permLonLat.lat * 1000000) / 1000000;
                permLonLat.lon = Math.round(permLonLat.lon * 1000000) / 1000000;

                // Generate Permalink for copy and paste
                var userURL = "http://map.openseamap.org/map/"; // prefix
                userURL += "?zoom=" + map.getZoom(); // add map zoom to string
                userURL += "&mlat=" + permLonLat.lat; // add latitude
                userURL += "&mlon=" + permLonLat.lon; // add longitude
                userURLtemp = document.getElementById("markerText").value;
                userURLtemp = userURLtemp.replace(/\r?\n/g, '<br />');
                userURL += "&mtext=" + userURLtemp // add marker text; if empty OSM-permalink JS will ignore the '&mtext'
                userURL += "&layers=" + layersPermalink; // add encoded layers
                OpenLayers.Util.getElement("userURL").innerHTML = userURL; // write contents of userURL to textarea
        

                // check if "markerpos" exists; if so write the specified content inside
                OpenLayers.Util.getElement("markerpos").innerHTML = ns + lat_d + "°" + format2FixedLenght(lat_m,6,3) + "'" + " " + we + lon_d + "°" + format2FixedLenght(lon_m,6,3) + "'" + "<br />" + permLonLat.lat + ", " + permLonLat.lon;
                });          

                showActionDialog(htmlText);
            }

            function showAerialSlider() {
                if ($('#slider').is(':visible') === true) { // check if slider is activated
                    closeAerialSlider();
                    document.getElementById("checkLayerBingAerial").checked = false; // make sure the checkboxes are right
                    document.getElementById("checkSlider").checked = false;
                    layer_bing_aerial.setVisibility(false); // hide aerial when slider is closed
                } else {
                    showBingAerial();
                    addAerialSlider();
                    document.getElementById("checkLayerBingAerial").checked = true; // make sure the checkboxes are right
                    document.getElementById("checkSlider").checked = true;
                };
            }

            function closeAerialSlider() {
                $('#slider').hide();
            }

            function addAerialSlider() {

                layer_bing_aerial.setOpacity(1); // make
                layer_bing_aerial.setVisibility(true);
                $('#slider').show();
                
                $(function() {

                    var slider  = $('#slider');

                    //Call the Slider
                    slider.slider({
                    //Config
                        range: "min", //init values for the slider; goes from 0 to 1 with 0.01 as minimal distance between two values
                        min: 0,
                        value: 1,
                        step: 0.01,
                        max: 1,

                    start: function(event,ui) { // when the knob is touched
                        layer_mapnik.setVisibility(true); // would be invisible when aerial shows up by default; make it visible
                },

                    //Slider Event
                    slide: function(event, ui) { //When the slider is sliding

                        var value  = slider.slider('value'); // get value from slider
                        layer_bing_aerial.setOpacity(value); // write value to opacity

                        },

                        stop: function(event,ui) { 
                            document.getElementById("checkSlider").checked = true; // make checkbox is right
                        },
                    });

                });

            }
            
            function closeNauticalRoute() {
                layer_nautical_route.setVisibility(false);
                closeActionDialog();
                NauticalRoute_stopEditMode();
            }
            
            function closePermalink() {
                layer_permalink.setVisibility(false); 
                closeActionDialog();
            }

            function addSearchResults(xmlHttp) {
                // Marker at search results
                map.addLayer(layer_permalink); // use of existing layer; when addPermalink() is called, all search related markers will be cleared
                layer_permalink.setVisibility(true); // set layer visible
                var size = new OpenLayers.Size(21,25); // marker init values
                var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
                var icon = new OpenLayers.Icon('http://www.openlayers.org/dev/img/marker.png', size, offset);
                

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
                        var markLonLat = new OpenLayers.LonLat(placeLon,placeLat); // construct new LonLat object
                        markLonLat.transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913")); // coordinate transformation
                        layer_permalink.addMarker(new OpenLayers.Marker(markLonLat,icon.clone())); // add marker to layer
                }
                htmlText += "<tr><td>&nbsp;</td><td align=\"right\"><br/><input type=\"button\" id=\"buttonMapClose\" value=\"<?=$t->tr("clearMarker")?>\" onclick=\"layer_permalink.clearMarkers();\">&nbsp;<input type=\"button\" id=\"buttonMapClose\" value=\"<?=$t->tr("close")?>\" onclick=\"closeActionDialog();\"></td></tr></table>";
                showActionDialog(htmlText);
            }

            function drawmap() {
                map = new OpenLayers.Map('map', {
                    projection: projMerc,
                    displayProjection: proj4326,
                    eventListeners: {
                        moveend: mapEventMove,
                        zoomend: mapEventZoom,
                        click: mapEventClick,
                        changelayer: mapChangeLayer
                    },

                    controls: [
                        new OpenSeaMap.Control.Permalink(),
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
                layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik", {
                    layerId: 1
                });
                // Seamark
                layer_seamark = new OpenLayers.Layer.TMS("seamarks", "http://t1.openseamap.org/seamark/",
                    { layerId: 3, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, displayOutsideMaxExtent:true});
                // Sport
                layer_sport = new OpenLayers.Layer.TMS("Sport", "http://tiles.openseamap.org/sport/",
                    { layerId: 4, numZoomLevels: 19, type: 'png', getURL:getTileURL, isBaseLayer:false, visibility: false, displayOutsideMaxExtent:true});
                //GebcoDepth
                layer_gebco_deepshade = new OpenLayers.Layer.WMS("deepshade", "http:///osm.franken.de:8080/geoserver/wms",
                    {layers: "gebco:deepshade", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png', transparent: true},
                    { layerId: 5, isBaseLayer: false, visibility: false, opacity: 0.2, minResolution: 38.22});
                layer_gebco_deeps_gwc = new OpenLayers.Layer.WMS("deeps_gwc", "http://osm.franken.de:8080/geoserver/gwc/service/wms",
                    {layers: "gebco_new", format:"image/jpeg"},
                    { layerId: 6, isBaseLayer: false, visibility: false, opacity: 0.4});
                // POI-Layer for harbours and tidal scales
                layer_pois = new OpenLayers.Layer.Vector("pois", {
                    layerId: 7,
                    visibility: true,
                    projection: proj4326,
                    displayOutsideMaxExtent:true
                });
                // Bing
                layer_bing_aerial = new OpenLayers.Layer.Bing({
                    layerId: 12,
                    name: 'Aerial photo',
                    key: 'AuA1b41REXrEohfokJjbHgCSp1EmwTcW8PEx_miJUvZERC0kbRnpotPTzGsPjGqa',
                    type: 'Aerial',
                    isBaseLayer: true,
                    displayOutsideMaxExtent: true
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
                // POI-Layer for harbours and tidal scales
                layer_tidalscale = new OpenLayers.Layer.Vector("tidalscale", {
                    layerId: 16,
                    visibility: false,
                    projection: proj4326,
                    displayOutsideMaxExtent:true
                });
                // Permalink Layer (17)
                layer_permalink = new OpenLayers.Layer.Markers("Permalink", { 
                    layerId: 17,
                    visibility: false,
                    projection: proj4326
                });
                // Water Depth Beta (18)
                layer_water_depth = new OpenLayers.Layer.WMS("Water Depth Track Points", "http:///osm.franken.de/cgi-bin/mapserv.fcgi?",
                    {layers: "trackpoints_cor1_test_dbs,trackpoints_cor1_test,test_zoom_10_cor_1_points,test_zoom_9_cor_1_points,test_zoom_8_cor_1_points,test_zoom_7_cor_1_points,test_zoom_6_cor_1_points,test_zoom_5_cor_1_points,test_zoom_4_cor_1_points,test_zoom_3_cor_1_points,test_zoom_2_cor_1_points", projection: new OpenLayers.Projection("EPSG:900913"), type: 'png',transparent:!0},
                    {layerId: 18, isBaseLayer: false, visibility: false, tileSize:new OpenLayers.Size(1024,1024)});

                
                map.addLayers([
                                    layer_mapnik,
                                    layer_bing_aerial,
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
                                    layer_water_depth
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
                harbours.clear();
                layer_pois.removeAllFeatures();
            }

            function clearTidalScaleLayer() {
                arrayTidalScales.clear();
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
                layer_mapnik.redraw();
                layer_bing_aerial.redraw();
                // Clear POI layer
                clearPoiLayer();
                clearTidalScaleLayer();
                if(oldZoom!=zoom) {
                    oldZoom=zoom;
                }
                if (downloadLoaded) {
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
    <body onload="init();">
        <div id="map" style="position:absolute; bottom:0px; left:0px;"></div>
        <div style="position:absolute; bottom:48px; left:12px; cursor:pointer;">
            <a id="license_osm"  onClick="showMapKey('license')"><img alt="OSM-Logo" src="resources/icons/OSM-Logo-32px.png" height="32px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_ccbysa" onClick="showMapKey('license')"><img alt="CC by SA" src="resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>"></a>
            <a id="license_bing" href="http://wiki.openseamap.org/wiki/Bing" target="_blank" style="display:none"><img alt="bing" src="resources/icons/bing.png" height="29px"></a>
            <a id="license_marine_traffic" onClick="showMapKey('license')" style="display:none"><img alt="Marine Traffic" src="resources/icons/MarineTrafficLogo.png" height="30px"></a>
        </div>
        <div id="actionDialog">
            <br>&nbsp;not found&nbsp;<br>&nbsp;
        </div>
        <?php include('classes/topmenu.inc'); ?>


        <div id="noez" style="position:absolute; top:25px; left:50%; width: 200px;">
        
            <div id="slider" style="display:none;"></div>
        </div>        
        
    </body>
</html>
