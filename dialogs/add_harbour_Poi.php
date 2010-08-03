<table height="100%" width="100%" border="0" cellspacing="0" cellpadding="5" valign="top">
	<tr>
		<td>
			<IMG src="./resources/places/harbour.png" width="32" height="32" align="center" border="0"/>
		</td>
		<td onclick="addElement('harbour', 'void')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("harbour")?>
		</td>
	</tr>
	<tr>
		<td	height="5" class="normal" colspan="2">
			<hr>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/places/harbour_master.png" width="28" height="32" align="center" border="0"/>
		</td>
		<td onclick="addElement('poi', 'harbour_master')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("harbour_master")?>
		</td>
	</tr>
	<tr>
		<td	height="5" class="normal" colspan="2">
			<hr>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/places/crane.png" width="32" height="32" align="center" border="0"/>
		</td>
		<td onclick="addSeamark('safe_water')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("crane")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/places/slipway.png" width="32" height="32" align="center" border="0"/>
		</td>
		<td onclick="addSeamark('safe_water')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("slipway")?>
		</td>
	</tr>
	<tr>
		<td	height="5" class="normal" colspan="2">
			<hr>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/places/fuel.png" width="32" height="32" align="center" border="0"/>
		</td>
		<td onclick="addSeamark('safe_water')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("safe_water")?>
		</td>
	</tr>
	<tr>
		<td>
			<IMG src="./resources/places/waste_disposal.png" width="32" height="32" align="center" border="0"/>
		</td>
		<td onclick="addSeamark('safe_water')"
			onmouseover="this.parentNode.style.backgroundColor = 'gainsboro';"
			onmouseout="this.parentNode.style.backgroundColor = 'white';"
			style="cursor:pointer"><?=$t->tr("waste_disposal")?>
		</td>
	</tr>
	<tr height="100%">
		<td>
		</td>
		<td align="right" valign="bottom" >
			<input type="button" value='<?=$t->tr("close")?>' onclick="showHarbourAdd(false)" >
		</td>
	</tr>
</table>