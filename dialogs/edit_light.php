<table border="0" cellpadding="3">
	<tr>
		<td>
			Kennung
		</td>
		<td>
			<select  name="light_character" id="lightChar">
				<option value="unspecified"/>Unbekannt- - - -
			</select>
		</td>
	</tr>
	<!--<tr>
		<td>
			Gruppe
		</td>
		<td>
			<select  name="light_group" id="lightGroup">
				<option value="unspecified"/>Unbekannt- - - -
				<option value="unspecified"/>None
			</select>
		</td>
	</tr>-->
	<tr>
		<td>
			Wiederkehr
		</td>
		<td>
			<input type="text" name="light_period" id="lightPeriod" size="10" align="left" value="unknown"/>
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