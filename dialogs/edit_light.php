<table border="0" cellpadding="3">
	<tr>
		<td>
			Kennung
		</td>
		<td>
			<select  name="light_character" id="lightChar" onChange="onChangeLightCharacter()">
				<option value="unspecified"/>Unbekannt
			</select>
		</td>
	</tr>
	<tr>
		<td>
			Wiederkehr
		</td>
		<td>
			<input type="text" name="light_period" id="lightPeriod" size="4" style="text-align:right;" value="unknown"/>
			s
		</td>
	</tr>
</table>
<p align="right">
	<br>
	<input type="button" name="save_button_light" value="OK" onclick="saveLight()">
	&nbsp;&nbsp;
	<input type="button" name="cancel_button_light" value="Abbrechen" onclick="cancelLight()">
	&nbsp;&nbsp;
</p>