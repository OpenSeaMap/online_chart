<iframe id="josm_call_iframe" src="#" style="visibility:hidden;"></iframe>
<div id="topmenu2" style="position:absolute; top:10px; left:60px;">
    <ul class="dropdown">
        <li>
            <a href="http://openseamap.org/" target="blank">
                <img alt="OpenSeaMapLogo" src="./resources/icons/OpenSeaMapLogo_32.png" width="24" height="24" border="0">
            </a>
        </li>
        <li>
            <a>
                <img alt="search" src="./resources/action/find.png" width="22" height="22" border="0"  onClick="nominatim(document.getElementById('searchinputbox').value)">
                <input id="searchinputbox" name="searchtext" type="text"
                    size="10" maxlength="40"
                    style="height: 18px; border: 1px solid Black"
                    onKeyPress="if (event.keyCode==13 || event.which==13) {nominatim(this.value);}"
                >
            </a>
        </li>
        <li>
            <a><img alt="edit" src="./resources/action/edit.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("edit")?></a>
            <ul class="sub_menu">
                <li><a href="javascript:josm_call()"><?=$t->tr("editMapJOSM")?></a></li>
            </ul>
        </li>
        <li>
            <a><img alt="view" src="./resources/action/view.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("view")?></a>
            <ul class="sub_menu">
                <li><a href="weather.php"><img alt="weather" src="./resources/map/weather.png" width="24" height="24" border="0">&nbsp;<?=$t->tr("weather")?></a></li>
                <li onClick="showSeamarks()"><a><input type="checkbox" id="checkLayerSeamark">&nbsp;<?=$t->tr("Seezeichen")?></a></li>
                <li onClick="showHarbours()"><a><input type="checkbox" id="checkLayerHarbour">&nbsp;<?=$t->tr("harbours")?></a></li>
                <li onClick="showTidalScale()"><a><input type="checkbox" id="checkLayerTidalScale">&nbsp;<?=$t->tr("tidalScale")?></a></li>
                <li onClick="showSport()"><a><input type="checkbox" id="checkLayerSport">&nbsp;Sport</a></li>
                <li onClick="showBingAerial()"><a><input type="checkbox" id="checkLayerBingAerial">&nbsp;<?=$t->tr("bingaerial")?></a></li>
                <li onClick="showGridWGS()"><a><input type="checkbox" id="checkLayerGridWGS">&nbsp;<?=$t->tr("coordinateGrid")?></a></li>
                <li onClick="showElevationProfile()"><a><input type="checkbox" id="checkLayerElevationProfile">&nbsp;<?=$t->tr("elevationProfile")?></a></li>
                <li onClick="showGebcoDepth()"><a><input type="checkbox" id="checkLayerGebcoDepth">&nbsp;<?=$t->tr("gebcoDepth")?></a></li>
                <li onClick="showWikipediaLinks(false, false)">
                    <a><input type="checkbox" id="checkLayerWikipedia">&nbsp;Wikipedia-Links</a>
                    <ul class="sub_menu">
                        <li onClick="showWikipediaLinks(false, true)"><a><input type="checkbox" id="checkLayerWikipediaMarker">&nbsp;Marker</a></li>
                        <li onClick="showWikipediaLinks(true, true)"><a><input type="checkbox" id="checkLayerWikipediaThumbnails">&nbsp;Thumbnails</a></li>
                    </ul>
                </li>
                <li onClick="showAis()"><a><input type="checkbox" id="checkLayerAis">&nbsp;<?=$t->tr("ais")?></a></li>

                <li onClick="showWaterDepthTrackPoints(true)">
                  <a onClick="document.getElementById('checkLayerWaterDepthTrackPoints').checked = !document.getElementById('checkLayerWaterDepthTrackPoints').checked">
                    <input type="checkbox" id="checkLayerWaterDepthTrackPoints" />&nbsp;<?=$t->tr("water_depth")?></input>
                  </a>
                    <ul class="sub_menu">
                        <li onClick="layer_waterdepth_trackpoints_10m.setVisibility(true);layer_waterdepth_trackpoints_100m.setVisibility(false)">
                          <a><input type="radio" name="radioLayerWaterDepthTrackPoints" id="checkLayerWaterDepthTrackPoints10m" />&nbsp;10 m</a>
                        </li>
                            <li onClick="layer_waterdepth_trackpoints_10m.setVisibility(false);layer_waterdepth_trackpoints_100m.setVisibility(true)">
                              <a><input type="radio" name="radioLayerWaterDepthTrackPoints" id="checkLayerWaterDepthTrackPoints100m" />&nbsp;100 m</a>
                            </li>
                    </ul>
                </li>

                <li><a><img alt="dev" src="./resources/map/development_32px.png" width="24" height="24" border="0">&nbsp;<?=$t->tr("development")?></a>
                    <ul class="sub_menu">
                        <li><a href="http://testdepth.openseamap.org" target="_blank">&nbsp;<?=$t->tr("water_depth")?></a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li>
            <a><img alt="tools" src="./resources/action/tools.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("tools")?></a>
            <ul class="sub_menu">
                <li data-tools="download">
                    <a><input type="checkbox" id="checkDownload">&nbsp;<img alt="download" src="./resources/action/download.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("downloadChart")?></a>
                </li>
                <li data-tools="nautical_route">
                    <a><input type="checkbox" id="checkNauticalRoute">&nbsp;<img alt="route" src="./resources/action/route-32px.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("tripPlanner")?></a>
                </li>
				<li data-tools="permalink">
                    <a><input type="checkbox" id="checkPermalink">&nbsp;<img alt="permalink" src="./resources/action/permalink-32x.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("permalinks")?></a>
                </li>
            </ul>
        </li>
        <li>
            <a><img alt="help" src="./resources/action/help.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("help")?></a>
            <ul class="sub_menu">
                <li>
                    <a><img alt="help" src="./resources/action/help.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("help")?></a>
                    <ul class="sub_menu">
                        <li onClick="showMapKey('help-josm')">
                            <a><?=$t->tr("help-josm")?></a>
                        </li>
                        <li onClick="showMapKey('help-tidal-scale')">
                            <a><?=$t->tr("help-tidal-scale")?></a>
                        </li>
                        <li onClick="showMapKey('help-trip-planner')">
                            <a><?=$t->tr("tripPlanner")?></a>
                        </li>
                        <li onClick="showMapKey('help-website')">
                            <a><?=$t->tr("help-website-int")?></a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a><img alt="info" src="./resources/action/info.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("Legende")?></a>
                    <ul class="sub_menu">
                        <li onClick="showMapKey('harbour')">
                            <a><?=$t->tr("harbour")?></a>
                        </li>
                        <li onClick="showMapKey('seamark')">
                            <a><?=$t->tr("Seezeichen")?></a>
                        </li>
                        <li onClick="showMapKey('light')">
                            <a><?=$t->tr("Leuchtfeuer")?></a>
                        </li>
                        <li onClick="showMapKey('lock')">
                            <a><?=$t->tr("BrückenSchleusen")?></a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="http://forum.openseamap.org" target='blank'>
                        <img alt="forum" src="./resources/action/forum.png" width="22" height="22" border="0">&nbsp;Forum
                    </a>
                </li>
                <li onClick="showMapKey('license')">
                    <a><img alt="CC by SA" src="./resources/action/Cc-sa-32px.png" width="22" height="22" border="0">&nbsp;<?=$t->tr("license")?></a>
                </li>
            </ul>
        </li>
    </ul>
</div>
