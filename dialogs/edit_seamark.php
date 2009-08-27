<?php
	include("../../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="de" />
		<title>Seezeichen Bearbeiten</title>
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

			buoyImage = new Image();
			buoyImageTop = new Image();
			buoyImageLighted = new Image();
			buoyImageTopLighted = new Image();

			function init() {
				_mode = getArgument("mode");
				if (_mode == "create") {
					document.getElementById("header_add").style.visibility = "visible";
					database = new DataModel();
					_category = getArgument("type")
					_seamark = database.get("meta", _category);
					if (_category != "safe_water" && _category != "isolated_danger" && _category != "special_purpose") {
						_tags[0] = "seamark:" + _seamark + ":category," + _category;
					} else {
						_tags[0] = "seamark:category," + _seamark;
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
							document.getElementById("header_delete").style.visibility = "visible";
							document.AddLateral.save_button.value = "Löschen";
							break
						case "move":
							document.getElementById("header_move").style.visibility = "visible";
							break
						case "update":
							document.getElementById("header_edit").style.visibility = "visible";
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

				document.AddLateral.seamark_category.value = _category;
				document.AddLateral.seamark_shape.value = _buoy_shape;

				if (getKey("seamark:topmark:shape") != "-1") {
					document.AddLateral.top.checked = true;
				}
				if (getKey("seamark:fog_signal") != "-1") {
					document.AddLateral.signal.checked = true;
				}
				if (getKey("seamark:radar_reflector") != "-1") {
					document.AddLateral.radar.checked = true;
				}
				if (getKey("seamark:light:colour") != "-1") {
					document.AddLateral.light.checked = true;
					document.AddLateral.lightchr.value = getKey("seamark:light:character");
				}
				var buff = getKey("seamark:" + _seamark + ":ref");
				if (buff != "-1") {
					document.AddLateral.ref.value = buff;
				}
				loadImages();
				onChangeCheck();
			}

			function loadImages() {
				switch (_category) {
					case "starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Green.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Green_Conical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Green_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Green_Conical_Lighted.png";
						break;
					case "port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Red.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Red_Cylindrical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Red_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Red_Cylindrical_Lighted.png";
						break;
					case "safe_water":
						_topmark_shape = "sphere";
						_light_colour = "white";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_SafeWater.png";
						buoyImageTop.src = "../resources/lateral/Lateral_SafeWater_Sphere.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_SafeWater_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_SafeWater_Sphere_Lighted.png";
						break;
					case "preferred_channel_starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Pref_Starboard.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Pref_Starboard_Conical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Pref_Starboard_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Pref_Starboard_Conical_Lighted.png";
						break;
					case "preferred_channel_port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/lateral/Lateral_Pref_Port.png";
						buoyImageTop.src = "../resources/lateral/Lateral_Pref_Port_Cylindrical.png";
						buoyImageLighted.src = "../resources/lateral/Lateral_Pref_Port_Lighted.png";
						buoyImageTopLighted.src = "../resources/lateral/Lateral_Pref_Port_Cylindrical_Lighted.png";
						break;
					case "north":
						_topmark_shape = "2_cones_up";
						_light_colour = "white";
						document.AddLateral.top.checked = true;
						document.AddLateral.top.disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_North.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_North.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_North_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_North_Lighted.png";
						break;
					case "east":
						_topmark_shape = "2_cones_base_together";
						_light_colour = "white";
						document.AddLateral.top.checked = true;
						document.AddLateral.top.disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_East.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_East.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_East_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_East_Lighted.png";
						break;
					case "south":
						_topmark_shape = "2_cones_down";
						_light_colour = "white";
						document.AddLateral.top.checked = true;
						document.AddLateral.top.disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_South.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_South.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_South_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_South_Lighted.png";
						break;
					case "west":
						_topmark_shape = "2_cones_point_together";
						_light_colour = "white";
						document.AddLateral.top.checked = true;
						document.AddLateral.top.disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_West.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_West.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_West_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_West_Lighted.png";
						break;
					case "isolated_danger":
						_topmark_shape = "2_spheres";
						_light_colour = "white";
						document.AddLateral.top.checked = true;
						document.AddLateral.top.disabled = true;
						buoyImage.src = "../resources/cardinal/Cardinal_Single.png";
						buoyImageTop.src = "../resources/cardinal/Cardinal_Single.png";
						buoyImageLighted.src = "../resources/cardinal/Cardinal_Single_Lighted.png";
						buoyImageTopLighted.src = "../resources/cardinal/Cardinal_Single_Lighted.png";
						break;
					case "special_purpose":
						_topmark_shape = "x-shape";
						_light_colour = "white";
						document.AddLateral.top.disabled = false;
						buoyImage.src = "../resources/special_purpose/Special_Purpose.png";
						buoyImageTop.src = "../resources/special_purpose/Special_Purpose_x-Shape.png";
						buoyImageLighted.src = "../resources/special_purpose/Special_Purpose_Lighted.png";
						buoyImageTopLighted.src = "../resources/special_purpose/Special_Purpose_x-Shape_Lighted.png";
						break;
				}
				onChangeLights();
				onChangeTopmark();
			}

			// Selection of seamark category has changed
			function seamarkChanged() {
				old_seamark = _seamark;
				_category = document.AddLateral.seamark_category.value;
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
				if (_category != "safe_water" && _category != "isolated_danger" && _category != "special_purpose") {
					_tags[0] = "seamark:" + _seamark + ":category," + _category;
				}
				_tags[0] = "seamark:category," + _seamark;
				loadImages();
				onChangeLights();
				onChangeTopmark(); 
			}

			function onChangeShape() {
				_buoy_shape = document.AddLateral.seamark_shape.value;
				setKey("seamark:" + _seamark + ":shape", _buoy_shape);
			}
			
			function onChangeCheck() {
				if (document.AddLateral.top.checked == true && document.AddLateral.light.checked == false) {
					SetBuoyImage("buoyImageTop");
					document.getElementById("light_chr").style.visibility = "collapse";
				} else if (document.AddLateral.light.checked == true && document.AddLateral.top.checked == true) {
					SetBuoyImage("buoyImageTopLighted");
					document.getElementById("light_chr").style.visibility = "visible";
				} else if (document.AddLateral.light.checked == true && document.AddLateral.top.checked == false) {
					SetBuoyImage("buoyImageLighted");
					document.getElementById("light_chr").style.visibility = "visible";
				} else {
					SetBuoyImage("buoyImage");
					document.getElementById("light_chr").style.visibility = "collapse";
				}
			}

			// Selection of the Light checkbox has changed
			function onChangeLights() {
				if (document.AddLateral.light.checked == true) {
					setKey("seamark:light:colour", _light_colour);
					setKey("seamark:light:character", document.AddLateral.lightchr.value);
				} else {
					setKey("seamark:light:colour", "");
					setKey("seamark:light:character", "");
				}
				onChangeCheck()
			}

			// Selection of the Topmark checkbox has changed
			function onChangeTopmark() {
				if (document.AddLateral.top.checked == true) {
					setKey("seamark:topmark:shape", _topmark_shape);
				} else {
					setKey("seamark:topmark:shape", "");
				}
				onChangeCheck()
			}

			// Selection of the Fog signal checkbox has changed
			function onChangeFogSig() {
				if (document.AddLateral.signal.checked == true) {
					setKey("seamark:fog_signal", "yes");
				} else {
					setKey("seamark:fog_signal", "");
				}
				onChangeCheck()
			}

			// Selection of the radar reflector checkbox has changed
			function onChangeRadarRefl() {
				if (document.AddLateral.radar.checked == true) {
					setKey("seamark:radar_reflector", "yes");
				} else {
					setKey("seamark:radar_reflector", "");
				}
				onChangeCheck()
			}

			function SetBuoyImage(imageName) {
				document.AddLateral.buoyImg.src = eval(imageName + ".src")
			}

			function save() {
				// check for user login
				if (!opener.window.userName) {
					alert("Sie müssen angemeldet sein um die Daten zu speichern.");
					opener.window.loginUserSave();
					return;
				}
				opener.window.editSeamarkOk(createXML(), _mode);
				this.close();
			}

			function cancel() {
				if (_mode == "create" || _mode == "move") {
					opener.window.updateSeamarks();
				}
				this.close();
			}

			// create the XML-File for OSM-API
			function createXML() {
				var tagXML = "";
				if (document.AddLateral.light.checked == true) {
					setKey("seamark:light:character", document.AddLateral.lightchr.value);
				}
				if (document.AddLateral.ref.value != null) {
					setKey("seamark:name", document.AddLateral.ref.value);
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
		<div id="header_add" style="position:absolute; top:2px; left:5px; visibility:hidden;"><h2>Seezeichen hinzufügen</h2></div>
		<div id="header_edit" style="position:absolute; top:2px; left:5px; visibility:hidden;"><h2>Seezeichen bearbeiten</h2></div>
		<div id="header_move" style="position:absolute; top:2px; left:5px; visibility:hidden;"><h2>Seezeichen verschieben</h2></div>
		<div id="header_delete" style="position:absolute; top:2px; left:5px; visibility:hidden;"><h2>Seezeichen löschen</h2></div>
		<div id="body_form" style="position:absolute; top:65px; left:0px;">
		<form name="AddLateral" action="">
			<table border="0" cellpadding="5">
				<tr>
					<td>
						Art des Zeichens:&nbsp;
					</td>
					<td>
						<select  name="seamark_category" onChange="seamarkChanged()">
							<option value="unspecified"/>Unbekannt- - - - - - - - - - - - -
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
					</td>
					<td rowspan="3" align="center" valign="middle" width="250" height="250">
						<img name="buoyImg" src="resources/Lateral_Green.png" align="center" border="0" /><br>
					</td>
				</tr>
				<tr>
					<td valign="top">
						Bauart des Zeichens:&nbsp;
					</td>
					<td valign="top">
						<select name="seamark_shape" onChange="onChangeShape()">
							<option selected value="unspecified"/>Unbekannt- - - - - - - - - - - - -
							<option value="sphere"/>Kugeltonne
							<option value="conical"/>Spitztonne
							<option value="can"/>Stumpftonne
							<option value="barrel"/>Fasstonne
							<option value="pillar"/>Bakentonne
							<option value="spar"/>Spierentonne
							<option value="beacon"/>Spiere
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top">
						Weitere Eigenschaften:
					</td>
					<td valign="top">
						<input type="checkbox" name="top" value="topmar" onclick="onChangeTopmark()"/> Topzeichen<br>
						<input type="checkbox" name="radar" value="racon" onclick="onChangeRadarRefl()"/> Radarreflektor<br>
						<input type="checkbox" name="light" value="lights" onclick="onChangeLights()"/> Befeuert<br>
						<input type="checkbox" name="signal" value="fogsig" onclick="onChangeFogSig()"/> Nebelhorn
					</td>
				</tr>
				<tr>
					<td valign="top">
						Bezeichnung des Zeichens:
					</td>
					<td valign="top">
						<input type="text" name="ref" align="left"/>
					</td>
					<td valign="top" align="right">
						<div id="light_chr" style="visibility:hidden;">
								<input type="text" name="lightchr" align="left" value="unknown"/>
						</div>
					</td>
				</tr>
			</table>
			<p align="right">
				<br>
				<input type="button" name="save_button" value="Speichern" onclick="save()">
				&nbsp;&nbsp;
				<input type="button" name="cancel_button" value="Abbrechen" onclick="cancel()">
				&nbsp;&nbsp;
			</p>
		</form>
		</div>
	</body>
</html>