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
		<meta http-equiv="content-language" content="<?=$t->getCurrentLanguage()?>" />
		<link rel="stylesheet" type="text/css" href="map-edit.css">
		<script type="text/javascript" src="./javascript/openlayers/OpenLayers.js"></script>
		<script type="text/javascript" src="./javascript/OpenStreetMap.js"></script>
		<script type="text/javascript" src="./javascript/prototype.js"></script>
		<script type="text/javascript" src="./javascript/map_utils.js"></script>
		<script type="text/javascript" src="./javascript/utilities.js"></script>
		<script type="text/javascript">

			//global variables
			var map;
			var layer_mapnik;
			var layer_tah;
			var layer_markers;
			var _Request;					//AJAX requests
			var _ZoomOld = "1";				//Previus zoom level
			var _Loaded = false;			//Map data is initially loaded
			var _Saving = false;			//Saving data in progress
			var _ChangeSetId = "-1";		//OSM-Changeset ID
			var _NodeId = "-1";				//OSM-Node ID
			var _Comment = null;			//Comment for Changeset
			var _Version = null;			//Version of the node
			var _xmlOsm = null;				//XML Data read from OSM database
			var _xmlNode = null;			//XML-Data for node creation
			var _userName = null;			//OSM-Username of the user
			var _userPassword = null;		//OSM-Password of the user
			var controls;					//OpenLayer-Controls
			var _ToDo = null;				//actually selected action
			var _Moving = false;			//needed for cursor and first fixing
			var click;						//click-event
			var seamarkType;				//seamarks
			var arrayMarker = new Array();	//Array of displayed Markers
			var arrayNodes = new Array();	//Array of available Nodes

			// position and zoomlevel (will be overriden with permalink parameters)
			var lon = 12.0915;
			var lat = 54.1878;
			var zoom = 16;

			function init() {
				// Set current language for internationalization
				OpenLayers.Lang.setCode("<?= $t->getCurrentLanguage() ?>");
				document.getElementById("selectLanguage").value = "<?= $t->getCurrentLanguage() ?>";
				// look for existing cookies
				if (document.cookie != "")  {
					var user = getCookie("user");
					var pass = getCookie("pass");
					if (user != "-1" && pass != "-1") {
						document.getElementById('loginUsername').value = user;
						document.getElementById('loginPassword').value = pass;
						loginUser_login();
					}
					var buffZoom = parseInt(getCookie("zoom"));
					var buffLat = parseFloat(getCookie("lat"));
					var buffLon = parseFloat(getCookie("lon"));
					if (buffZoom != -1) {
						zoom = buffZoom;
					}
					if (buffLat != -1 && buffLon != -1) {
						lat = buffLat;
						lon = buffLon;
					}
					var lang = getCookie("lang")
					if (lang != -1 && lang != "<?= $t->getCurrentLanguage() ?>") {
						document.getElementById("selectLanguage").value = lang;
						onLanguageChanged();
					}
				}
				// Display map
				drawmap();
			}

			function closing() {
				
				if (_Request != null) {
					// Abort running requests
					_Request.abort();
				}
			}

			// Language selection has been changed
			function onLanguageChanged() {
				var lang = document.getElementById("selectLanguage").value;
				window.location.href = "./map_edit.php?lang=" + lang;
				setCookie("lang", lang); 
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

			// Draw the map
			function drawmap() {
				map = new OpenLayers.Map('map', {
					projection: projMerc,
					displayProjection: proj4326,
					eventListeners: {
						"moveend": mapEventMove,
						"zoomend": mapEventZoom
					},
					controls: [
						new OpenLayers.Control.Permalink(),
						new OpenLayers.Control.Navigation(),
						new OpenLayers.Control.LayerSwitcher(),
						new OpenLayers.Control.MousePosition(),
						new OpenLayers.Control.ScaleLine({topOutUnits : "nmi", bottomOutUnits: "km", topInUnits: 'nmi', bottomInUnits: 'km', maxWidth: '40'}),
						new OpenLayers.Control.OverviewMap(),
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
				//layer_seamap = new OpenLayers.Layer.TMS("Seezeichen", "http://tiles.openseamap.org/seamark/",
				//{ numZoomLevels: 18, type: 'png', getURL: getTileURL, isBaseLayer: false, displayOutsideMaxExtent: true});
				// markers
				layer_markers = new OpenLayers.Layer.Markers("Address",
				{ projection: new OpenLayers.Projection("EPSG:4326"), visibility: true, displayInLayerSwitcher: false });
				// click events
				click = new OpenLayers.Control.Click();

				map.addLayers([layer_mapnik, layer_tah, layer_markers]);
				map.addControl(click);
				if (!map.getCenter()) {
					jumpTo(lon, lat, zoom);
				}
			}

			// Map event listener
			function mapEventMove(event) {
				if (map.getZoom() >= 15 &&  _Loaded) {
					updateSeamarks();
				}
				setCookie("lat", y2lat(map.getCenter().lat).toFixed(5));
				setCookie("lon", x2lon(map.getCenter().lon).toFixed(5));
			}

			// Map event listener
			function mapEventZoom(event) {
				var zoomLevel = map.getZoom();
				if (zoomLevel <= 15) {
					mapHideMarker();
					document.getElementById("buttonReload").disabled = true;
				} else {
					document.getElementById("buttonReload").disabled = false;
					mapShowMarker();
				}
				_ZoomOld = zoomLevel;
				setCookie("zoom", zoomLevel);
			}

			function mapShowMarker() {
				showInfoDialog(false);
				if (_ZoomOld <= 15) {
					updateSeamarks();
				}
			}

			function mapHideMarker() {
				layer_markers.clearMarkers();
				_NodeId = "-1";
				_Loaded = false;
				showInfoDialog(true, "<?=$t->tr('zoomToSmall')?>");
				document.getElementById("loading").style.visibility = 'hidden';
			}
			
			// add a marker on the map
			function addMarker(id, popupText) {
				var pos = new OpenLayers.LonLat(lon, lat);
				pos.transform(proj4326, projMerc);
				var feature = new OpenLayers.Feature(layer_markers, pos);
				var size = new OpenLayers.Size(32,32);
				var offset = new OpenLayers.Pixel(-16, -16);
				var icon = new OpenLayers.Icon('./resources/action/circle_blue.png', size, offset);

				feature.closeBox = true;
				feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {minSize: new OpenLayers.Size(260, 100) } );
				feature.data.popupContentHTML = popupText;
				feature.data.overflow = "hidden";

				arrayMarker[id] = new OpenLayers.Marker(pos, icon.clone());
				arrayMarker[id].feature = feature;

				markerClick = function(evt) {
					if (_ToDo != "add" && _ToDo != "move") {
					if (this.popup == null) {
						this.popup = this.createPopup(this.closeBox);
						map.addPopup(this.popup);
						this.popup.show();
					} else {
						this.popup.toggle();
					}
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
				xmlOSM += _xmlNode;
				xmlOSM += "</node>\n</osm>";
				// Sending content
				osmNode(action, xmlOSM);
			}

			function closeChangeSetOsm() {
				_ChangeSetId = "-1";
			}

			function showPositionDialog() {
				if (_ToDo == "add") {
					// reset old values
					document.getElementById("pos-lat").value = "0.0";
					document.getElementById("pos-lon").value = "0.0";
				} else {
					document.getElementById("pos-lat").value = lat.toFixed(5);
					document.getElementById("pos-lon").value = lon.toFixed(5);
				}
				//show dialog
				document.getElementById("position_dialog").style.visibility = "visible";
				// activate click event for entering a new position
				click.activate();
				// set cursor to crosshair style
				map.div.style.cursor="crosshair";
				// remeber that we are in moving mode
			}

			
			function clickSeamarkMap() {
				// remove existing temp marker
				if (_Moving) {
					//FIXME Dirty workaround for not getting a defined state of marker creation
					layer_markers.removeMarker(arrayMarker["2"]);
				}
				// display new coordinates
				document.getElementById("pos-lat").value = lat.toFixed(5);
				document.getElementById("pos-lon").value = lon.toFixed(5);
				// display temporary marker for orientation
				addMarker("2", "");
				arrayMarker["2"].setUrl('./resources/action/circle_red.png');
				//FIXME Dirty workaround for not getting a defined state of marker creation
				_Moving = true;
			}

			function onPositionDialogCancel() {
				// hide position dialog
				document.getElementById("position_dialog").style.visibility = "collapse";
				// disable click event
				map.div.style.cursor="default";
				click.deactivate();
				_Moving = false;
				_ToDo = null;
				// remove existing temp marker
				if (arrayMarker["2"] != null) {
					layer_markers.removeMarker(arrayMarker["2"]);
				}
				arrayMarker[_NodeId].setUrl('./resources/action/circle_blue.png');
			}

			function onEditDialogCancel(id) {
				arrayMarker[id].setUrl('./resources/action/circle_blue.png');
				_NodeId = "-1"
				_ToDo = null;
			}

			function addSeamark(seamark) {
				_ToDo = "add";
				showPositionDialog();
				document.getElementById("add_seamark_dialog").style.visibility = "collapse";
				// set the seamark type
				seamarkType = seamark;
				// remember what we are doing
			}

			function addSeamarkPosOk(latValue, lonValue) {
				lon = parseFloat(lonValue);
				lat = parseFloat(latValue);
				if (_NodeId != "-1") {
					arrayMarker[_NodeId].setUrl('./resources/action/circle_blue.png');
				}
				// remove existing temp marker
				if (arrayMarker["2"] != 'undefined') {
					layer_markers.removeMarker(arrayMarker["2"]);
				}
				_NodeId = "1";
				addMarker(_NodeId, "");
				arrayMarker[_NodeId].setUrl('./resources/action/circle_red.png');
				addSeamarkEdit();
			}

			function addSeamarkEdit() {
				editWindow = window.open("./dialogs/edit_seamark.php" + "?mode=create&type=" + seamarkType + "&lang=<?=$t->getCurrentLanguage()?>", "Bearbeiten", "width=630, height=420, resizable=yes");
 				editWindow.focus();
			}

			// Editing of the Seamark finished with OK
			function editSeamarkOk(xmlTags, todo) {
				_xmlNode = xmlTags;
				_ToDo = todo;
				_Saving = true;
				_Moving = false;
				if (!_userName) {
					alert("<?=$t->tr("logged_out_save")?>");
					loginUser();
				} else {
					document.getElementById('send_dialog').style.visibility = 'visible';
					document.getElementById('sendComment').focus();
				}
			}

			function editSeamarkEdit(id, version, pos_lat, pos_lon) {
				if (_NodeId != "-1") {
					arrayMarker[_NodeId].setUrl('./resources/action/circle_blue.png');
				}
				_Version = version;
				_NodeId = id;
				lat = pos_lat;
				lon = pos_lon;

				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				arrayMarker[id].setUrl('./resources/action/circle_red.png');
				editWindow = window.open('./dialogs/edit_seamark.php?mode=update&id=' + id + "&version=" + version + "&lang=<?=$t->getCurrentLanguage()?>" , "Bearbeiten", "width=630, height=420, resizable=yes");
 				editWindow.focus();
			}

			function moveSeamarkEdit(id, version, pos_lat, pos_lon) {
				lat = pos_lat;
				lon = pos_lon;
				if (_NodeId != "-1") {
					arrayMarker[_NodeId].setUrl('./resources/action/circle_blue.png');
				}
				_NodeId = id;
				_Version = version;
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				arrayMarker[id].setUrl('./resources/action/circle_yellow.png');
				// remember what we are doing
				_ToDo = "move";
				showPositionDialog()
			}

			function moveSeamarkOk(pos_lat, pos_lon) {
				lat = parseFloat(pos_lat);
				lon = parseFloat(pos_lon);
				// remove existing temp marker
				if (arrayMarker["2"] != 'undefined') {
					layer_markers.removeMarker(arrayMarker["2"]);
				}
				// set popup text for the new marker
				var popupText = "ID = " + _NodeId;
				popupText += " - Lat = " + lat;
				popupText += " - Lon = " + lon;
				popupText += " - Version = " + _Version;
				// add marker at the new position
				addMarker(_NodeId, popupText);
				arrayMarker[_NodeId].setUrl('./resources/action/circle_red.png');
				moveSeamarkSave();
			}

			function moveSeamarkSave() {
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				editWindow = window.open('./dialogs/edit_seamark.php?mode=move&id=' + _NodeId + "&version=" + _Version + "&lang=<?=$t->getCurrentLanguage()?>", "Bearbeiten", "width=630, height=420, resizable=yes");
 				editWindow.focus();
			}

			function deleteSeamarkEdit(id, version) {
				if (_NodeId != "-1") {
					arrayMarker[_NodeId].setUrl('./resources/action/circle_blue.png');
				}
				_NodeId = id;
				_Version = version;
				if (arrayMarker[id].feature.popup != null) {
					arrayMarker[id].feature.popup.hide();
				}
				arrayMarker[id].setUrl('./resources/action/delete.png');
				editWindow = window.open('./dialogs/edit_seamark.php?mode=delete&id=' + _NodeId + "&version=" + version + "&lang=<?=$t->getCurrentLanguage()?>", "Löschen", "width=380, height=420, resizable=yes");
 				editWindow.focus();
			}

			// Entering a new position finished
			function positionOk(latValue, lonValue) {
				if (latValue != lat.toFixed(5) || lonValue != lon.toFixed(5)) {
					if (!_Moving) {
						// set actual position as center
						jumpTo( parseFloat(lonValue),  parseFloat(latValue), map.getZoom());
						addMarker("2", "");
						arrayMarker["2"].setUrl('./resources/action/circle_red.png');
					}
				}
				switch (_ToDo) {
					case "add":
						addSeamarkPosOk(latValue, lonValue);
						break;
					case "move":
						moveSeamarkOk(latValue, lonValue);
						break;
				}
				// nothing todo left
				_ToDo = null;
				// disable click event
				map.div.style.cursor="default";
				click.deactivate();
				// hide position dialog
				document.getElementById('position_dialog').style.visibility = 'hidden';
			}

			// Open login window
			function loginUser() {
				document.getElementById('login_dialog').style.visibility = 'visible';
				document.getElementById('loginUsername').focus();
			}

			// Logout user and close changeset
			function logoutUser() {
				// close existing changeset
				if (_ChangeSetId >= 1) {
					//osmChangeSet("close", "void");
				}
				// delete user data
				_userName = null;
				_userPassword = null;
				// show login screen on the sidebar
				document.getElementById('login').style.visibility = 'visible';
				document.getElementById('logout').style.visibility = 'hidden';
				document.getElementById('loggedInName').style.visibility = 'hidden';
			}

			// Get user name and password from login dialog
			function loginUser_login() {
				_userName = document.getElementById('loginUsername').value;
				_userPassword = document.getElementById('loginPassword').value;
				setCookie("user", _userName);
				setCookie("pass", _userPassword);
				document.getElementById('login').style.visibility = 'hidden';
				document.getElementById('logout').style.visibility = 'visible';
				document.getElementById('loggedInName').style.visibility = 'visible';
				document.getElementById('login_dialog').style.visibility = 'hidden';
				document.getElementById('loggedInName').innerHTML=""+ _userName +"";
				if (_Saving) {
					document.getElementById('send_dialog').style.visibility = 'visible';
					document.getElementById('sendComment').focus();
				}
			}

			function loginUser_cancel() {
				document.getElementById('login_dialog').style.visibility = 'hidden';
				if (_Saving) {
					_Saving = false;
					_ToDo = null;
					readOsmXml();
				}
			}
			
			function sendingOk() {
				_Comment = document.getElementById('sendComment').value;
				if (_Comment == "") {
					alert("<?=$t->tr("enterComment")?>");
					return;
				}
				if (_ChangeSetId == "-1") {
					osmChangeSet("create", _ToDo);
				} else {
					sendNodeOsm(_ToDo);
				}
				document.getElementById('send_dialog').style.visibility = 'hidden';
			}

			// Dialogs----------------------------------------------------------------------------------------------------------------
			function showSeamarkAdd(visible) {
				if (visible) {
					document.getElementById('add_seamark_dialog').style.visibility = 'visible';
					document.getElementById('add_landmark_dialog').style.visibility = 'hidden';
					document.getElementById('add_harbour_dialog').style.visibility = 'hidden';
				} else {
					document.getElementById('add_seamark_dialog').style.visibility = 'hidden';
				}
			}

			function showLandmarkAdd(visible) {
				if (visible) {
					document.getElementById('add_landmark_dialog').style.visibility = 'visible';
					document.getElementById('add_seamark_dialog').style.visibility = 'hidden';
					document.getElementById('add_harbour_dialog').style.visibility = 'hidden';
				} else {
					document.getElementById('add_landmark_dialog').style.visibility = 'hidden';
				}
			}

			function showHarbourAdd(visible) {
				if (visible) {
					document.getElementById('add_harbour_dialog').style.visibility = 'visible';
					document.getElementById('add_seamark_dialog').style.visibility = 'hidden';
					document.getElementById('add_landmark_dialog').style.visibility = 'hidden';
				} else {
					document.getElementById('add_harbour_dialog').style.visibility = 'hidden';
				}
			}

			function showAboutDialog(visible) {
				if (visible) {
					document.getElementById('about_dialog').style.visibility = 'visible';
				} else {
					document.getElementById('about_dialog').style.visibility = 'hidden';
				}
			}
			
			function showInfoDialog(visible, text) {
				if(typeof text == "undefined"){
					text = " - - -";
				}
				if (visible) {
					document.getElementById('info_dialog').style.visibility = 'visible';
					document.getElementById('info_dialog').innerHTML=""+ text +"";
				} else {
					document.getElementById('info_dialog').style.visibility = 'hidden';
				}
			}
			
			// OSM-Api----------------------------------------------------------------------------------------------------------------
			function updateNode() {
				// FIXME: it is not necessary to reload all nodes. The updated one should be enough.
				updateSeamarks();
			}

			function osmChangeSet(action, todo) {
				var url = './api/changeset.php';
				var params = new Object();
				var dialog;

				params["action"] = action;
				params["id"] = _ChangeSetId;
				params["comment"] = _Comment;
				params["userName"] = _userName;
				params["userPassword"] = _userPassword;

				if (action = "create") {
					dialog = "creating";
				} else {
					dialog = "closing";
				}
				
				document.getElementById(dialog).style.visibility = "visible";

				new Ajax.Request(url, {
					method: 'get',
					parameters : params,
					onSuccess: function(transport) {
						var response = transport.responseText;
						if (action = "create") {
							var args = response.split(":");
							if (args[0] != "Error") {
								setChangeSetId(response);
								//alert(_ChangeSetId + " : " + todo);
								sendNodeOsm(todo);
								document.getElementById(dialog).style.visibility = "collapse";
								return "0";
							} else {
								document.getElementById(dialog).style.visibility = "collapse";
								switch (trim(args[1])) {
									case "401":
										alert("<?=$t->tr('send401')?>");
										logoutUser();
										loginUser();
										break;
									case "404":
										alert("<?=$t->tr('send404')?>");
										loginUser_cancel();
										break;
									default:
										alert("Erzeugen des Changesets Fehlgeschlagen: " + response);
										loginUser_cancel();
										break;
								}
								setChangeSetId("-1");
								return "-1";
							}
						}
					},
					onFailure: function() {
						document.getElementById(dialog).style.visibility = "collapse";
						alert("damm");
						return "-1";
					},
					onException: function(request, exception) {
						document.getElementById(dialog).style.visibility = "collapse";
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
				params["name"] = _userName;
				params["password"] = _userPassword;
				params["data"] = data;

				document.getElementById("saving").style.visibility = "visible";

				new Ajax.Request(url, {
					method: "get",
					parameters : params,
					onSuccess: function(transport) {
						var response = transport.responseText;
						switch (action) {
							case "create":
								_NodeId = trim(response);
							case "move":
							case "update":
							case "delete":
								_Loaded = false;
								updateNode();
								break;
							case "get":
								alert("Node= " + response);
								break;
						}
						document.getElementById("saving").style.visibility = "collapse";
						_Saving = false;
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
				//var zoomLevel = map.getZoom();
				if (map.getZoom() > 15) {
					if (_Loaded) {
						showInfoDialog(true, "<img src=\"resources/action/wait.gif\" width=\"22\" height=\"22\" /> &nbsp;&nbsp;<?=$t->tr('loading')?>");
					} else {
						document.getElementById("loading").style.visibility = "visible";
					}
					document.getElementById("selectLanguage").disabled = true;
					document.getElementById("buttonReload").disabled = true;
					var url = './api/map.php';
					var params = new Object();
					var bounds = map.getExtent().toArray();
					params["n"] = y2lat(bounds[3]);
					params["s"] = y2lat(bounds[1]);
					params["w"] = x2lon(bounds[0]);
					params["e"] = x2lon(bounds[2]);
					if (_Request != null) {
						// Abort running requests
						_Request.abort();
					}
					//alert("lade");
					_Request = new Ajax.Request(url, {
						method: 'get',
						parameters: params,
						onSuccess: function(transport) {
							var response = transport.responseText;
							if (map.getZoom() > 15) {
								_xmlOsm = response;
								if (readOsmXml() >= 0) {
									document.getElementById("loading").style.visibility = "collapse";
									showInfoDialog(false);
									document.getElementById("selectLanguage").disabled = false;
									document.getElementById("buttonReload").disabled = false;
									if (_NodeId != "-1" && _NodeId != "1" && !_Moving) {
										arrayMarker[_NodeId].setUrl('./resources/action/circle_green.png');
									}
									_Loaded = true;
								} else {
									_Loaded = false;
								}
							}
							return 0;
						},
						onFailure: function() {
							alert("Error while loading data");
							document.getElementById("loading").style.visibility = "collapse";
							document.getElementById("selectLanguage").disabled = false;
							document.getElementById("buttonReload").disabled = false;
							_Loaded = false;
							return -1;
						},
						onException: function(request, exception) {
							/*alert("Error (prototype): " + exception);
							document.getElementById("loading").style.visibility = "collapse";
							document.getElementById("selectLanguage").disabled = false;
							document.getElementById("buttonReload").disabled = false;
							_Loaded = false;*/
							return -1;
						}
					});
				} else {
					mapHideMarker();
				}
			}

			function readOsmXml() {

				var xmlData = _xmlOsm;
				var xmlObject;
				var show = false;

				// Browserweiche für den DOMParser:
				// Mozilla and Netscape browsers
				if (document.implementation.createDocument) {
					try {
						xmlParser = new DOMParser();
						xmlObject = xmlParser.parseFromString(xmlData, "text/xml");
 					} catch(e) {
						alert("Error (dom): " + e);
						return -1;
					}
				 // MSIE
				} else if (window.ActiveXObject) {
					try {
						xmlObject = new ActiveXObject("Microsoft.XMLDOM")
						xmlObject.async="false"
						xmlObject.loadXML(xmlData)
					} catch(e) {
						alert("Error (msie-dom): " + e);
						return -1;
					}
				}
				try {
					var root = xmlObject.getElementsByTagName('osm')[0];
					var items = root.getElementsByTagName("node");
				} catch(e) {
					//alert("Error (root): " + e);
					return -1;
				}
				if (map.getZoom() > 15) {
					layer_markers.clearMarkers();
					if (_Moving) {
						addMarker("2", "");
						arrayMarker["2"].setUrl('./resources/action/circle_red.png');
					} else {
						_ToDo = null;
					}
					var buffLat = lat;
					var buffLon = lon;
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
							arrayNodes[id] = "";

							// Getting the tags (key value pairs)
							var tags = item.getElementsByTagName("tag");
							for (var n=0; n < tags.length; ++n) {
								var tag = tags[n];
								var key = tag.getAttribute("k");
								if (key == "seamark") {
									show = true;
								}
								var val = tag.getAttribute("v");
								/*if (key == "seamark:type") {
									popupText += "<br/>seamark = " + val;
								}*/
								arrayNodes[id] += key + "," + val + "|";
							}
							//if (show) {
								var popupText = "<table border=\"0\" cellpadding=\"1\">"
								popupText += "<tr><td>ID</td><td> = <t/d><td>" + id + "</td></tr>";
								popupText += "<tr><td>Version</td><td> = <t/d><td>" + version + "</td></tr>";
								popupText += "<tr><td>Lat</td><td> = <t/d><td>" + lat.toFixed(5) + "</td></tr>";
								popupText += "<tr><td>Lon</td><td> = <t/d><td>" + lon.toFixed(5) + "</td></tr></table>";
								popupText += "<br/><br/>";
								popupText += "<a href='http://api06.dev.openstreetmap.org/browse/node/" + id + "/history' target='blank'><?=$t->tr("historyNode")?></a>";
								popupText += "<br/>";
								popupText += "<br/> <br/>";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("edit")?>\" onclick=\"editSeamarkEdit(" + id + "," + version + "," + lat + "," + lon + ")\">&nbsp;&nbsp;";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("move")?>\"onclick=\"moveSeamarkEdit(" + id + "," + version + "," + lat + "," + lon + ")\">&nbsp;&nbsp;";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("delete")?>\"onclick=\"deleteSeamarkEdit(" + id + "," + version + ")\">";
								addMarker(id, popupText);
								show = false;
							//}
						}
					}
					if (_Moving) {
						arrayMarker[_NodeId].setUrl('./resources/action/circle_yellow.png');
					}
					lat = buffLat;
					lon = buffLon;
				}
				return 0;
			}

			// Some api stuff---------------------------------------------------------------------------------------------------------
			function getChangeSetId() {
				return _ChangeSetId;
			}

			function setChangeSetId(id) {
				_ChangeSetId = trim(id);
			}

			function getComment() {
				return _Comment;
			}

			function setComment(value) {
				_Comment = trim(value);
			}

			function getKeys(id) {
				return arrayNodes[id];
			}

			// Some little helpers----------------------------------------------------------------------------------------------------
			// Abort an AJAX request
			Ajax.Request.prototype.abort = function() {
				// prevent and state change callbacks from being issued
				this.transport.onreadystatechange = Prototype.emptyFunction;
				// abort the XHR
				this.transport.abort();
				// update the request counter
				Ajax.activeRequestCount--;
				// just to be sure ;-)
				if (Ajax.activeRequestCount < 0) {
    				Ajax.activeRequestCount = 0;
				}
			};

		</script>
	</head>
	<body onload=init(); onUnload=closing();>
		<!--Sidebar ****************************************************************************************************************** -->
		<div id="head" class="sidebar" style="position:absolute; top:2px; left:0px;">
			<a><b><?=$t->tr("online_editor")?></b></a>
		</div>
		<div id="language" class="sidebar" style="position:absolute; top:30px; left:0px;">
			<hr>
			<?=$t->tr("language")?>:&nbsp;
			<select id="selectLanguage" onChange="onLanguageChanged()">
				<option value="en"/>English
				<option value="de"/>Deutsch
			</select>
		</div>
		<div id="login" class="sidebar" style="position:absolute; top:70px; left:0px;">
			<hr>
			<p><?=$t->tr("logged_out")?></p>
			<input type="button" value='<?=$t->tr("login")?>' onclick="loginUser()">
		</div>
		<div id="logout" class="sidebar" style="position:absolute; top:70px; left:0px; visibility:hidden;" >
			<hr>
			<p><?=$t->tr("logged_in")?></p><br/><br/>
			<input type="button" value='<?=$t->tr("logout")?>' onclick="logoutUser()" >
		</div>
		<div id="loggedInName" style="position:absolute; top:132px; left:10px; visibility:hidden;">- - -</div>
		<div style="position:absolute; top:185px; left:11.5%;"><a href="http://sourceforge.net/apps/mediawiki/openseamap/index.php?title=De:Online-Editor" target="blank"><?=$t->tr("help")?></a></div>
		<div id="data" class="sidebar" style="position:absolute; top:200px; left:0px;">
			<hr>
			<b><?=$t->tr("data")?></b>
			<br/><br/>
			<select id="pos-iala">
				<option selected value="A" disabled = "true"/>IALA - A
			</select>&nbsp; &nbsp;
			<input type="button" id="buttonReload" value='<?=$t->tr("reload")?>' onclick="updateSeamarks()">
		</div>
		<div style="position:absolute; top:295px; left:11.5%;"><a href="http://sourceforge.net/apps/mediawiki/openseamap/index.php?title=De:Online-Editor" target="blank"><?=$t->tr("help")?></a></div>
		<div id="action" class="sidebar" style="position:absolute; top:305px; left:0px;">
			<hr>
			<a><b><?=$t->tr("add")?></b></a><br/><br/>
			<table width="100%" border="0" cellspacing="0" cellpadding="5" valign="top">
				<tr>
					<td	onclick="showSeamarkAdd(true)"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer"><?=$t->tr("Seezeichen")?>
					</td>
					<td>
						<IMG src="resources/action/go-next.png" width="16" height="16" align="right" border="0"/>
					</td>
				</tr>
			</table>
		</div>
		<div id="data" class="sidebar" style="position:absolute; top:400px; left:0px;">
			<hr>
			<table width="100%" border="0" cellspacing="0" cellpadding="5" valign="top">
				<tr>
					<td	onclick="showAboutDialog(true)"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer"><?=$t->tr("about_editor")?>
					</td>
				</tr>
				<tr>
					<td	onclick="window.open('http://sourceforge.net/apps/mediawiki/openseamap/index.php?title=De:Online-Editor/edit');"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer"><?=$t->tr("help")?>
					</td>
				</tr>
				<tr>
					<td	onclick="window.location.href='../index.php'"
						onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
						onmouseout="this.parentNode.style.backgroundColor = 'white';"
						style="cursor:pointer"><?=$t->tr("Startseite")?>
					</td>
				</tr>
			</table>
			<hr>
		</div>
		<!--Map ********************************************************************************************************************** -->
		<div id="map" style="position:absolute; bottom:0px; right:0px;"></div>
		<div style="position:absolute; bottom:50px; left:3%;">
			Version 0.0.97.1
		</div>
		<div style="position:absolute; bottom:10px; left:4%;">
			<img src="../resources/icons/somerights20.png" title="This work is licensed under the Creative Commons Attribution-ShareAlike 2.0 License" onClick="window.open('http://creativecommons.org/licenses/by-sa/2.0')" />
		</div>
		<!--Sidebar dialogs ********************************************************************************************************** -->
		<!--Add Seamark-Data-Dialog-->
		<div id="add_seamark_dialog" class="dialog" style="position:absolute; top:50px; left:15%;">
			<?php include ("./dialogs/add_seamark.php"); ?>
		</div>
		<!--Add Landmark-Data-Dialog-->
		<div id="add_landmark_dialog" class="dialog" style="position:absolute; top:150px; left:15%; width:300px; height:300px">
			<?php include ("./dialogs/add_light.php"); ?>
		</div>
		<!--Add Harbour-Data-Dialog-->
		<div id="add_harbour_dialog" class="dialog" style="position:absolute; top:150px; left:15%; width:300px; height:300px;">
			<?php include ("./dialogs/add_harbour.php"); ?>
		</div>
		<!--Pop up dialogs  ********************************************************************************************************** -->
		<!--Position-Dialog-->
		<div id="position_dialog" class="dialog" style="position:absolute; top:25px; left:20%;">
			<?php include ("./dialogs/new_position.php"); ?>
		</div>
		<div id="login_dialog" class="dialog" style="position:absolute; top:40%; left:40%;">
			<?php include ("./dialogs/user_login.php"); ?>
		</div>
		<div id="send_dialog" class="dialog" style="position:absolute; top:45%; left:40%;">
			<?php include ("./dialogs/sending.php"); ?>
		</div>
		<div id="info_dialog" class="dialog" style="position:absolute; top:20px; right:40px;">
			 - - -
		</div>
		<div id="about_dialog" class="dialog" style="position:absolute; top:35%; left:50%;">
			<?php include ("./dialogs/about_editor.php"); ?>
		</div>
		<!--Status dialogs *********************************************************************************************************** -->
		<!--Load Data Wait-Dialog-->
		<div id="loading" class="infobox" style="position:absolute; top:50%; left:50%;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;<?=$t->tr("dataLoad")?>
		</div>
		<!--Create Changeset Wait-Dialog-->
		<div id="creating" class="infobox" style="position:absolute; top:50%; left:50%;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;<?=$t->tr("changesetCreate")?>
		</div>
		<!--Close Changeset Wait-Dialog-->
		<div id="closing" class="infobox" style="position:absolute; top:50%; left:50%;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;<?=$t->tr("changesetClose")?>
		</div>
		<!--Save Data Wait-Dialog-->
		<div id="saving" class="infobox" style="position:absolute; top:50%; left:50%;">
			<img src="resources/action/wait.gif" width="22" height="22" /> &nbsp;&nbsp;<?=$t->tr("dataSave")?>
		</div>
	</body>
</html>
