<?php
	include("../../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Seezeichen Bearbeiten</title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="de" />
		<link rel="stylesheet" type="text/css" href="../map-edit.css">
		<script type="text/javascript" src="../javascript/DataModel.js"></script>
		<script type="text/javascript">

			// Global Variables
			var _node;
			var _tags = new Array();
			var _seamark;
			var _buoy_shape;
			var _light_colour;
			var _category;
			var _version = 0;
			var _id = 0;
			var _mode;
			var _topmark_shape;

			// Images
			buoyImage = new Image();
			buoyImageTop = new Image();
			buoyImageLighted = new Image();
			buoyImageTopLighted = new Image();

			function init() {
				_mode = getArgument("mode");
				if (_mode == "create") {
					document.getElementById("headerAdd").style.visibility = "visible";
					database = new DataModel();
					_category = getArgument("type")
					_seamark = database.get("meta", _category);
					_tags[0] = "seamark:category," + _seamark;
					if (_category != "safe_water" && _category != "isolated_danger" && _category != "special_purpose") {
						_tags[1] = "seamark:" + _seamark + ":category," + _category;
					}
				} else {
					_id = getArgument("id");
					_version = getArgument("version");
					_node = opener.window.getKeys(_id);
					_tags = _node.split("|");
					var buff = getKey("seamark");
					if (buff == "-1") {
						_seamark = getKey("seamark:category");
					} else {
						_seamark = buff;
					}

					switch (_mode) {
						case "delete":
							document.getElementById("headerDelete").style.visibility = "visible";
							document.getElementById("buttonSave").value = "Löschen";
							document.getElementById("titleCategory").style.visibility = "hidden";
							document.getElementById("boxCategory").style.visibility = "hidden";
							document.getElementById("titleType").style.visibility = "hidden";
							document.getElementById("boxType").style.visibility = "hidden";
							document.getElementById("titleMisc").style.visibility = "hidden";
							document.getElementById("boxTopmark").style.visibility = "hidden";
							document.getElementById("boxRadar").style.visibility = "hidden";
							document.getElementById("boxLight").style.visibility = "hidden";
							document.getElementById("boxFogsignal").style.visibility = "hidden";
							break
						case "move":
							document.getElementById("headerMove").style.visibility = "visible";
							break
						case "update":
							document.getElementById("headerEdit").style.visibility = "visible";
							break
					}

					switch (_seamark) {
						case "buoy_safe_water":
							_category = "safe_water";
							break;
						case "buoy_special_purpose":
							_category = "special_purpose";
							break;
						case "buoy_isolated_danger":
							_category = "isolated_danger";
							break;
						case "buoy_lateral":
							_category = getKey("seamark:buoy_lateral:category");
							break;
						case "buoy_cardinal":
							_category = getKey("seamark:buoy_cardinal:category");
							break;
					}
				}
				
				_buoy_shape = getKey("seamark:" + _seamark + ":shape");

				document.getElementById("comboCategory").value = _category;
				document.getElementById("comboShape").value = _buoy_shape;

				if (getKey("seamark:topmark:shape") != "-1") {
					document.getElementById("checkTopmark").checked = true;
					if (_category == "special_purpose" && _mode != "delete") {
						showTopmarkColour(true);
					}
				}
				if (getKey("seamark:fog_signal") != "-1") {
					document.getElementById("checkFogsignal").checked = true;
				}
				if (getKey("seamark:radar_reflector") != "-1") {
					document.getElementById("checkRadar").checked = true;
				}
				if (getKey("seamark:light:colour") != "-1") {
					document.getElementById("checkLight").checked = true;
					if (_mode != "delete") {
						showLightEdit(true);
					}
				}
				var buff = getKey("seamark:name");
				if (buff != "-1") {
					document.getElementById("inputName").value = buff;
				}
				loadImages();
				onChangeCheck();
			}

			function loadImages() {
				switch (_category) {
					case "starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Green.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Green_Conical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Green_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Green_Conical_Lighted.png";
						break;
					case "port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Red.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Red_Cylindrical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Red_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Red_Cylindrical_Lighted.png";
						break;
					case "safe_water":
						_topmark_shape = "sphere";
						_light_colour = "white";
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_SafeWater.png";
						buoyImageTop.src = "../resources/lateral/Lateral_SafeWater_Sphere.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_SafeWater_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_SafeWater_Sphere_Lighted.png";
						break;
					case "preferred_channel_starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Pref_Starboard.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Pref_Starboard_Conical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Pref_Starboard_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Pref_Starboard_Conical_Lighted.png";
						break;
					case "preferred_channel_port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Pref_Port.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Pref_Port_Cylindrical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Pref_Port_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Pref_Port_Cylindrical_Lighted.png";
						break;
					case "north":
						_topmark_shape = "2_cones_up";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_North.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_North.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_North_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_North_Lighted.png";
						break;
					case "east":
						_topmark_shape = "2_cones_base_together";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_East.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_East.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_East_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_East_Lighted.png";
						break;
					case "south":
						_topmark_shape = "2_cones_down";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_South.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_South.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_South_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_South_Lighted.png";
						break;
					case "west":
						_topmark_shape = "2_cones_point_together";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_West.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_West.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_West_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_West_Lighted.png";
						break;
					case "isolated_danger":
						_topmark_shape = "2_spheres";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_Single.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_Single.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_Single_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_Single_Lighted.png";
						break;
					case "special_purpose":
						_topmark_shape = "x-shape";
						_light_colour = "white";
						var colour = getKey("seamark:topmark:colour")
						if (colour != "-1") {
							document.getElementById("topColour").value = colour;
						}
						document.getElementById("checkTopmark").disabled = false;
						buoyImage.src = "../resources/special_purpose/Special_Purpose.png";
						buoyImageTop.src = "../resources/special_purpose/Special_Purpose_x-Shape.png";
						buoyImageLighted.src = "../resources/special_purpose/Special_Purpose_Lighted.png";
						buoyImageTopLighted.src = "../resources/special_purpose/Special_Purpose_x-Shape_Lighted.png";
						break;
				}

				fillLightCombobox();
				displayLight();
			}

			function moveDivUp(id, offset) {
				var Y = parseInt(document.getElementById(id).style.top);
				Y = Y - offset
				document.getElementById(id).style.top = Y + "px";
			}

			function moveDivDown(id, offset) {
				var Y = parseInt(document.getElementById(id).style.top);
				Y = Y + offset
				document.getElementById(id).style.top = Y + "px";
			}

			function clearSelectOptions(selectionElement) {
				var selectionElement = document.getElementById("lightChar");
				while (selectionElement.options.length > 0) {
					selectionElement.options[0] = null;
				}
			}

			function addSelectOption(selectionElement, value) {
				var option = document.createElement("OPTION");
				var text = document.createTextNode(value);
				option.appendChild(text);
				selectionElement.appendChild(option);
			}

			// Selection of seamark category has changed
			function seamarkChanged() {
				old_seamark = _seamark;
				// reset old dialog visibility
				if (document.getElementById("boxEditLightCharacter").style.visibility == "visible") {
					showLightEdit(false);
				}
				if (document.getElementById("boxEditTopmark").style.visibility == "visible") {
					showTopmarkColour(false);
				}
				_category = document.getElementById("comboCategory").value;
				database = new DataModel();
				_seamark = database.get("meta", _category);

				if (old_seamark != _seamark) {
					if(_tags != "") {
						for(i = 0; i < _tags.length; i++) {
							var tag = _tags[i].split(",");
							values = tag[0].split(":");
							if(values[1] == old_seamark) {
								if (_seamark == "buoy_safe_water" && values[2] == "category") {
									setKey("seamark:" + old_seamark + ":category", "");
								} else {
									_tags[i] = "seamark:" + _seamark + ":" + values[2] + "," + tag[1];
								}
							}
						}
					}
				}
				setKey("seamark:category", _seamark);
				if (_category != "safe_water" && _category != "isolated_danger" && _category != "special_purpose") {
					setKey("seamark:" + _seamark + ":category", _category);
				}
				loadImages();
				onChangeCheck();
				if (document.getElementById("checkTopmark").checked == true && _category == "special_purpose") {
					showTopmarkColour(true);
				}
				if (document.getElementById("checkLight").checked == true) {
					showLightEdit(true);
				} 
			}

			function onChangeShape() {
				_buoy_shape = document.getElementById("comboShape").value;
				setKey("seamark:" + _seamark + ":shape", _buoy_shape);
			}
			
			function onChangeCheck() {
				if (document.getElementById("checkTopmark").checked == true && document.getElementById("checkLight").checked == false) {
					SetBuoyImage("buoyImageTop");
					document.getElementById("boxLightCharacter").style.visibility = "collapse";
				} else if (document.getElementById("checkLight").checked == true && document.getElementById("checkTopmark").checked == true) {
					SetBuoyImage("buoyImageTopLighted");
					document.getElementById("boxLightCharacter").style.visibility = "visible";
				} else if (document.getElementById("checkLight").checked == true && document.getElementById("checkTopmark").checked == false) {
					SetBuoyImage("buoyImageLighted");
					document.getElementById("boxLightCharacter").style.visibility = "visible";
				} else {
					SetBuoyImage("buoyImage");
					document.getElementById("boxLightCharacter").style.visibility = "collapse";
				}
			}

			// Selection of the Light checkbox has changed
			function onChangeLights() {
				if (document.getElementById("checkLight").checked == true) {
					setKey("seamark:light:colour", _light_colour);
					showLightEdit(true);
					saveLight();
				} else {
					setKey("seamark:light:colour", "");
					setKey("seamark:light:character", "");
					showLightEdit(false);
				}
				onChangeCheck();
			}

			// Selection of the Topmark checkbox has changed
			function onChangeTopmark() {
				if (document.getElementById("checkTopmark").checked == true) {
					setKey("seamark:topmark:shape", _topmark_shape);
					if (_category == "special_purpose") {
						showTopmarkColour(true);
					}
				} else {
					setKey("seamark:topmark:shape", "");
					if (_category == "special_purpose") {
						showTopmarkColour(false);
					}
				}
				onChangeCheck();
			}

			// Selection of the Fog signal checkbox has changed
			function onChangeFogSig() {
				if (document.getElementById("checkFogsignal").checked == true) {
					setKey("seamark:fog_signal", "yes");
				} else {
					setKey("seamark:fog_signal", "");
				}
				onChangeCheck();
			}

			// Selection of the radar reflector checkbox has changed
			function onChangeRadarRefl() {
				if (document.getElementById("checkRadar").checked == true) {
					setKey("seamark:radar_reflector", "yes");
				} else {
					setKey("seamark:radar_reflector", "");
				}
				onChangeCheck();
			}

			function SetBuoyImage(imageName) {
				document.getElementById("imageBuoy").src = eval(imageName + ".src")
			}

			// Show the light edit dialog
			function showLightEdit(show) {
				if (show) {
					document.getElementById("boxEditLightCharacter").style.visibility = "visible";
					moveDivDown("boxFogsignal", 22);
					if (_seamark != "buoy_cardinal") {
						document.getElementById("boxEditLightSequence").style.visibility = "visible";
						moveDivDown("boxFogsignal", 25);
					}
				} else {
					document.getElementById("boxEditLightCharacter").style.visibility = "hidden";
					moveDivUp("boxFogsignal", 22);
					if (_seamark != "buoy_cardinal") {
						document.getElementById("boxEditLightSequence").style.visibility = "hidden";
						moveDivUp("boxFogsignal", 25);
					}
				}
			}

			// Write keys for light
			function saveLight() {
				var buffCharacter = document.getElementById("lightChar").value;
				var period = document.getElementById("lightPeriod").value;

				if (buffCharacter != "" && buffCharacter != "unknown") {
					var buff = buffCharacter.split("(");
					var character = buff[0];
					if (_category == "south") {
						character += "+Lfl";
					}
					setKey("seamark:light:character", character);
					if (buff.length >=2) {
						var group = buff[1];
						group = group.split(")");
						setKey("seamark:light:group", group[0]);
					} else {
						setKey("seamark:light:group", "");
					}
					if (period != "" && period != "unknown" && period != " - - - ") {
						setKey("seamark:light:period",period);
					} else {
						setKey("seamark:light:period", "");
					}
					displayLight();
				}
			}

			//Display light character underneath the image and set values for edit dialog
			function displayLight() {
				var character = getKey("seamark:light:character");
				var group = getKey("seamark:light:group");
				var period = getKey("seamark:light:period");
				var val = "unbekannt";
					
				if (character != "-1" && character != "<?=$t->tr("unknown")?>") {
					if (_category == "south") {
						var buff = character.split("+");
						val = buff[0];
					} else {
						val = character;
					}
					if (group != "-1" && group != "<?=$t->tr("unknown")?>") {
						val += "(" + group + ")";
					}
					if (_category == "south") {
						val += "+Lfl";
					}
					document.getElementById("lightChar").value = val;
					onChangeLightCharacter();
					switch (_light_colour) {
						case "white":
							val += " W";
							break
						case "red":
							val += " R";
							break
						case "green":
							val += " G";
							break
					}
					if (period != "-1" && period != "unknown" && period != " - - - ") {
						document.getElementById("lightPeriod").value = period;
						val += " " + period + "s";
					}
				}
				document.getElementById("inputLightString").value = val;
			}

			function fillLightCombobox() {
				database = new DataModel();
				var selectionElement = document.getElementById("lightChar")
				clearSelectOptions();
				addSelectOption(selectionElement, "<?=$t->tr("unknown")?>");
				var values = database.get("light", "light_" + _category);
				var lights = values.split(":");
				for(i = 0; i < lights.length; i++) {
					addSelectOption(selectionElement, lights[i]);
				}
			}

			function onChangeLightCharacter() {
				var buff = document.getElementById("lightChar").value.split("(");
				if (buff[0] == "Q" || buff[0] == "VQ") {
					document.getElementById("lightPeriod").value = " - - - ";
					document.getElementById("lightPeriod").disabled = true;
				} else {
					document.getElementById("lightPeriod").value = "unknown";
					document.getElementById("lightPeriod").disabled = false;
				}
			}

			// Show topmark edit?
			function showTopmarkColour(show) {
				if (show) {
					document.getElementById("boxEditTopmark").style.visibility = "visible";
					moveDivDown("boxRadar", 22);
					moveDivDown("boxLight", 22);
					moveDivDown("boxFogsignal", 22);
					moveDivDown("boxEditLightCharacter", 22);
					moveDivDown("boxEditLightSequence", 22);
				} else {
					document.getElementById("boxEditTopmark").style.visibility = "hidden";
					moveDivUp("boxRadar", 22);
					moveDivUp("boxLight", 22);
					moveDivUp("boxFogsignal", 22);
					moveDivUp("boxEditLightCharacter", 22);
					moveDivUp("boxEditLightSequence", 22);
				}
			}

			// Write keys for Topmark
			function saveTopmark() {
				var colour = document.getElementById("topColour").value;
				if (colour != "" && colour != "unknown") {
					setKey("seamark:topmark:colour", colour);
				} else {
					setKey("seamark:topmark:colour", "");
				}
				document.getElementById("edit_topmark").style.visibility = "hidden";
			}
			
			// Show the topmark edit dialog
			function cancelTopmark() {
				document.getElementById("edit_topmark").style.visibility = "hidden";
			}

			function save() {
				// check for user login
				if (!opener.window.userName) {
					alert("Sie müssen angemeldet sein um die Daten zu speichern.");
					opener.window.loginUserSave();
					return;
				}
				opener.window.editSeamarkOk(createXML(), _mode);
				//alert(createXML());
				this.close();
			}

			function cancel() {
				if (_mode == "create" || _mode == "move") {
					opener.window.readOsmXml();
				} else {
					opener.window.onEditDialogCancel(_id);
				}
				this.close();
			}

			// create the XML-File for OSM-API
			function createXML() {
				var tagXML = "";
				var value = document.getElementById("inputName").value
				if (value != null) {
					setKey("seamark:name", value);
				}
				if(_tags != "") {
					for(i = 0; i < _tags.length; i++) {
						var tag = _tags[i].split(",");
						if (tag[0] != "") {
							tagXML += "<tag k=\"" + tag[0] + "\" v=\"" + tag[1] + "\"/>" + "\n";
						}
					}
				}
				//alert(tagXML);
				return tagXML
			}

			function getArgument(argument) {
				if(window.location.search != "") {
					// We have parameters
					var undef = document.URL.split("?");
					var args = undef[1].split("&");
					for(i = 0; i < args.length; i++) {
						var a = args[i].split("=");
						if(a[0] == argument) {
							return a[1];
						}
					}
					return "-1";
				}
				return "-1";
			}

			function getKey(key) {
				if(_tags != "") {
					for(i = 0; i < _tags.length; i++) {
						var tag = _tags[i].split(",");
						if(tag[0] == key) {
							return tag[1];
						}
					}
					return "-1";
				}
				return "-1";
			}

			function setKey(key, value) {
				if(_tags != "") {
					for(i = 0; i < _tags.length; i++) {
						var tag = _tags[i].split(",");
						if(tag[0] == key) {
							if (value == "") {
								_tags.splice(i, 1);
							} else {
								_tags[i] = key + "," + value;
							}
							return;
						}
					}
					if (value != "") {
						_tags.splice(0, 0, key + "," + value);
					}
				}
			}
		</script>
	</head>

	<body onload=init();>
		<div id="headerAdd" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkAdd")?></h2></div>
		<div id="headerEdit" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkEdit")?></h2></div>
		<div id="headerMove" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkMove")?></h2></div>
		<div id="headerDelete" style="position:absolute; top:0px; left:5px; visibility:hidden; color:red;"><h2><?=$t->tr("seamarkDelete")?></h2></div>
		<div id="titleCategory" style="position:absolute; top:80px; left:7px;"><?=$t->tr("seamarkCategory")?>:</div>
		<div id="boxCategory" style="position:absolute; top:80px; left:165px;">
			<select id="comboCategory" onChange="seamarkChanged()">
				<option value="unspecified"/><?=$t->tr("comboUnknown")?>
				<option value="safe_water"/>Ansteuerung
				<option value="starboard"/>Steuerbord
				<option value="port"/>Backbord
				<option value="preferred_channel_starboard"/>Abzweigung Steuerbord
				<option value="preferred_channel_port"/>Abzweigung Backbord
				<option value="north"/>Gefahr Nord
				<option value="east"/>Gefahr Ost
				<option value="south"/>Gefahr Süd
				<option value="west"/>Gefahr West
				<option value="isolated_danger"/>Einzelgefahrenzeichen
				<option value="special_purpose"/>Sonderzeichen
			</select>
		</div>
		<div id="titleType" style="position:absolute; top:120px; left:7px;"><?=$t->tr("seamarkType")?>:</div>
		<div id="boxType" style="position:absolute; top:120px; left:165px;">
			<select id="comboShape" onChange="onChangeShape()">
				<option selected value="unspecified"/><?=$t->tr("comboUnknown")?>
				<option value="sphere"/><?=$t->tr("sphere")?>
				<option value="conical"/><?=$t->tr("conical")?>
				<option value="can"/><?=$t->tr("can")?>
				<option value="barrel"/><?=$t->tr("barrel")?>
				<option value="pillar"/><?=$t->tr("pillar")?>
				<option value="spar"/><?=$t->tr("spar")?>
				<option value="beacon"/><?=$t->tr("beacon")?>
			</select>
		</div>
		<div id="titleMisc" style="position:absolute; top:160px; left:7px;"><?=$t->tr("miscItems")?>:</div>
		<div id="boxTopmark" style="position:absolute; top:160px; left:165px;">
			<input type="checkbox" id="checkTopmark" onclick="onChangeTopmark()"/><?=$t->tr("topmark")?>
		</div>
		<div id="boxRadar" style="position:absolute; top:182px; left:165px;">
			<input type="checkbox" id="checkRadar" onclick="onChangeRadarRefl()"/><?=$t->tr("radar")?>
		</div>
		<div id="boxLight" style="position:absolute; top:204px; left:165px;">
			<input type="checkbox" id="checkLight" onclick="onChangeLights()"/><?=$t->tr("lighted")?>
		</div>
		<div id="boxFogsignal" style="position:absolute; top:226px; left:165px;">
			<input type="checkbox" id="checkFogsignal" onclick="onChangeFogSig()"/><?=$t->tr("fogsignal")?>
		</div>
		<div style="position:absolute; bottom:80px; left:7px;"><?=$t->tr("seamarkName")?>:</div>
		<div style="position:absolute; bottom:80px; left:165px;">
			<input type="text" id="inputName" align="left"/>
		</div>
		<div style="position:absolute; top:70px; right:40px;">
			<img id="imageBuoy" src="../resources/Lateral_Green.png" align="center" border="0" />
		</div>
		<div id="boxLightCharacter" style="position:absolute; top:260px; right:20px; visibility:hidden;">
			<input type="text" id="inputLightString" align="left" size="10" value="Befeuerung" readonly="readonly"/>
		</div>
		<div style="position:absolute; bottom:20px; right:10px;">
			<input type="button" id="buttonSave" value='<?=$t->tr("save")?>' onclick="save()">
			&nbsp;&nbsp;
			<input type="button" id="buttonCancel" value='<?=$t->tr("cancel")?>' onclick="cancel()">
			&nbsp;&nbsp;
		</div>
		<div id="boxEditTopmark" style="position:absolute; top:179px; left:190px; width:188px; visibility:hidden;">
			<table border="0" width="100%">
				<tr>
					<td>
						<?=$t->tr("colour")?>:
					</td>
					<td align="right">
						<select id="topColour" onChange="saveTopmark()">
							<option value="unknown"/><?=$t->tr("unknown")?>
							<option value="red"/><?=$t->tr("red")?>
							<option value="yellow"/><?=$t->tr("yellow")?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="boxEditLightCharacter" style="position:absolute; top:222px; left:190px; width:188px; visibility:hidden;" >
			<table border="0" width="100%">
				<tr>
					<td>
						Kennung:
					</td>
					<td align="right">
						<select  name="light_character" id="lightChar" onChange="saveLight()">
							<option value="unknown"/><?=$t->tr("unknown")?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="boxEditLightSequence" style="position:absolute; top:246px; left:190px; width:188px; visibility:hidden;">
			<table border="0" width="100%">
				<tr>
					<td>
						Wiederkehr:
					</td>
					<td align="right">
						<input type="text" name="light_period" id="lightPeriod" size="5" style="text-align:right;" value="unknown" onChange="saveLight()"/>
						s
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>