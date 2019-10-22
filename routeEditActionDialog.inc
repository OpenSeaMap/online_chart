
<div id="actionDialogMenu" class="menu">
    <ul>
        <li><span><?=$t->tr("file")?></span>
            <ul>
                <li><a onclick="toggleOpenFileDialog();"><?php $t->tr("open")?>...</a></li>
                <li><a onclick="toggleSaveFileDialog();"><?php $t->tr("savefiledialog")?>...</a></li>
                <li class="separator"></li>
                <li onclick="togglePrefDialog();"><?php $t->tr("preferences")?>...</li>
                <li class="separator"></li>
                <li><a onClick="if (!routeChanged || confirm('<?=$t->tr("confirmCloseRoute")?>')) {closeNauticalRoute();}">Close</a></li>
            </ul>
        </li>
        <li><span><?=$t->tr("edit")?></span>
            <ul>
                <li><a onclick="if (!routeChanged || confirm('<?=$t->tr("confirmDeleteRoute")?>')) {closeNauticalRoute();addNauticalRoute();}"/><?=$t->tr("deleteRoute")?></a></li>
            </ul>
        </li>
        <li><span><a href="#help"><?=$t->tr("help")?></a></span>
        </li>
    </ul>
</div>

<div class="modal" id="openfiledialog">
    <div class="modal-content">
        <span class="close-button" onclick="toggleOpenFileDialog();">×</span>
        <h1>Open File</h1>
        <input type="file" id="openfilename" accept="application/gpx+xml"/> Choose file<br>
        <input type="radio" id="useTrack"> use track information<br>
        <input type="radio" id="useRoute"> use route information<br>
        <button class="trigger" onclick="toggleOpenFileDialog();NauticalRoute_openTrack();">Load</button>
        <button class="trigger" onclick="toggleOpenFileDialog();">Cancel</button>
    </div>
</div>

<div class="modal" id="savefiledialog">
    <div class="modal-content">
        <span class="close-button" onclick="toggleSaveFileDialog();">×</span>
        <h1>Save File</h1>
        <?=$t->tr("format")?> <select id="routeFormat">
            <option value="GPX"/>GPX
            <option value="CSV"/>CSV
            <option value="GML">GML
            <option value="KML"/>KML
        </select>
        <button class="trigger" onclick="toggleSaveFileDialog();NauticalRoute_saveTrack();"><?=$t->tr("download")?></button>
        <button class="trigger" onclick="toggleSaveFileDialog();"><?=$t->tr("cancel")?></button>
    </div>
</div>

<div class="modal" id="preferences">
    <div class="modal-content">
        <span class="close-button" onclick="togglePrefDialog();">×</span>
        <h1><?=$t->tr("preferences")?></h1>
        <ul>
            <li><?=$t->tr("coordinateFormat")?> <input type="text" size="4em" id="coordFormat" onchange="NauticalRoute_getPoints(routeTrack);" value="N __°##.#'">
            </li>
            <li><?=$t->tr("units")?> <select id="distUnits" onchange="NauticalRoute_getPoints(routeTrack);">
                <option value="nm"/>[nm]
                <option value="ft"/>[ft]
                <option value="km"/>[km]
                <option value="m"/>[m]
                </select>
            </li>
        </ul>
    </div>
</div>

<table id="tripSummary">
    <thead>
        <tr><th><?=$t->tr("start")?></th><th><?=$t->tr("finish")?></th><th><?=$t->tr("distance")?></th></tr>
    </thead>
    <tbody>
        <tr><td id="routeStart">--</td><td id="routeEnd">--</td><td id="routeDistance">--</td></tr>
    </tbody>
</table>

<table id="segmentList">
    <thead>
        <tr>
            <th>#</th>
            <th><?=$t->tr("course")?></th>
            <th><?=$t->tr("distance")?></th>
            <th><?=$t->tr("name")?></th>
            <th></th>
        </tr>
    </thead>
    <tbody id="routePoints">
    </tbody>
</table>
