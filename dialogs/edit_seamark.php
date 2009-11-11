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
		<meta http-equiv="content-language" content="<?=$t->getCurrentLanguage()?>" />
		<link rel="stylesheet" type="text/css" href="../map-edit.css">
		<script type="text/javascript" src="../javascript/DataModel.js"></script>
		<script type="text/javascript" src="../javascript/utilities.js"></script>
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
			var _saving = false;
			var _topmark_shape;

			function init() {
				database = new DataModel();
				_mode = getArgument("mode");
				if (_mode == "create") {
					document.getElementById("headerAdd").style.visibility = "visible";
					_category = getArgument("type")
					_seamark = database.get("meta", _category);
					_tags[0] = "seamark:type," + _seamark;
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
						// Temporal work around for writing all category tags to type
						if (_seamark == "-1") {
							_seamark = getKey("seamark:type");
						} else {
							setKey("seamark:type", _seamark)
						}
					} else if (buff == "buoy") {
						_seamark = database.get("trans", getKey("buoy"));
					} else {
						_seamark = buff;
					}

					switch (_mode) {
						case "delete":
							document.getElementById("headerDelete").style.visibility = "visible";
							document.getElementById("buttonSave").value = '<?=$t->tr("delete")?>';
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
				fillShapeCombobox();
				document.getElementById("comboCategory").value = _category;
				document.getElementById("comboShape").value = _buoy_shape;

				loadImages();
				if (getKey("seamark:topmark:shape") != "-1") {
					document.getElementById("checkTopmark").checked = true;
					document.getElementById("boxImageTop").style.visibility = "visible";
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
					document.getElementById("boxImageLight").style.visibility = "visible";
					if (_mode != "delete") {
						showLightEdit(true);
					}
				}
				var buff = getKey("seamark:name");
				if (buff != "-1") {
					document.getElementById("inputName").value = buff;
				}
				onChangeFogSig();
			}

			function loadImages() {
				switch (_category) {
					case "starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "conical":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Green_Cone.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Green_Conical_Low.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Green_Pillar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Green_Conical.png";
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_Green.png";
						break;
					case "port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Red_Spar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical.png";
								break;
							case "can":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Red_Can.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical_Low.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Red_Pillar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical.png";
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_Red.png";
						break;
					case "safe_water":
						_topmark_shape = "sphere";
						_light_colour = "white";
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_SafeWater_Spar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Sphere.png";
								break;
							case "sphere":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_SafeWater_Sphere.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Sphere_Low.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_SafeWater_Pillar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Sphere.png";
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png"
						break;
					case "preferred_channel_starboard":
						_topmark_shape = "cone";
						_light_colour = "green";
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "conical":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Pref_Starboard_Cone.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Green_Conical_Low.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Pref_Starboard_Pillar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Green_Conical.png";
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_Green.png";
						break;
					case "preferred_channel_port":
						_topmark_shape = "cylinder";
						_light_colour = "red";
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Pref_Port_Spar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical.png";
								break;
							case "can":
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Pref_Port_Can.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical_Low.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/lateral/Lateral_Pref_Port_Pillar.png";
								document.getElementById("fieldImageTop").src = "../resources/lateral/Topmark_Red_Cylindrical.png";
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_Red.png";
						break;
					case "north":
						_topmark_shape = "2_cones_up";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_North_Spar.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_North_Pillar.png";
								break;
						}
						document.getElementById("fieldImageTop").src = "../resources/cardinal/Topmark_Clear.png";
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
					case "east":
						_topmark_shape = "2_cones_base_together";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_East_Spar.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_East_Pillar.png";
								break;
						}
						document.getElementById("fieldImageTop").src = "../resources/cardinal/Topmark_Clear.png";
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
					case "south":
						_topmark_shape = "2_cones_down";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_South_Spar.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_South_Pillar.png";
								break;
						}
						document.getElementById("fieldImageTop").src = "../resources/cardinal/Topmark_Clear.png";
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
					case "west":
						_topmark_shape = "2_cones_point_together";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_West_Spar.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_West_Pillar.png";
								break;
						}
						document.getElementById("fieldImageTop").src = "../resources/cardinal/Topmark_Clear.png";
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
					case "isolated_danger":
						_topmark_shape = "2_spheres";
						_light_colour = "white";
						document.getElementById("checkTopmark").checked = true;
						document.getElementById("checkTopmark").disabled = true;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_Single_Spar.png";
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/cardinal/Cardinal_Single_Pillar.png";
								break;
						}
						
						document.getElementById("fieldImageTop").src = "../resources/cardinal/Topmark_Clear.png";
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
					case "special_purpose":
						_topmark_shape = "x-shape";
						_light_colour = "white";
						var colour = getKey("seamark:topmark:colour")
						if (colour != "-1") {
							document.getElementById("topColour").value = colour;
						}
						document.getElementById("checkTopmark").disabled = false;
						switch (_buoy_shape) {
							case "spar":
								document.getElementById("fieldImageBuoy").src = "../resources/special_purpose/Special_Purpose_Spar.png";
								if (getKey("seamark:topmark:colour") == "red") {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Red_Cross.png";
								} else {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Yellow_Cross.png";
								}
								break;
							case "barrel":
								document.getElementById("fieldImageBuoy").src = "../resources/special_purpose/Special_Purpose_Barrel.png";
								if (getKey("seamark:topmark:colour") == "red") {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Red_Cross_Low.png";
								} else {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Yellow_Cross_Low.png";
								}
								break;
							default:
								document.getElementById("fieldImageBuoy").src = "../resources/special_purpose/Special_Purpose_Pillar.png";
								if (getKey("seamark:topmark:colour") == "red") {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Red_Cross.png";
								} else {
									document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Yellow_Cross.png";
								}
								break;
						}
						document.getElementById("fieldImageLight").src = "../resources/light/Light_White.png";
						break;
				}
				fillLightCombobox();
				displayLight();
			}

			function fillShapeCombobox() {
				database = new DataModel();
				// workaround for getting translation until database works
				var translation = new Array();
				translation["sphere"] = "<?=$t->tr("sphere")?>";
				translation["conical"] = "<?=$t->tr("conical")?>";
				translation["can"] = "<?=$t->tr("can")?>";
				translation["barrel"] = "<?=$t->tr("barrel")?>";
				translation["pillar"] = "<?=$t->tr("pillar")?>";
				translation["spar"] = "<?=$t->tr("spar")?>";
				var selectionElement = document.getElementById("comboShape");
				clearSelectOptions(selectionElement);
				addSelectOption(selectionElement, "<?=$t->tr("comboUnknown")?>", "");
				var values = database.get("meta", "shape_" + _category);
				var shape = values.split(":");
				for(i = 0; i < shape.length; i++) {
					addSelectOption(selectionElement, shape[i], translation[shape[i]]);
				}
				selectionElement.value = _buoy_shape;
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
				while (selectionElement.options.length > 0) {
					selectionElement.options[0] = null;
				}
			}

			function addSelectOption(selectionElement, value, string) {
				var option = document.createElement("OPTION");
				var text = document.createTextNode(value);
				option.appendChild(text);
				option.value = value;
				if (string != "") {
					option.text = string;
				}
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
				setKey("seamark:type", _seamark);
				if (_category != "safe_water" && _category != "isolated_danger" && _category != "special_purpose") {
					setKey("seamark:" + _seamark + ":category", _category);
				}
				loadImages();
				if (document.getElementById("checkTopmark").checked == true && _category == "special_purpose") {
					showTopmarkColour(true);
				}
				if (document.getElementById("checkLight").checked == true) {
					showLightEdit(true);
				}
				fillShapeCombobox();
				onChangeTopmark();
			}

			function onChangeShape() {
				_buoy_shape = document.getElementById("comboShape").value;
				setKey("seamark:" + _seamark + ":shape", _buoy_shape);
				loadImages();
			}
			

			// Selection of the Light checkbox has changed
			function onChangeLights() {
				if (document.getElementById("checkLight").checked == true) {
					setKey("seamark:light:colour", _light_colour);
					document.getElementById("boxImageLight").style.visibility = "visible";
					showLightEdit(true);
					saveLight();
				} else {
					setKey("seamark:light:colour", "");
					setKey("seamark:light:character", "");
					document.getElementById("boxImageLight").style.visibility = "hidden";
					showLightEdit(false);
				}
			}

			// Selection of the Topmark checkbox has changed
			function onChangeTopmark() {
				if (document.getElementById("checkTopmark").checked == true) {
					setKey("seamark:topmark:shape", _topmark_shape);
					document.getElementById("boxImageTop").style.visibility = "visible";
					if (_category == "special_purpose") {
						showTopmarkColour(true);
					}
				} else {
					setKey("seamark:topmark:shape", "");
					document.getElementById("boxImageTop").style.visibility = "hidden";
					if (_category == "special_purpose") {
						showTopmarkColour(false);
					}
				}
			}

			// Selection of the Fog signal checkbox has changed
			function onChangeFogSig() {
				if (document.getElementById("checkFogsignal").checked == true) {
					setKey("seamark:fog_signal", "yes");
					document.getElementById("boxImageFogsignal").style.visibility = "visible";
				} else {
					setKey("seamark:fog_signal", "");
					document.getElementById("boxImageFogsignal").style.visibility = "hidden";
				}
			}

			// Selection of the radar reflector checkbox has changed
			function onChangeRadarRefl() {
				if (document.getElementById("checkRadar").checked == true) {
					setKey("seamark:radar_reflector", "yes");
				} else {
					setKey("seamark:radar_reflector", "");
				}
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
					document.getElementById("boxLightCharacter").style.visibility = "visible";
				} else {
					document.getElementById("boxEditLightCharacter").style.visibility = "hidden";
					moveDivUp("boxFogsignal", 22);
					if (_seamark != "buoy_cardinal") {
						document.getElementById("boxEditLightSequence").style.visibility = "hidden";
						moveDivUp("boxFogsignal", 25);
					}
					document.getElementById("boxLightCharacter").style.visibility = "hidden";
				}
			}

			// Write keys for light
			function saveLight() {
				var buffCharacter = document.getElementById("lightChar").value;
				var period = document.getElementById("lightPeriod").value;

				if (buffCharacter != "" && buffCharacter != "<?=$t->tr("unknown")?>") {
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
				var val = "<?=$t->tr("unknown")?>";
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
					if (period != "-1" && period != "<?=$t->tr("unknown")?>" && period != " - - - ") {
						document.getElementById("lightPeriod").value = period;
						val += " " + period + "s";
					}
				}
				document.getElementById("inputLightString").value = val;
			}

			function fillLightCombobox() {
				database = new DataModel();
				var selectionElement = document.getElementById("lightChar")
				clearSelectOptions(selectionElement);
				addSelectOption(selectionElement, "<?=$t->tr("unknown")?>", "");
				var values = database.get("light", "light_" + _category);
				var lights = values.split(":");
				for(i = 0; i < lights.length; i++) {
					addSelectOption(selectionElement, lights[i], "");
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
				saveLight()
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
				if (colour == "red"){
					if (_buoy_shape == "barrel") {
						document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Red_Cross_Low.png";
					} else {
						document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Red_Cross.png";
					}
				} else {
					if (_buoy_shape == "barrel") {
						document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Yellow_Cross_Low.png";
					} else {
						document.getElementById("fieldImageTop").src = "../resources/special_purpose/Topmark_Yellow_Cross.png";
					}
				}
				if (colour != "" && colour != "unknown") {
					setKey("seamark:topmark:colour", colour);
				} else {
					setKey("seamark:topmark:colour", "");
				}
			}

			function save() {
				opener.window.editSeamarkOk(createXML(), _mode);
				_saving = true;
				this.close();
			}

			function cancel() {
				this.close();
			}

			function onClosing() {
				if (_mode == "create" || _mode == "move") {
					opener.window.clearMoving();
					opener.window.readOsmXml();
				} else {
					opener.window.onEditDialogCancel(_id);
				}
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
							tagXML += "<tag k=\"" + convert2Web(tag[0]) + "\" v=\"" +  convert2Web(tag[1]) + "\"/>" + "\n";
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

	<body onload=init(); onUnload="if (!_saving) onClosing();">
		<div id="headerAdd" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkAdd")?></h2></div>
		<div id="headerEdit" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkEdit")?></h2></div>
		<div id="headerMove" style="position:absolute; top:0px; left:5px; visibility:hidden;"><h2><?=$t->tr("seamarkMove")?></h2></div>
		<div id="headerDelete" style="position:absolute; top:0px; left:5px; visibility:hidden; color:red;"><h2><?=$t->tr("seamarkDelete")?></h2></div>
		<div id="titleCategory" style="position:absolute; top:80px; left:7px;"><?=$t->tr("seamarkCategory")?>:</div>
		<div id="boxCategory" style="position:absolute; top:80px; left:165px;">
			<select id="comboCategory" onChange="seamarkChanged()">
				<option value="unspecified"/><?=$t->tr("comboUnknown")?>
				<option value="safe_water"/><?=$t->tr("comboSafeWater")?>
				<option value="starboard"/><?=$t->tr("comboStarboard")?>
				<option value="port"/><?=$t->tr("comboPort")?>
				<option value="preferred_channel_starboard"/><?=$t->tr("comboPrefStarboard")?>
				<option value="preferred_channel_port"/><?=$t->tr("comboPrefPort")?>
				<option value="north"/><?=$t->tr("comboNorth")?>
				<option value="east"/><?=$t->tr("comboEast")?>
				<option value="south"/><?=$t->tr("comboSouth")?>
				<option value="west"/><?=$t->tr("comboWest")?>
				<option value="isolated_danger"/><?=$t->tr("comboIsolatedDanger")?>
				<option value="special_purpose"/><?=$t->tr("comboSpecialPurpose")?>
			</select>
		</div>
		<div id="titleType" style="position:absolute; top:120px; left:7px;"><?=$t->tr("seamarkType")?>:</div>
		<div id="boxType" style="position:absolute; top:120px; left:165px;">
			<select id="comboShape" onChange="onChangeShape()">
				<option selected value="unspecified"/><?=$t->tr("comboUnknown")?>
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
		<div id="boxImageBuoy" style="position:absolute; top:70px; right:0px;">
			<img id="fieldImageBuoy" src="../resources/lateral/Lateral_Green_Pillar.png" width="256" height="320" align="center" border="0" />
		</div>
		<div id="boxImageTop" style="position:absolute; top:70px; right:0px; visibility:hidden;">
			<img id="fieldImageTop" src="../resources/lateral/Topmark_Green_Conical.png" width="256" height="320" align="center" border="0" />
		</div>
		<div id="boxImageLight" style="position:absolute; top:70px; right:0px; visibility:hidden;">
			<img id="fieldImageLight" src="../resources/light/Light_Green.png" width="256" height="320" align="center" border="0" />
		</div>
		<div id="boxImageFogsignal" style="position:absolute; top:70px; right:0px; visibility:hidden;">
			<img id="fieldImageFogsignal" src="../resources/misc/Fogsignal.png" width="256" height="320" align="center" border="0" />
		</div>
		<div id="boxLightCharacter" style="position:absolute; top:280px; right:20px; background-color:grey; visibility:hidden;">
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
						<?=$t->tr("character")?>:
					</td>
					<td align="right">
						<select  name="light_character" id="lightChar" onChange="onChangeLightCharacter();">
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
						<?=$t->tr("period")?>:
					</td>
					<td align="right">
						<input type="text" name="light_period" id="lightPeriod" size="5" style="text-align:right;" value='<?=$t->tr("unknown")?>' onChange="saveLight()"/>
						s
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>