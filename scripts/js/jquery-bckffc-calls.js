$(document).ready(function() {
	// handling groupActionForm specific behaviour
	// defining the on click behaviour for single check boxes
	// aka fill the hidden form with the coma separated list of ids selected 
	$('input[name="id[]"]').on('click',function(){
		$('#groupActionForm input[name="ids"]').val('');
		$('input[name="id[]"]:checked').each(function(){
			$('#groupActionForm input[name="ids"]').val($('#groupActionForm input[name="ids"]').val() + ',' + $(this).val());
		});
		$('#groupActionForm input[name="ids"]').val($('#groupActionForm input[name="ids"]').val().substring(1));
	});
	// handling the select/unselect all
	$('.reverse').on('click',function(e){
		if($(this).is(':checked')) {
			$('.reverse').each(function(){$(this).prop('checked','checked')});
			$('input[name="id[]"]').each(function(){$(this).prop('checked','');this.click();});
		}
		else {
			$('.reverse').each(function(){$(this).prop('checked','')});
			$('input[name="id[]"]').each(function(){$(this).prop('checked','checked');this.click();});
		}
	});
	
	// import field
	$('form.import input[type="file"]').on('change',function(){
		$(this).parent().find('label').text($(this).val().substring($(this).val().lastIndexOf("\\") + 1));
	});
	
	$('a.bckffcActionDelete').click(function(){if(!confirm('Attention êtes vous sur de vouloir supprimer ??')) return false;});
	$('.datePick').datepicker({dateFormat: 'yy-mm-dd'});
	
	// waiting for a better solution we'll hide the navigation bar
	if($('#bckffc-login').length>0) {
		$('nav').hide();
	}
	
	$('a.preview').each(function() {
		if($(this).attr('href')=='../media/_upload/') $(this).hide();
	});
	$('#content table tr').each(function(){
		if($(this).find('td:eq(2)').text()=='non répondu') {
			$(this).addClass('red');
		}
		else {
			if($(this).find('td:eq(2)').text()=='oui') {
				$(this).addClass('green');
			}
			if($(this).find('td:eq(2)').text()=='non') {
				$(this).addClass('white');
			}
		}
	});

	// mise en place d'un système de liste ouverte type "Autre précisez ..."
	$('.openlist').on('focus click',function() {
		$(this).parent().find('.data-list').show();
	});
	$('.openlist').on('change',function() {
		$(this).parent().find('.data-list').hide();
	});
	$('.data-list li').on('click',function() {
		if(!$(this).hasClass('free')) {
			$(this).parent().after('<input type="text" class="layout left openlist" style="clear:left;">');
			$(this).parent().parent().find('.openlist:eq(1)').val($(this).text());
			$(this).parent().parent().find('.openlist:eq(1)').attr('readonly', true);
			$(this).parent().hide();
		}
		else {
			$(this).parent().after('<input type="text" class="layout left openlist" style="clear:left;">');
			$(this).parent().parent().find('.openlist:eq(1)').val($(this).text());
			$(this).parent().parent().find('.openlist:eq(1)').attr('readonly', false);
			$(this).parent().parent().find('.openlist:eq(1)').focus();
			$(this).parent().hide();
		}
		var newvalue = '';
		var hiddenfield = $(this).parent().parent().parent().find('.list');
		var hiddenvalue = hiddenfield.val();
		var a = new Array();
		var newarray = new Array();

		if(hiddenvalue.length>0) a = hiddenvalue.split(',');
		for(var i=0;i<a.length;i++) {
			newarray.push(a[i]);
		}
		newarray.push($(this).attr('data'));
		newvalue = newarray.join(',');
		hiddenfield.attr('value',newvalue).val(newvalue);
	});

	// gestion de la supression des relations entre les entités
	$('fieldset.rel a.delete').on('click',function(e) {
		e.preventDefault();
		// alert('toto');
		// récupération du champs caché listant les relations
		var hiddenField = $(this).parent().parent().parent().parent().find('input[type="hidden"]');
		// récupération de la valeur du champs caché
		var hiddenVal = hiddenField.val();
		// récupération de la valeur a supprimer
		var id = $(this).attr('href').substr(4);

		var a = hiddenVal.split(',');
		var n = new Array();
		for(var i=0;i<a.length;i++) {
			if(a[i]!=id) n.push(a[i]);
		}
		hiddenField.val(n.join(','));
		$(this).parent().remove();
	});

	$('#update').click(function() {
		// alert("toto");
		var message='';
		var typeMismatch='';

		// checking if all mandatory fields are field
		$('#bckffcForm .mandatory').each(function() {
			if($(this).parent().is(':visible') && $(this).attr('value')=='') {
				$('#'+$(this).attr('name')).addClass('error');
				message = message + $('#'+$(this).attr('name')).find('label').text() + "<br/>";
			}
		});
		$('#bckffcForm .mail').each(function() {
			if(!$(this).verifyMail()) {
				typeMismatch = typeMismatch + $('#'+$(this).attr('name')).find('label').text() + mailFormatError;
			}
		});
		$('#bckffcForm .int').each(function() {
			if(!$(this).verifyInteger()) {
				typeMismatch = typeMismatch + $('#'+$(this).attr('name')).find('label').text() + intFormatError;
			}
		});
		$('#bckffcForm .num').each(function() {
			if(!$(this).verifyNum()) {
				typeMismatch = typeMismatch + $('#'+$(this).attr('name')).find('label').text() + numFormatError;
			}
		});
		$('#bckffcForm .float').each(function() {
			if(!$(this).verifyFloat()) {
				typeMismatch = typeMismatch + $('#'+$(this).attr('name')).find('label').text() + floatFormatError;
			}
		});
		$('#bckffcForm .date').each(function() {
			var format='Y-m-d';
			if(!$(this).verifyDate('Y-m-d')) {
				typeMismatch = typeMismatch + $('#'+$(this).attr('name')).find('label').text() + dateFormatError;
			}
		});
		
		if(message!='') {
			message = missingFields + message;
		}
		if(typeMismatch!='') {
			typeMismatch = incorrectFormat + typeMismatch;
		}
		if(message!='' || typeMismatch!='') {
			$('#message').html(closeLink + message + typeMismatch);
			$('#message').show();
			return false;
		}
		$('#bckffcForm').submit();
	});
});