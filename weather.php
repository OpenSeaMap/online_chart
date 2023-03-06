<?php
    include("classes/Translation.php");
    include("classes/weather.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>OpenSeaMap - <?php echo $t->tr("weather")?></title>
        <meta name="AUTHOR" content="Olaf Hannemann" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <meta http-equiv="content-language" content="<?= $t->getCurrentLanguage() ?>"/> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <link rel="SHORTCUT ICON" href="resources/icons/OpenSeaMapLogo_16.png"/>
        <link rel="stylesheet" type="text/css" href="weather.css">
        <link rel="stylesheet" href="./javascript/ol@v7.3.0/ol.css">
        <script src="./javascript/ol@v7.3.0/ol.js"></script>
        <script type="text/javascript" src="./javascript/utilities.js"></script>
        <script type="text/javascript" src="./javascript/map_utils.js"></script>
        <script type="text/javascript" src="./javascript/permalink.js"></script>
        <script type="text/javascript">

            // The map
            var map;
            var layers_weather_wind;
            var layers_weather_pressure;
            var layers_weather_air_temperature;
            var layers_weather_precipitation;
            var layers_weather_significant_wave_height;


            // Selected time layer
            var layerNumber = 0;
            var numbers = ['5', '7', '9' , '11', '15', '19', '23', '27'];

            // Layer visibility
            var showWindLayer = false;
            var showPressureLayer = false;
            var showAirTemperatureLayer = false;
            var showPrecipitationLayer = false;
            var showSignificantWaveHeightLayer = false;

            // Position and zoomlevel of the map  (will be overriden with permalink parameters or cookies)
            var lon = 11.6540;
            var lat = 54.1530;
            var zoom = 6;

            // Load page for the first time
            function init() {
                buildTimeMenu();
                initMap();
                readPermalinkOrCookies();
                showWind();
                document.getElementById("timeLayer0").className = "selected";
                document.getElementById("checkPressure").checked = false;
                document.getElementById("checkAirTemperature").checked = false;
                document.getElementById("checkPrecipitation").checked = false;
                document.getElementById("checkSignificantWaveHeight").checked = false;
            }

            // Apply the url parameters or cookies or default values
            function readPermalinkOrCookies() {

                // Read zoom, lat, lon
                var cookieZoom = parseInt(getCookie("weather_zoom"), 10);
                var cookieLat = parseFloat(getCookie("weather_lat"));
                var cookieLon = parseFloat(getCookie("weather_lon"));
                var permalinkLat = parseFloat(getArgument("lat"));
                var permalinkLon = parseFloat(getArgument("lon"));
                var permalinkZoom = parseInt(getArgument("zoom"), 10);

                zoom = permalinkZoom || cookieZoom || zoom;
                lat = permalinkLat || cookieLat || lat;
                lon = permalinkLon || cookieLon || lon;

                // Zoom to coordinates from marker/permalink/cookie or default values. 
                jumpTo(lon, lat, zoom);

                // Apply layers visiblity from permalink
                const permalinkLayers = getArgument("layers");
                if (permalinkLayers) {
                    const layers = map.getLayers().getArray();
                    [...permalinkLayers].forEach((visibility, index) => {
                        const layer = layers.find((l) => {
                        return l.get('layerId') === index + 1;
                        });
                        if (layer) {
                           layer.setVisible(/^(B|T)$/.test(visibility));
                        }
                    });
                } else {
                    map.getLayers().forEach((layer)=> {
                        const cookieKey = layer.get('cookieKey');
                        const cookieValue =  getCookie(cookieKey);
                        if (cookieKey && cookieValue) {
                        layer.setVisible((cookieValue === 'true'));
                        }
                    });
                }
            }

            var layerId = 1;
            function createWeatherXYZLayer(type, number) {
                return new ol.layer.Tile({
                    visible: false,
                    source: new ol.source.XYZ({
                        tileUrlFunction: function(coordinate) {
                            return getTileUrlFunction(`http://weather.openportguide.de/tiles/actual/${type}/${number}/`, 'png', coordinate);

                        }
                    }),
                    properties: {
                        layerId: layerId++,
                    }
                });
            }

            function initMap() {
                map = new ol.Map({
                    target: 'map',
                    view: new ol.View({
                        minZoom: 3,
                        maxZoom: 7,
                    }),
                    controls: [
                        new ol.control.Zoom(),
                        new ol.control.Attribution(),   
                        new Permalink(),
                    ]
                });

                map.on('moveend', mapEventMove);

                // Mapnik
                var layer_mapnik = new ol.layer.Tile({
                    source: new ol.source.OSM(),
                    properties: {
                        layerId: layerId++,
                    }
                });
                
                // Wind layers
                layers_weather_wind = numbers.map((number)=> {
                    return createWeatherXYZLayer('wind_stream', number);
                });
                

                // Air pressure layers
                layers_weather_pressure = numbers.map((number)=> {
                    return createWeatherXYZLayer('surface_pressure', number);
                });

                // Temperature layers
                layers_weather_air_temperature = numbers.map((number)=> {
                    return createWeatherXYZLayer('air_temperature', number);
                });

                // Precipitation layers
                layers_weather_precipitation = numbers.map((number)=> {
                    return createWeatherXYZLayer('precipitation', number);
                });

                // Wave height layers
                layers_weather_significant_wave_height = numbers.map((number)=> {
                    return createWeatherXYZLayer('significant_wave_height', number);
                });
                
                [
                    layer_mapnik,
                    ...layers_weather_wind, 
                    ...layers_weather_pressure,
                    ...layers_weather_air_temperature,
                    ...layers_weather_precipitation,
                    ...layers_weather_significant_wave_height,
                ].forEach((l) => map.addLayer(l));
            }

            // Map event listener moved
            function mapEventMove(event) {
                zoom = map.getView().getZoom();
                const lonLat = ol.proj.toLonLat(map.getView().getCenter());
;                // Set cookie for remembering lat lon values
                setCookie("weather_lat", lonLat[1].toFixed(5));
                setCookie("weather_lon", lonLat[0].toFixed(5));
                setCookie("weather_zoom",zoom);
            }

            function showWind() {
                if (!showWindLayer) {
                    document.getElementById("checkWind").checked = true;
                    document.getElementById("comment").style.visibility = "visible";
                    document.getElementById("buttonWind").className = "selected";
                    setWindLayerVisible();
                    showWindLayer = true;
                } else {
                    document.getElementById("checkWind").checked = false;
                    document.getElementById("comment").style.visibility = "hidden";
                    document.getElementById("buttonWind").className = "";
                    clearWindLayerVisibility();
                    showWindLayer = false;
                }
            }

            function showPressure() {
                if (!showPressureLayer) {
                    document.getElementById("checkPressure").checked = true;
                    document.getElementById("buttonPressure").className = "selected";
                    setWindLayerVisible();
                    setPressureLayerVisible();
                    showPressureLayer = true;
                } else {
                    document.getElementById("checkPressure").checked = false;
                    document.getElementById("buttonPressure").className = "";
                    clearPressureLayerVisibility();
                    showPressureLayer = false;
                }
            }

            function showAirTemperature() {
                if (!showAirTemperatureLayer) {
                    document.getElementById("checkAirTemperature").checked = true;
                    document.getElementById("buttonAirTemperature").className = "selected";
                    setAirTemperatureLayerVisible();
                    showAirTemperatureLayer = true;
                } else {
                    document.getElementById("checkAirTemperature").checked = false;
                    document.getElementById("buttonAirTemperature").className = "";
                    clearAirTemperatureLayerVisibility();
                    showAirTemperatureLayer = false;
                }
            }

            function showPrecipitation() {
                if (!showPrecipitationLayer) {
                    document.getElementById("checkPrecipitation").checked = true;
                    document.getElementById("buttonPrecipitation").className = "selected";
                    setPrecipitationLayerVisible();
                    showPrecipitationLayer = true;
                } else {
                    document.getElementById("checkPrecipitation").checked = false;
                    document.getElementById("buttonPrecipitation").className = "";
                    clearPrecipitationLayerVisibility();
                    showPrecipitationLayer = false;
                }
            }

            function showSignificantWaveHeight() {
                if (!showSignificantWaveHeightLayer) {
                    document.getElementById("checkSignificantWaveHeight").checked = true;
                    document.getElementById("buttonSignificantWaveHeight").className = "selected";
                    setSignificantWaveHeightLayerVisible();
                    showSignificantWaveHeightLayer = true;
                } else {
                    document.getElementById("checkSignificantWaveHeight").checked = false;
                    document.getElementById("buttonSignificantWaveHeight").className = "";
                    clearSignificantWaveHeightLayerVisibility();
                    showSignificantWaveHeightLayer = false;
                }
            }

            // Read time files from server and create the menu
            function buildTimeMenu() {
                var arrayTimeValues = [
                    "<?=$utc->getWeatherUtc('5')?>",
                    "<?=$utc->getWeatherUtc('7')?>",
                    "<?=$utc->getWeatherUtc('9')?>",
                    "<?=$utc->getWeatherUtc('11')?>",
                    "<?=$utc->getWeatherUtc('15')?>",
                    "<?=$utc->getWeatherUtc('19')?>",
                    "<?=$utc->getWeatherUtc('23')?>",
                    "<?=$utc->getWeatherUtc('27')?>",
                ]

                var oldDate = "00";
                var html = "<b><?=$t->tr("time")?> (UTC)</b><br/><br/>";

                for(i = 0; i < arrayTimeValues.length; i++) {
                    var values = arrayTimeValues[i].split(" ");
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
                    html += `
                    <li id="timeLayer${i}"
                        className="${layerNumber === i ? 'selected' : ''}"
                        onClick="setLayerVisible(${i})">${time}</li>
                    `;
                }
                html += "</ul>";
                document.getElementById('timemenu').innerHTML = html;
            }

            function setLayerVisible(number) {
                document.getElementById("timeLayer" + layerNumber).className = "";
                layerNumber = number;
                document.getElementById("timeLayer" + layerNumber).className = "selected";

                if (showWindLayer) {
                    setWindLayerVisible();
                }
                if (showPressureLayer) {
                    setPressureLayerVisible();
                }
                if (showAirTemperatureLayer) {
                    setAirTemperatureLayerVisible();
                }
                if (showPrecipitationLayer) {
                    setPrecipitationLayerVisible();
                }
                if (showSignificantWaveHeightLayer) {
                    setSignificantWaveHeightLayerVisible();
                }
            }

            function setWindLayerVisible() {
                clearWindLayerVisibility();
                layers_weather_wind[layerNumber].setVisible(true);
            }

            function setPressureLayerVisible() {
                clearPressureLayerVisibility();
                layers_weather_pressure[layerNumber].setVisible(true);
            }

            function setAirTemperatureLayerVisible() {
                clearAirTemperatureLayerVisibility();
                layers_weather_air_temperature[layerNumber].setVisible(true);
            }

            function setPrecipitationLayerVisible() {
                clearPrecipitationLayerVisibility();
                layers_weather_precipitation[layerNumber].setVisible(true);
            }

            function setSignificantWaveHeightLayerVisible() {
                clearSignificantWaveHeightLayerVisibility();
                layers_weather_significant_wave_height[layerNumber].setVisible(true);
            }

            function clearWindLayerVisibility() {
                layers_weather_wind.forEach((l) => l.setVisible(false));
            }

            function clearPressureLayerVisibility() {
                layers_weather_pressure.forEach((l) => l.setVisible(false));
            }

            function clearAirTemperatureLayerVisibility() {
                layers_weather_air_temperature.forEach((l) => l.setVisible(false));
            }

            function clearPrecipitationLayerVisibility() {
                layers_weather_precipitation.forEach((l) => l.setVisible(false));
            }

            function clearSignificantWaveHeightLayerVisibility() {
                layers_weather_significant_wave_height.forEach((l) => l.setVisible(false));
            }
        </script>
    </head>
    <body onload=init();>
        <div id="map"></div>
        <div id="copyright">
            <img src="resources/icons/somerights20.png" height="30px" title="<?=$t->tr("SomeRights")?>" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
            <img src="resources/icons/OpenPortGuideLogo_32.png" height="32px" title="<?=$t->tr("OpenPortGuide")?>" onClick="window.open('http://weather.openportguide.de/')" />
        </div>
        <div id="topmenu">
            <ul>
                <li onClick="window.location.href='./index.php?lang=<?=$t->getCurrentLanguage()?>'"><IMG src="resources/icons/OpenSeaMapLogo_88.png" width="24" height="24" align="center" border="0"><?=$t->tr("SeaChart")?></img></li>
                <li>|</li>
                <li id="buttonWind" onClick="showWind()">
                    <input type="checkbox" id="checkWind"/>
                    <img src="./resources/map/WindIcon.png"/>
                    <soan><?=$t->tr("wind")?></span>
                </li>
                <li id="buttonPressure" onClick="showPressure()">
                    <input type="checkbox" id="checkPressure"/>
                    <img src="./resources/map/AirPressureIcon.png"/>
                    <span><?=$t->tr("AirPressure")?></span>
                </li>
                <li id="buttonAirTemperature" onClick="showAirTemperature()">
                    <input type="checkbox" id="checkAirTemperature"/>
                    <img src="./resources/map/AirTemperatureIcon.png"/>
                    <span><?=$t->tr("AirTemperature")?></span>
                </li>
                <li id="buttonPrecipitation" onClick="showPrecipitation()">
                    <input type="checkbox" id="checkPrecipitation"/>
                    <img src="./resources/map/PrecipitationIcon.png"/>
                    <span><?=$t->tr("precipitation")?></span>
                </li>
                <li id="buttonSignificantWaveHeight" onClick="showSignificantWaveHeight()">
                    <input type="checkbox" id="checkSignificantWaveHeight"/>
                    <img src="./resources/map/WaveIcon.png"/>
                    <span><?=$t->tr("WaveHeight")?></span>
                </li>
            </ul>
        </div>
        <div id="timemenu">
            <h4>Time (UTC)</h4>
        </div>
        <div id="comment">
            <div>
                <img src="./resources/map/WindScale.png"/>
            </div>
        </div>
    </body>
</html>
