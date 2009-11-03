<h3><?=$t->tr("seamarkSave")?></h3>
<table>
	<tr>
		<td valign="top">
			<?=$t->tr("comment")?>:&nbsp;
		</td>
		<td valign="top">
			<input type="text" id="sendComment" align="left" size="40" onkeydown="if (checkKeyReturn(event)) {sendingOk()}"/>
		</td>
	</tr>
</table>
<p align="right">
	<br>
	<input type="button" value='<?=$t->tr("ok")?>' onclick="sendingOk()">
	&nbsp;&nbsp;
	<input type="button" value='<?=$t->tr("cancel")?>' onclick="document.getElementById('send_dialog').style.visibility = 'hidden'; readOsmXml();">
	&nbsp;&nbsp;
</p>

