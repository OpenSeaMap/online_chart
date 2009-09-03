<?php
	include("../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">

<html>
	<head>
		<title>OpenSeaMap: Karte bearbeiten</title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="de" />
		<link rel="stylesheet" type="text/css" href="map-edit.css">
		<script type="text/javascript" src="http://www.openlayers.org/api/OpenLayers.js"></script>
		<script type="text/javascript" src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
		<script type="text/javascript" src="javascript/prototype.js"></script>
		<script type="text/javascript">

			//global variables
			var map;
			var layer_mapnik;
			var layer_tah;
			var layer_markers;
			var _ChangeSetId = "-1";		//OSM-Changeset ID
			var _NodeId = "-1";				//OSM-Node ID
			var _Comment = null;			//Comment for Changeset
			var _Version = null;			//Version of the node
			var NodeXml = null;				//XML-Data for node creation
			var userName = null;			//OSM-Username of the user
			var userPassword = null;		//OSM-Password of the user
			var controls;					//OpenLayer-Controls
			var _ToDo = null;				//actually selected action
			var click;						//click-event
			var seamarkType;				//seamarks
			var arrayMarker = new Array();	//Array of displayed Markers
			var arrayNodes = new Array();	//Array of available Nodes

			// position and zoomlevel (will be overriden with permalink parameters)
			var lon = 12.0915;
			var lat = 54.1878;
			var zoom = 16;

			function jumpTo(lon, lat, zoom) {
				var x = Lon2Merc(lon);
				var y = Lat2Merc(lat);
				if (!map.getCenter()) {
					map.setCenter(new OpenLayers.LonLat(x, y), zoom);
				}
				return false;
			}

			function Lon2Merc(lon) {
				return 20037508.34 * lon / 180;
			}

			function Lat2Merc(lat) {
				var PI = 3.14159265358979323846;
				lat = Math.log(Math.tan( (90 + lat) * PI / 360)) / (PI / 180);
				return 20037508.34 * lat / 180;
			}

			function plusfacteur(a) { return a * (20037508.34 / 180); }
			function moinsfacteur(a) { return a / (20037508.34 / 180); }
			function y2lat(a) { return 180/Math.PI * (2 * Math.atan(Math.exp(moinsfacteur(a)*Math.PI/180)) - Math.PI/2); }
			function lat2y(a) { return plusfacteur(180/Math.PI * Math.log(Math.tan(Math.PI/4+a*(Math.PI/180)/2))); }
			function x2lon(a) { return moinsfacteur(a); }
			function lon2x(a) { return plusfacteur(a); }

			function getTileURL(bounds) {
				var res = this.map.getResolution();
				var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
				var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
				var z = this.map.getZoom();
				var limit = Math.pow(2, z);
				if (y < 0 || y >= limit) {
					return null;
				} else {
					x = ((x % limit) + limit) % limit;
					url = this.url;
					path= z + "/" + x + "/" + y + "." + this.type;
					if (url instanceof Array) {
						url = this.selectUrl(path, url);
					}
					return url+path;
				}
			}

			OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
				defaultHandlerOptions: {
					'single': true,
					'double': false,
					'pixelTolerance': 0,
					'stopSingle': false,
					'stopDouble': false
				},
				initialize: function(options) {
					this.handlerOptions = OpenLayers.Util.extend(
						{}, this.defaultHandlerOptions
					);
					OpenLayers.Control.prototype.initialize.apply(
						this, arguments
					);
					this.handler = new OpenLayers.Handler.Click(
						this, {
							'click': this.trigger
						}, this.handlerOptions
					);
				},

				trigger: function(e) {
					var lonlat = map.getLonLatFromViewPortPx(e.xy);
					var pos  = lonlat.transform(map.getProjectionObject(),map.displayProjection);
					lon = pos.lon;
					lat = pos.lat;
					clickSeamarkMap();
				}
			});

			// Map event listener
			function mapEvent(event) {
				// later needed for data update
			}

			// Draw the map
			function drawmap() {

				OpenLayers.Lang.setCode('de');

				map = new OpenLayers.Map('map', {
					projection: new OpenLayers.Projection("EPSG:900913"),
					displayProjection: new OpenLayers.Projection("EPSG:4326"),
					eventListeners: {
						"moveend": mapEvent,
						"zoomend": mapEvent
					},
					controls: [
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.MouseDefaults(),
						new OpenLayers.Control.LayerSwitcher(),
						new OpenLayers.Control.MousePosition(),
						new OpenLayers.Control.ScaleLine(),
						new OpenLayers.Control.PanZoomBar()],
						maxExtent:
						new OpenLayers.Bounds(-20037508.34, -20037508.34, 20037508.34, 20037508.34),
					numZoomLevels: 18,
					maxResolution: 156543,
					units: 'meters'
				});

				// Mapnik
				layer_mapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
				// Osmarender
				layer_tah = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
				// seamark
				layer_seamap = new OpenLayers.Layer.TMS("Seezeichen", "../tiles/",
				{ numZoomLevels: 18, type: 'png', getURL: getTileURL, isBaseLayer: false, displayOutsideMaxExtent: true});
				// markers
				layer_markers = new OpenLayers.Layer.Markers("Address",
				{ projection: new OpenLayers.Projection("EPSG:4326"), visibility: true, displayInLayerSwitcher: false });
				// click events
				click = new OpenLayers.Control.Click();

				map.addLayers([layer_mapnik, layer_tah, layer_markers, layer_seamap]);
				map.addControl(click);

				jumpTo(lon, lat, zoom);
			}

			// add a marker on the map
			function addMarker(id, popupText) {
				var pos = new OpenLayers.LonLat(Lon2Merc(lon), Lat2Merc(lat));
				var feature = new OpenLayers.Feature(layer_markers, pos);
				feature.closeBox = true;
				feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {minSize: new OpenLayers.Size(260, 100) } );
				feature.data.popupContentHTML = popupText;
				feature.data.overflow = "hidden";

				arrayMarker[id] = new OpenLayers.Marker(pos);
				arrayMarker[id].feature = feature;

				markerClick = function(evt) {
					if (this.popup == null) {
						this.popup = this.createPopup(this.closeBox);
						map.addPopup(this.popup);
						this.popup.show();
					} else {
						this.popup.toggle();
					}
				};
				layer_markers.addMarker(arrayMarker[id]);
				arrayMarker[id].events.register("mousedown", feature, markerClick);
			}

			// remove a marker from the map
			function removeMarker() {
				layer_markers.removeMarker(arrayMarker[_NodeId]);
			}

			// Send new node to OSM_Database
			function sendNodeOsm(action) {
				// Creating XML
				var xmlOSM = "<\?xml version='1.0' encoding='UTF-8'\?>\n"
				xmlOSM += "<osm version=\"0.6\" generator=\"OpenSeaMap-Editor\"> \n";
				xmlOSM += "<node id=\"" + _NodeId + "\" changeset=\"" + _ChangeSetId + "\" version=\"" + _Version + "\" lat=\"" + lat + "\" lon=\"" + lon + "\">\n";
				xmlOSM += NodeXml;
				xmlOSM += "</node>\n</osm>";
				// Sending content
				osmNode(action, xmlOSM);
			}

			function closeChangeSetOsm(comment) {
				_ChangeSetId = "-1";
			}

			function clickSeamarkMap() {
				document.getElementById("pos-lat").value = lat;
				document.getElementById("pos-lon").value = lon;
				// workaround for openlayers resetting the cursor style on click
				map.div.style.cursor="crosshair";
			}

			function onPositionDialogCancel() {
				// hide position dialog
				document.getElementById("position_dialog").style.visibility = "collapse";
				// disable click event
				map.div.style.cursor="default";
				click.deactivate();
			}

			function addSeamark(seamark) {
				document.getElementById("position_dialog").style.visibility = "visible";
				document.getElementById("add_seamark_dialog").style.visibility = "collapse";
				// activate click event for entering a new position
				click.activate();
				map.div.style.cursor="crosshair";
				// set the seamark type
				seamarkType = seamark;
				// remember what we are doing
				_ToDo = "add";
			}

			function addSeamarkPosOk(latValue, lonValue) {
				lon = parseFloat(lonValue);
				lat = parseFloat(latValue);
				_NodeId = "11";
				addMarker(_NodeId, "");
				addSeamarkEdit();
			}

			function addSeamarkEdit() {
				editWindow = window.open("./dialogs/edit_seamark.php" + "?mode=create&type=" + seamarkType, "Bearbeiten", "width=650, height=450, resizable=yes");
 				editWindow.focus();
			}

			// Editing of the Seamark finished with OK
			function editSeamarkOk(xmlTags, action) {
				NodeXml = xmlTags;
				sendWindow = window.open("./sending.php?action=" + action, "Sending", "width=460, height=170, resizable=yes");
			}

			function editSeamarkEdit(id, version, pos_lat, pos_lon) {
				_Version = version;
				_NodeId = id;
				lat = pos_lat;
				lon = pos_lon;

				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				editWindow = window.open("./dialogs/edit_seamark.php?mode=update&id=" + id + "&version=" + version, "Bearbeiten", "width=650, height=450, resizable=yes");
 				editWindow.focus();
			}

			function moveSeamarkEdit(id, version) {
				_NodeId = id;
				_Version = version;
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				document.getElementById("position_dialog").style.visibility = "visible";
				// activate click event for entering a new position
				click.activate();
				map.div.style.cursor="crosshair";
				// remember what we are doing
				_ToDo = "move";
			}

			function moveSeamarkOk() {
				// remove old marker
				removeMarker();
				// set popup text for the new marker
				var popupText = "ID = " + _NodeId;
				popupText += " - Lat = " + lat;
				popupText += " - Lon = " + lon;
				popupText += " - Version = " + _Version;
				// add marker at the new position
				addMarker(_NodeId, popupText);
				moveSeamarkSave();
			}

			function moveSeamarkSave() {
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				editWindow = window.open("./dialogs/edit_seamark.php?mode=move&id=" + _NodeId + "&version=" + _Version, "Bearbeiten", "width=650, height=450, resizable=yes");
 				editWindow.focus();
			}

			function deleteSeamarkEdit(id, version) {
				_NodeId = id;
				_Version = version;
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				editWindow = window.open("./dialogs/edit_seamark.php?mode=delete&id=" + id + "&version=" + version, "Löschen", "width=650, height=450, resizable=yes");
 				editWindow.focus();
			}

			// Entering a new position finished
			function positionOk(latValue, lonValue) {
				switch (_ToDo) {
					case "add":
						addSeamarkPosOk(latValue, lonValue);
						break;
					case "move":
						moveSeamarkOk();
						break;
				}
				// nothing todo left
				_ToDo = null;
				// disable click event
				map.div.style.cursor="default";
				click.deactivate();
				// hide position dialog
				document.getElementById("position_dialog").style.visibility = "collapse";
			}

			// Open login window
			function loginUser() {
				loginWindow = window.open("./dialogs/user-login.html", "Login", "width=380, height=200, resizable=yes");
 				loginWindow.focus();
			}

			// Open login window
			function loginUserSave() {
				loginWindow = window.open("./user-login.html", "Login", "width=380, height=200, resizable=yes");
 				loginWindow.focus();
			}

			// Logout user and close changeset
			function logoutUser() {
				// close changeset
				osmChangeSet("close", "void");
				// delete user data
				userName = null;
				userPassword = null;
				// show login screen on the sidebar
				document.getElementById("login").style.visibility = "visible";
				document.getElementById("logout").style.visibility = "collapse";
			}

			// Get user name and password from login dialog
			function loginUser_login(username, password) {
				userName = username;
				userPassword = password;
				document.getElementById("login").style.visibility = "collapse";
				document.getElementById("logout").style.visibility = "visible";
			}

			function showSeamarkAdd(visible) {
				if (visible == "true") {
					document.getElementById("add_seamark_dialog").style.visibility = "visible";
					document.getElementById("add_landmark_dialog").style.visibility = "collapse";
					document.getElementById("add_harbour_dialog").style.visibility = "collapse";
				} else {
					document.getElementById("add_seamark_dialog").style.visibility = "collapse";
				}
			}

			function showLandmarkAdd(visible) {
				if (visible == "true") {
					document.getElementById("add_landmark_dialog").style.visibility = "visible";
					document.getElementById("add_seamark_dialog").style.visibility = "collapse";
					document.getElementById("add_harbour_dialog").style.visibility = "collapse";
				} else {
					document.getElementById("add_landmark_dialog").style.visibility = "collapse";
				}
			}

			function showHarbourAdd(visible) {
				if (visible == "true") {
					document.getElementById("add_harbour_dialog").style.visibility = "visible";
					document.getElementById("add_seamark_dialog").style.visibility = "collapse";
					document.getElementById("add_landmark_dialog").style.visibility = "collapse";
				} else {
					document.getElementById("add_harbour_dialog").style.visibility = "collapse";
				}
			}


			function updateNode() {
				// FIXME: it is not necessary to reload all nodes. The updated one should be enough.
				updateSeamarks();
			}

			function osmChangeSet(action, todo) {
				var url = './api/changeset.php';
				var params = new Object();

				params["action"] = action;
				params["id"] = _ChangeSetId;
				params["comment"] = _Comment;
				params["userName"] = userName;
				params["userPassword"] = userPassword;

				document.getElementById("creating").style.visibility = "visible";

				new Ajax.Request(url, {
					method: 'get',
					parameters : params,
					onSuccess: function(transport) {
						var response = transport.responseText;
						if (action = "create") {
							if (parseInt(response) > 0) {
								_ChangeSetId = response;
								//alert(_ChangeSetId + " : " + todo);
								sendNodeOsm(todo);
								document.getElementById("creating").style.visibility = "collapse";
								return "0";
							} else {
								document.getElementById("creating").style.visibility = "collapse";
								alert("Erzeugen des Changesets Fehlgeschlagen");
								return "-1";
							}
						}
					},
					onFailure: function() {
						document.getElementById("creating").style.visibility = "collapse";
						alert("damm");
						return "-1";
					},
					onException: function(request, exception) {
						document.getElementById("creating").style.visibility = "collapse";
						alert("mist: " + exception + request);
						return "-1";
					}
				});
			}

			function osmNode(action, data) {
				var url = "./api/node.php";
				var params = new Object();
				params["action"] = action;
				params["changeset_id"] = _ChangeSetId;
				params["node_id"] = _NodeId;
				params["comment"] = _Comment;
				params["name"] = userName;
				params["password"] = userPassword;
				params["data"] = data;

				document.getElementById("saving").style.visibility = "visible";

				new Ajax.Request(url, {
					method: "get",
					parameters : params,
					onSuccess: function(transport) {
						var response = transport.responseText;
						switch (action) {
							case "create":
								_NodeId = response;
							case "update":
							case "delete":
								updateNode();
								break;
							case "get":
								alert("Node= " + response);
								break;
						}
						document.getElementById("saving").style.visibility = "collapse";
						return "0";
					},
					onFailure: function() {
						document.getElementById("saving").style.visibility = "collapse";
						alert("Error while sending data");
						return "-1";
					},
					onException: function(request, exception) {
						document.getElementById("saving").style.visibility = "collapse";
						alert("Error: " + exception + request);
						return "-1";
					}
				});
			}

			// Get seamarks from database
			function updateSeamarks() {
				var zoomLevel = map.getZoom();
				if (zoomLevel > 15) {
					document.getElementById("loading").style.visibility = "visible";

					var url = './api/map.php';
					var params = new Object();
					var bounds = map.getExtent().toArray();
					params["n"] = y2lat(bounds[3]);
					params["s"] = y2lat(bounds[1]);
					params["w"] = x2lon(bounds[0]);
					params["e"] = x2lon(bounds[2]);

					new Ajax.Request(url, {
						method: 'get',
						parameters : params,
						onSuccess: function(transport) {
							var response = transport.responseText;
							layer_markers.clearMarkers();
							readOsmXml(response);
							//alert(response);
							document.getElementById("loading").style.visibility = "collapse";
							return "0";
						},
						onFailure: function() {
							alert("Error while sending data");
							document.getElementById("loading").style.visibility = "collapse";
							return "-1";
						},
						onException: function(request, exception) {
							alert("Error: " + exception);
							document.getElementById("loading").style.visibility = "collapse";
							return "-1";
						}
					});
				} else {
					alert("Der Zoomlevel ist zu klein!");
				}
			}

			function readOsmXml(xmlData) {
				xmlParser = new DOMParser();
				var xmlObject = xmlParser.parseFromString(xmlData, "text/xml");
				var root = xmlObject.getElementsByTagName('osm')[0];
				var items = root.getElementsByTagName("node");

				for (var i=0; i < items.length; ++i) {
					// get one node after the other
					var item = items[i];
					// Ensure Seamark is visible (don't add deleted ones)
					if(item.getAttribute("visible") == "true") {
						// get Lat/Lon of the node
						lat = parseFloat(item.getAttribute("lat"));
						lon = parseFloat(item.getAttribute("lon"));
						id = item.getAttribute("id");
						var version = parseInt(item.getAttribute("version"));
						// Set head of the popup text
						var popupText = "ID = " + id;
						popupText += " - Lat = " + lat;
						popupText += " - Lon = " + lon;
						popupText += " - Version = " + version;
						popupText += "<br/> <br/>";
						arrayNodes[id] = "";

						// Getting the tags (key value pairs)
						var tags = item.getElementsByTagName("tag");
						for (var n=0; n < tags.length; ++n) {
							var tag = tags[n];
							var key = tag.getAttribute("k");
							var val = tag.getAttribute("v");
							arrayNodes[id] += key + "," + val + "|";
							popupText += "<br/><input type=\"text\"  size=\"25\"  name=\"kev\" value=\"" + key + "\"/>";
							popupText += " - <input type=\"text\" name=\"value\" value=\"" + val + "\"/>";
						}
						popupText += "<br/> <br/>";
						popupText += "<input type=\"button\" value=\"Bearbeiten\" onclick=\"editSeamarkEdit(" + id + "," + version + "," + lat + "," + lon + ")\">&nbsp;&nbsp;";
						popupText += "<input type=\"button\" value=\"Verschieben\"onclick=\"moveSeamarkEdit(" + id + "," + version + ")\">&nbsp;&nbsp;";
						popupText += "<input type=\"button\" value=\"Löschen\"onclick=\"deleteSeamarkEdit(" + id + "," + version + ")\">";
						addMarker(id, popupText);
					}
				}
				//FIXME: dirty hack for redrawing the map. Needed for popup click events.
				map.zoomOut();
				map.zoomIn();
			}

			// Some api stuff
			function getChangeSetId() {
				return _ChangeSetId;
			}

			function setChangeSetId(id) {
				_ChangeSetId = id;
			}

			function getComment() {
				return _Comment;
			}

			function setComment(value) {
				_Comment = value;
			}

			function getKeys(id) {
				return arrayNodes[id];
			}

		</script>
	</head>
	<body onload=drawmap();>
		<div id="head" style="position:absolute; top:2px; left:0px;">
			<a><b>OpenSeaMap - Editor</b></a>
		</div>
		<div id="login" style="position:absolute; top:30px; left:0px;">
			<hr>
			<form name="login" action="">
				<a>Sie müssen amgemeldet sein um die Karte bearbeiten zu können.<br/><br/></a>
				<input type="button" value="Anmelden" onclick="loginUser()">
			</form>
		</div>
		<div id="logout" style="position:absolute; top:30px; left:0px; visibility:hidden;" >
			<hr>
			<form name="logout" action="">
				<p>Sie sind angemeldet.</p>
				<input type="button" value="Abmelden" onclick="logoutUser()" >
			</form>
		</div>
		<div style="position:absolute; top:140px; left:11.5%;"><a href="http://wiki.openstreetmap.org/wiki/de:Seekarte" target="blank">Hilfe</a></div>
		<div id="data" style="position:absolute; top:150px; left:0px;">
			<hr>
			<a><b>Daten</b></a><br/><br/>
			<input type="button" value="Laden" onclick="updateSeamarks()">
		</div>
		<div style="position:absolute; top:230px; left:11.5%;"><a href="http://wiki.openstreetmap.org/wiki/de:Seekarte" target="blank">Hilfe</a></div>
		<div id="action" style="position:absolute; top:240px; left:0px;">
			<hr>
			<a><b>Hinzufügen</b></a><br/><br/>
			<table width="100%" border="0" cellspacing="0" cellpadding="5" valign="top">
				<tr>
					<td	onclick="showSeamarkAdd('true')"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer">Seezeichen
					</td>
					<td>
						<IMG src="resources/action/go-next.png" width="16" height="16" align="right" border="0"/>
					</td>
				</tr>
				<tr>
					<td	onclick="showLandmarkAdd('true')"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer">Leuchtfeuer
					</td>
					<td>
						<IMG src="resources/action/go-next.png" width="16" height="16" align="right" border="0"/>
					</td>
				</tr>
				<tr>
					<td	onclick="showHarbourAdd('true')"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer">Hafenanlage
					</td>
					<td>
						<IMG src="./resources/action/go-next.png" width="16" height="16" align="right" border="0"/>
					</td>
				</tr>
			</table>
		</div>
		<div id="map" style="position:absolute; bottom:0px; right:0px;"></div>
		<div style="position:absolute; bottom:50px; left:3%;">
			Version 0.0.91.3
		</div>
		<div style="position:absolute; bottom:10px; left:4%;">
			<img src="../resources/icons/somerights20.png" title="This work is licensed under the Creative Commons Attribution-ShareAlike 2.0 License" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
		</div>
		<!--Add Seamark-Data-Dialog-->
		<div id="add_seamark_dialog" class="dialog" style="position:absolute; top:50px; left:15%; width:300px; height:620px; visibility:hidden;">
			<?php include ("./dialogs/add_seamark.php"); ?>
		</div>
		<!--Add Landmark-Data-Dialog-->
		<div id="add_landmark_dialog" class="dialog" style="position:absolute; top:150px; left:15%; width:300px; height:300px; visibility:hidden;">
			<?php include ("./dialogs/add_light.php"); ?>
		</div>
		<!--Add Harbour-Data-Dialog-->
		<div id="add_harbour_dialog" class="dialog" style="position:absolute; top:150px; left:15%; width:300px; height:300px; visibility:hidden;">
			<?php include ("./dialogs/add_harbour.php"); ?>
		</div>
		<!--Position-Dialog-->
		<div id="position_dialog" class="dialog" style="position:absolute; top:25px; left:20%; width:300px; height:180px; visibility:hidden;">
			<?php include ("./dialogs/new_position.php"); ?>
		</div>
		<!--Load Data Wait-Dialog-->
		<div id="loading" class="infobox" style="position:absolute; top:50%; left:50%; width:250px; height:30px; visibility:hidden;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;Daten werden geladen.
		</div>
		<!--Create Changeset Wait-Dialog-->
		<div id="creating" class="infobox" style="position:absolute; top:50%; left:50%; width:250px; height:30px; visibility:hidden;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;Changeset wird erzeugt.
		</div>
		<!--Save Data Wait-Dialog-->
		<div id="saving" class="infobox" style="position:absolute; top:50%; left:50%; width:300px; height:30px; visibility:hidden;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;Daten werden gespeichert.
		</div>
	</body>
</html>
