<?php
/*
	loaded by index.php and map_edit:
	<link rel="stylesheet" href="topmenu.css" type="text/css" media="screen, projection"/>
	<script type="text/javascript" language="javascript" src="javascript/utilities.js"></script>
	<script type="text/javascript" language="javascript" src="javascript/jquery-1.5.2.min.js"></script>
	<script type="text/javascript" language="javascript" src="javascript/jquery.dropdownPlain.js"></script>
	<script type="text/javascript" language="javascript" src="javascript/nominatim.js"></script>
*/
?>

<!-- ICONS -------------
./resources/action/edit.png
./resources/map/weather.png
http://www.qatareshop.com/images/missing.png
http://upload.wikimedia.org/wikipedia/commons/b/b3/Advancedsettings.png
http://upload.wikimedia.org/wikipedia/commons/0/00/Crystal_Project_package_graphics.png
http://upload.wikimedia.org/wikipedia/commons/2/23/Crystal_Project_starthere.png
http://upload.wikimedia.org/wikipedia/commons/d/da/Crystal_Project_kweather.png
http://upload.wikimedia.org/wikipedia/commons/c/c9/Crystal_Project_find.png
http://upload.wikimedia.org/wikipedia/commons/c/cc/Crystal_Project_viewmag.png
http://commons.wikimedia.org/wiki/Category:Crystal_Project
-->
	<iframe id="josm_call_iframe" src="#" style="visibility:hidden;"></iframe>
	<div id="topmenu2" style="position:absolute; top:10px; left:80px;">
        <ul class="dropdown">
			<li><a href="http://<?= $_SERVER["HTTP_HOST"] ?>"><img src="../resources/icons/OpenSeaMapLogo_88.png" width="24" height="24" align="center" border="0" /></a></li>
			<li><a href="#">
				<img src="//upload.wikimedia.org/wikipedia/commons/c/cc/Crystal_Project_viewmag.png" width="24" height="24" align="center" border="0" />
				<input id="searchinputbox" name="searchtext" type="text"
				    size="10" maxlength="40"
				    style="height: 20px; border: 1px solid Black"
				    onKeyPress="if (event.keyCode==13 || event.which==13) { nominatim(this.value);}"
				>
				</a>

			</li>
			<li><a href="map_edit.php"><img src="http://upload.wikimedia.org/wikipedia/commons/2/23/Crystal_Project_starthere.png" width="24" height="24" align="center" border="0">&nbsp;<?=$t->tr("mapview")?></a>
        		<ul class="sub_menu">
        			 <li><a href="index.php"><?=$t->tr("Seekarte")?></a></li>
					 <li><a href="weather.php"><?=$t->tr("weather")?></a></li>
        			 <li><a href="map_edit.php">Online Editor</a></li>
        			 <li><a href="javascript:josm_call()">JOSM Remote</a>
						<ul>
							<li><a href="http://www.openseamap.org/index.php?id=josmdownload">JOSM Download</a></li>
							<li><a href="javascript:josm_call()">JOSM Remote Call</a></li>
							<li><a href="javascript:getJOSMVersion();">JOSM Version</a></li>
						</ul>
					</li>
				</ul>
			</li>
<!--
			<li><a href="weather.php">
				<img src="http://upload.wikimedia.org/wikipedia/commons/d/da/Crystal_Project_kweather.png" width="24" height="24" align="center" border="0">&nbsp;<?=$t->tr("weather")?>
				</a>
        		<ul class="sub_menu">
        			 <li><a href="#4.1"><input type="checkbox" id="checkWind"/>&nbsp;<img src="./resources/map/WindIcon.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("wind")?></a></li>
        			 <li><a href="#4.2"><input type="checkbox" id=""/>&nbsp;<img src="http://www.qatareshop.com/images/missing.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("tide")?></a></li>
        			 <li><a href="#4.3"><input type="checkbox" id=""/>&nbsp;<img src="./resources/map/AirPressureIcon.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("AirPressure")?></a></li>
        			 <li><a href="#4.4"><input type="checkbox" id=""/>&nbsp;<img src="./resources/map/WaveIcon.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("WaveHeight")?></a></li>
        			 <li><a href="#4.5"><input type="checkbox" id=""/>&nbsp;<img src="./resources/map/AirTemperatureIcon.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("AirTemperature")?></a></li>
        			 <li><a href="#4.6"><input type="checkbox" id=""/>&nbsp;<img src="http://www.qatareshop.com/images/missing.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("humidity")?></a></li>
        			 <li><a href="#4.7"><input type="checkbox" id=""/>&nbsp;<img src="http://www.qatareshop.com/images/missing.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("clouds")?></a></li>
        			 <li><a href="#4.8"><input type="checkbox" id=""/>&nbsp;<img src="./resources/map/PrecipitationIcon.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("precipitation")?></a></li>					 
				</ul>
			</li>
			<li><a href="#"><img src="http://upload.wikimedia.org/wikipedia/commons/2/23/Crystal_Project_starthere.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("mapview")?></a>
        		<ul class="sub_menu">
        			 <li><a href="#">5.1 <?=$t->tr("depth")?></a></li>
        			 <li><a href="#">5.2 Häfen</a></li>
        			 <li><a href="#">5.3 Taucher</a></li>
        			 <li><a href="#">5.4 Sportlayer (Regatta)</a></li>
        			 <li><a href="#">5.5 Kajakfahrer</a></li>
        			 <li><a href="#">5.6 Binnenschiffer</a></li>
        			 <li><a href="#">5.7 AIS</a></li>					 
				</ul>
			</li>
/-->
			<li><a href="#"><img src="http://upload.wikimedia.org/wikipedia/commons/b/b3/Advancedsettings.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("Werkzeuge")?></a>
				<ul class="sub_menu">
<!--
					<li>
						<a href="#6.1">Messen</a>
						<ul>
							<li><a href="#">Kurs</a></li>
							<li><a href="#">Distanz</a></li>
							<li><a href="#">Route</a></li>
						</ul>
					</li>
        			<li><a href="#6.2">Expertensuche</a></li>
					<li>
						<a href="#6.3">Navigation</a>
						<ul>
							<li><a href="#">GPS</a></li>
							<li><a href="#">Schiffsstandort</a></li>
							<li><a href="#">Log</a></li>
							<li><a href="#">Goto</a></li>
							<li><a href="#">Routen</a></li>
							<li><a href="#">Autopilot</a></li>							
						</ul>
        			</li>
/-->
					<li onClick="showMapDownload()"><a href="#6.4"><img src="./resources/action/download.png" width="24" height="24" align="center" border="0" />&nbsp;<?=$t->tr("downloadChart")?></a></li>
					<li onClick="showMapKey()"><a href="#6.5"><img src="./resources/action/info.png" width="24" height="24" align="center" border="0" /> Legende/key</a></li>
					<li>
						<a href="#6.6"><?=$t->tr("help")?></a>
						<ul>
							<li><a href="http://wiki.openseamap.org/index.php?title=<?=$t->getCurrentLanguage()?>:Online-Editor"><?=$t->getCurrentLanguage()?>:Online-Editor</a></li>
							<li><a href="http://wiki.openseamap.org/index.php?title=De:Online-Editor">De:Online-Editor</a></li>
						</ul>
        			</li>
<!--
					<li>
						<a href="#6.X">Optionen</a>
						<ul>
							<li><a href="#">Sprache</a></li>
							<li><a href="#">Koordinatenformat</a></li>
							<li><a href="#">Koordinatengitter</a></li>
						</ul>
        			</li>
/-->
				</ul>
			</li>			
        </ul>
		
	</div>

