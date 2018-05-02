<!--form class="actionGlobales Import" method="post" accept-charset="utf-8" enctype="multipart/form-data">
	<fieldset class="file_f">
		<input type="file" name="import" value="" id="import"/>
		<input type="hidden" name="action" value="import">
		<input type="submit" name="" value="importer"/>
	</fieldset>
</form-->
<form class="import" method="post" accept-charset="utf-8" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import">
	<fieldset>
		<label for="import">Choisir un fichier</label><input type="submit" name="" value="importer"/>
		<input type="file" name="import" value="" id="import"/>
	</fieldset>
</form>