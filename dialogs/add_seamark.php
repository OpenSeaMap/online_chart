<table height="100%" width="100%" border="0" cellspacing="0" cellpadding="5" valign="top">
	<tr>
		<td>
			<IMG src="resources/lateral/Lateral_SafeWater.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('safe_water')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("safe_water")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="resources/lateral/Lateral_Green.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('starboard')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("lateral_starboard")?></td>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="resources/lateral/Lateral_Red.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('port')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("lateral_port")?></td>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="resources/lateral/Lateral_Pref_Starboard.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('preferred_channel_starboard')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("lateral_pref_starboard")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="resources/lateral/Lateral_Pref_Port.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('preferred_channel_port')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("lateral_pref_port")?></td>
		</td>
	</tr>
	<tr>
		<td	height="5" class="normal" colspan="2">
			<hr>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/cardinal/Cardinal_North.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('north')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("cardinal_north")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/cardinal/Cardinal_East.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('east')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("cardinal_east")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/cardinal/Cardinal_South.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('south')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("cardinal_south")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/cardinal/Cardinal_West.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('west')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("cardinal_west")?></td>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/cardinal/Cardinal_Single.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('isolated_danger')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("isolated_danger")?>
		</td>
	</tr>
	<tr>
		<td	height="5" class="normal" colspan="2">
			<hr>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/special_purpose/Special_Purpose.png" width="30" height="36" align="center" border="0"/>
		</td>
		<td	onclick="addSeamark('special_purpose')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("special_purpose")?>
		</td>
	</tr>
	<tr height="100%">
		<td>
		</td>
		<td align="right" valign="bottom" >
			<input type="button" value='<?=$t->tr("close")?>' onclick="showSeamarkAdd('false')" >
		</td>
	</tr>
</table>