<form name="Login" action="">
	<h3><?=$t->tr("login")?></h3>
	<table>
		<tr>
			<td valign="top">
				<?=$t->tr("userName")?>:&nbsp;
			</td>
			<td valign="top">
				<input type="text" id="loginUsername" align="left"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?=$t->tr("password")?>:&nbsp;
			</td>
			<td valign="top">
				<input type="password" id="loginPassword" align="left" onkeydown="if (checkKeyReturn(event)) {loginUser_login()}"/>
			</td>
		</tr>
	</table>
	<p align="right">
		<br>
		<input type="button" value='<?=$t->tr("login")?>' onclick="loginUser_login()">
		&nbsp;&nbsp;
		<input type="button" value='<?=$t->tr("cancel")?>' onclick="document.getElementById('login_dialog').style.visibility = 'hidden'">
		&nbsp;&nbsp;
	</p>
</form>
