/******************************************************************************
 Javascript map_edit_utils
 author Olaf Hannemann
 license GPL V3
 version 0.0.1

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
 ******************************************************************************/

			// OSM-Api----------------------------------------------------------------------------------------------------------------
			function updateNode(id) {
				var url = "./api/get_node.php";
				var params = new Object();
				params["node_id"] = id;

				document.getElementById("loading").style.visibility = "visible";

					new Ajax.Request(url, {
					method: "get",
					parameters : params,
					onSuccess: function(transport) {
						var response = transport.responseText;
						alert("Node= " + response);
						layer_markers.removeMarker(arrayMarker[id]);
						readOsmXml(response);
						document.getElementById("loading").style.visibility = "hidden";
					},
					onFailure: function() {
						document.getElementById("loading").style.visibility = "hidden";
						alert("Error while sending data");
						return "-1";
					},
					onException: function(request, exception) {
						document.getElementById("loading").style.visibility = "hidden";
						alert("Error: " + exception + request);
						return "-1";
					}
				});
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
							//var args = response.split(":");
							if (trim(response) != "Couldn't authenticate you") {
								setChangeSetId(response);
								//alert(response);
								sendNodeOsm(todo);
								document.getElementById(dialog).style.visibility = "collapse";
								return "0";
							} else {
								document.getElementById(dialog).style.visibility = "collapse";
								alert("<?=$t->tr('send401')?>");
								loginUser_cancel();
								setChangeSetId("-1");
								readOsmXml();
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
								updateSeamarks();
								break;
						}
						document.getElementById("saving").style.visibility = "collapse";
						_Saving = false;
						showInfoDialog(true, "<?=$t->tr('helpSeamarkSaved')?>");
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
				if (map.getZoom() > 15) {
					if (_Loaded) {
						showInfoDialog(true, "<img src=\"resources/action/wait.gif\" width=\"22\" height=\"22\" /> &nbsp;&nbsp;<?=$t->tr('loading')?>");
					} else {
						document.getElementById("loading").style.visibility = "visible";
					}
					_Loading = true;
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
					_Request = new Ajax.Request(url, {
						method: 'get',
						parameters: params,
						onSuccess: function(transport) {
							var response = transport.responseText;
							if (map.getZoom() > 15) {
								_xmlOsm = trim(response);
								if (readOsmXml() >= 0) {
									document.getElementById("loading").style.visibility = 'hidden';
									document.getElementById("action").style.visibility = 'visible';
									showInfoDialog(false);
									if (_NodeId != "-1" && _NodeId != "1" && !_Moving) {
										arrayMarker[_NodeId].setUrl('./resources/action/circle_green.png');
									}
									_Loaded = true;
								} else {
									alert("<?=$t->tr('xmlLoadError')?>");
									showInfoDialog(true, "<?=$t->tr('noData')?>");
									_Loaded = false;
								}
								document.getElementById("loading").style.visibility = 'hidden';
								document.getElementById("selectLanguage").disabled = false;
								document.getElementById("buttonReload").disabled = false;
							}
							_Loading = false;
							return 0;
						},
						onFailure: function() {
							alert("Error while loading data");
							document.getElementById("loading").style.visibility = "collapse";
							document.getElementById("selectLanguage").disabled = false;
							document.getElementById("buttonReload").disabled = false;
							_Loading = false;
							_Loaded = false;
							return -1;
						},
						onException: function(request, exception) {
							/*alert("Error (prototype): " + exception);
							document.getElementById("loading").style.visibility = "collapse";
							document.getElementById("selectLanguage").disabled = false;
							document.getElementById("buttonReload").disabled = false;*/
							document.getElementById("selectLanguage").disabled = false;
							_Loaded = false;
							_Loading = false;
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
					var root = xmlObject.getElementsByTagName("osm")[0];
					var items = root.getElementsByTagName("node");
				} catch(e) {
					//alert("Error (root): "+ e);
					return -1;
				}
				if (map.getZoom() > 15) {
					clearMarker();
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
							var name = "- - -"
							for (var n=0; n < tags.length; ++n) {
								var tag = tags[n];
								var key = tag.getAttribute("k");
								if (key == "seamark" || key == "seamark:type") {
									show = true;
								}
								var val = tag.getAttribute("v");
								if (key == "seamark:name") {
									name= val;
								}
								arrayNodes[id] += key + "^" + val + "|";
							}
							if (show) {
								var popupText = "<table border=\"0\" cellpadding=\"1\">"
								popupText += "<tr><td>Name</td><td> = <t/d><td>" + name + "</td></tr>";
								popupText += "<tr><td>ID</td><td> = <t/d><td>" + id + "</td></tr>";
								popupText += "<tr><td>Version</td><td> = <t/d><td>" + version + "</td></tr>";
								popupText += "<tr><td>Lat</td><td> = <t/d><td>" + lat.toFixed(5) + "</td></tr>";
								popupText += "<tr><td>Lon</td><td> = <t/d><td>" + lon.toFixed(5) + "</td></tr></table>";
								popupText += "<br/><br/>";
								popupText += "<a href='http://www.openstreetmap.org/browse/node/" + id + "/history' target='blank'><?=$t->tr("historyNode")?></a>";
								popupText += "<br/>";
								popupText += "<br/> <br/>";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("edit")?>\" onclick=\"editSeamarkEdit(" + id + "," + version + "," + lat + "," + lon + ")\">&nbsp;&nbsp;";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("move")?>\"onclick=\"moveSeamarkEdit(" + id + "," + version + "," + lat + "," + lon + ")\">&nbsp;&nbsp;";
								popupText += "<input type=\"button\" value=\"<?=$t->tr("delete")?>\"onclick=\"deleteSeamarkEdit(" + id + "," + version + ")\">";
								addMarker(id, popupText);
								show = false;
							}
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