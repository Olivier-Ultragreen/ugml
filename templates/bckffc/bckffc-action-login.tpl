<div id="bckffc-login">
	<form id="bckffcForm" method="post" action="bckffc-login[EXT]">
		<input type="hidden" name="action" value="login"/>
		<fieldset class="ftext">
			<label for="login">identifiant</label>
			<input type="text" name="login" value="" id="login" class="mandatory" required/>
		</fieldset>
		<fieldset class="fpass">
			<label for="pwd">mot de passe</label>
			<input type="password" name="pwd" value="" id="pwd" class="mandatory" required/>
		</fieldset>
		<fieldset class="fsubpic">
			<label class="invisible">button</label><input class="button" type="submit" value="connexion"/>
		</fieldset>
	</form>
	<div class="error-msg">
		[error-msg]
	</div>			<!-- mustdo -->
</div>