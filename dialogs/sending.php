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
		<title>OpenSeaMap: Übertragen der Daten</title>
		<script type="text/javascript">

			var _ChangeSetId = "-1";
			var _Comment = "";
			var _Action = "";
			
			function init() {
				_ChangeSetId = opener.window.getChangeSetId();
				_Comment = opener.window.getComment();
				_Action = getArgument("action")
				
				this.document.sendOSM.comment.value = _Comment;
			}
			
			function ok() {
				_Comment = this.document.sendOSM.comment.value;
				
				if (_Comment == "") {
					alert("<?=$t->tr("enterComment")?>");
					return;
				} else {
					opener.window.setComment(_Comment);
				}
				if (_ChangeSetId == "-1") {
					opener.window.osmChangeSet("create", _Action);
				} else {
					opener.window.sendNodeOsm(_Action);
				}
				this.close();
			}

			function cancel() {
				opener.window.readOsmXml();
				this.close();
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
	<body onload=init();>
		<form name="sendOSM" action="">
			<h3><?=$t->tr("seamarkSave")?></h3>
			<table>
				<tr>
					<td valign="top">
						<?=$t->tr("comment")?>:&nbsp;
					</td>
					<td valign="top">
						<input type="text" name="comment" align="left" size="40"/>
					</td>
				</tr>
			</table>
			<p align="right">
				<br>
				<input type="button" value='<?=$t->tr("ok")?>' onclick="ok()">
				&nbsp;&nbsp;
				<input type="button" value='<?=$t->tr("cancel")?>' onclick="cancel()">
				&nbsp;&nbsp;
			</p>
		</form>
	</body>
</html>