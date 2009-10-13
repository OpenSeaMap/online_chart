<h3><?=$t->tr("positionSeamark")?></h3>
<form name="position" action="">
<table>
	<tr>
		<td valign="top">
			Lat:&nbsp;
		</td>
		<td valign="top">
			<input type="text" id="pos-lat" style="text-align:right;"/>
		</td>
	</tr>
	<tr>
		<td valign="top">
			Lon:&nbsp;
		</td>
		<td valign="top">
			<input type="text" id="pos-lon" style="text-align:right;" onkeydown="if (checkKeyReturn(event)) {positionOk(document.getElementById('pos-lat').value, document.getElementById('pos-lon').value);}"/>
		</td>
	</tr>
</table>
<p align="right">
	<input type="button" value='<?=$t->tr("ok")?>' onclick="positionOk(document.getElementById('pos-lat').value, document.getElementById('pos-lon').value);">
	&nbsp;&nbsp;
	<input type="button" value='<?=$t->tr("cancel")?>' onclick="onPositionDialogCancel()">
	&nbsp;&nbsp;
</p>
</form>