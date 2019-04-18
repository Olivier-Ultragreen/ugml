<div id="bckffcLogin">
	<form method="post" action="bckffc-login[EXT]" class="col col-12 col-lg-offset-4 col-lg-4">
		<fieldset class="layout full left">
			<input type="hidden" name="action" value="login"/>
			<h1 class="layout full left aligncenter">Identifiez-vous</h1>
			<fieldset class="layout full left">
				<label class="hidden">Identifiant</label>
				<input type="text" name="login" value="" class="layout full left mandatory" placeholder="Identifiant"/>
			</fieldset>
			<fieldset class="layout full left">
				<label class="hidden">Mot de passe</label>
				<input type="password" name="pwd" value="" class="layout full left mandatory" placeholder="Mot de passe"/>
			</fieldset>
			<fieldset class="layout full left aligncenter">
				<input class="button" type="submit" value="connexion"/>
			</fieldset>
		</fieldset>
	</form>
	<div class="error-msg">
	[error-msg]
	</div>			<!-- mustdo -->
</div>