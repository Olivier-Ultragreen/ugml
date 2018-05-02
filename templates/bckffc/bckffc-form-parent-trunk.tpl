<fieldset>
	<input id="[name]" type="hidden" name="[name]" value="[value]"/>
	<label>[alias]</label>
	<select name="[name]_display">
		[options]
	</select>
	<script>
	$(document).ready(function() {
		$('.depthLevel2').each(function(){
			var option = ' » ' + $(this).text();
			$(this).text(option);
		});
		$('.depthLevel3').each(function(){
			var option = ' »» ' + $(this).text();
			$(this).text(option);
		});
	});
	</script>
</fieldset>