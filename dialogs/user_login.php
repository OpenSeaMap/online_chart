<form name="Login" action="">
	<h3><?=$t->tr("login")?></h3>
	<table>
		<tr>
			<td valign="top">
				<?=$t->tr("userName")?>:&nbsp;
			</td>
			<td valign="top">
				<input type="text" id="loginUsername" align="left"/>&nbsp;&nbsp;
			</td>
			<td valign="top">
				<?=$t->tr("haveAccount")?>
			</td>
		</tr>
		<tr>
			<td valign="bottom">
				<?=$t->tr("password")?>:&nbsp;
			</td>
			<td valign="bottom">
				<input type="password" id="loginPassword" align="left" onkeydown="if (checkKeyReturn(event)) {loginUser_login()}"/>&nbsp;&nbsp;
			</td>
			<td valign="bottom">
				<a href="http://www.openstreetmap.org/user/new" target="blank"><?=$t->tr("createAccount")?></a></div>
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
