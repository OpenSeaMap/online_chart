<?php
	include("../../classes/Translation.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?=$t->tr("editHarbour")?></title>
		<meta name="AUTHOR" content="Olaf Hannemann" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<meta http-equiv="content-language" content="<?=$t->getCurrentLanguage()?>" />
		<link rel="SHORTCUT ICON" href="../../resources/icons/OpenSeaMapLogo_16.png"/>
		<link rel="stylesheet" type="text/css" href="../map-edit.css">
		<script type="text/javascript" src="../javascript/utilities.js"></script>
		<script type="text/javascript">

			// Global Variables
			var _mode;
			var _saving = false;

			function init() {
			  _mode = getArgument("mode");
			}

			function save() {
				this.close();
				opener.window.editHarbourOk();
			}

			function cancel() {
				this.close();
			}

			function onClosing() {
				if (_mode == "create" || _mode == "move") {
					opener.window.clearMoving();
					//opener.window.readOsmXml();
				} else {
					opener.window.onEditDialogCancel(_id);
				}
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

		</script>
	</head>

	<body onload=init(); onUnload="if (!_saving) onClosing();">
		<div id="headerAdd" style="position:absolute; top:0px; left:5px;"><h2>Hafen hinzufügen</h2></div>
		<div id="titleCategory" style="position:absolute; top:80px; left:7px;"><?=$t->tr("harbourCategory")?>:</div>
		<div id="boxCategory" style="position:absolute; top:80px; left:165px;">
			<select id="comboCategory" onChange="seamarkChanged()">
				<option value="unspecified"/><?=$t->tr("comboUnknown")?>
				<option selected value="marina"/>Marina
			</select>
		</div>
		<div style="position:absolute; top:120px; left:7px;"><?=$t->tr("harbourName")?></div>
		<div style="position:absolute; top:117px; left:165px;">
			<input type="text" id="inputName" align="left"/>
		</div>
		<div style="position:absolute; bottom:20px; right:10px;">
			<input type="button" id="buttonSave" value='<?=$t->tr("save")?>' onclick="save()">
			&nbsp;&nbsp;
			<input type="button" id="buttonCancel" value='<?=$t->tr("cancel")?>' onclick="cancel()">
			&nbsp;&nbsp;
		</div>
	</body>
</html>