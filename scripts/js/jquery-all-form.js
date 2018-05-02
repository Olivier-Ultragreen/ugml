var lang = 'fr';
mailFormatError = ' : format incorrect!<br/>';
intFormatError = ' : format incorrect !<br/>';
numFormatError = ' : format incorrect !<br/>';
floatFormatError = ' : format incorrect !<br/>';
dateFormatError = ' : format attendu dd/mm/yyyy !<br/>';
phoneFormatError =' : format incorrect !<br/>';

(function( $ ){
	$.fn.verifyInteger = function() {
		if(isNaN(this.val())) return false;
		else return true;
	};
	$.fn.verifyNum = function() {
		return $.isNumeric(this.val());
    };
	$.fn.verifyFloat = function() {
		return $.isNumeric(this.val());
    };
	$.fn.verifyMail = function() {
		if(this.val() == '') return true;
		var reg = new RegExp('^([A-Za-z0-9\-\._])+\@([A-Za-z0-9\-\._])+\.([A-Za-z]{2,4})$','g');
		return reg.test(this.val());
	};
	$.fn.verifyPhone = function() {
		if(this.val() == '') return true;
		var reg = new RegExp('^([0-9]{8,12})$','g');
		return reg.test(this.val());
	};
	
	$.fn.verifyDate = function(format) {
		if(this.val()=='') return true;
		switch(format) {
			case "yyyy-mm-dd": var reg = new RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$', 'g'); break;
			case "Y-m-d": var reg = new RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$', 'g'); break;
			case "dd/mm/yyyy": var reg = new RegExp('^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$', 'g'); break;
			default : var reg = new RegExp('^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$', 'g'); break;
		}
		return reg.test(this.val());
	};
	$.fn.getDisplayFields = function() {
		var fieldname = this.attr('name');
		if(this.attr('name').lastIndexOf('[]')>0) {
			fieldname = fieldname.substring(0,fieldname.lastIndexOf('[]')) + '_display[]';
		}
		else {
			fieldname = fieldname + '_display';
			if(this.attr('name').lastIndexOf('_list')>0){
				fieldname = fieldname + '[]';
			}
		}
		return this.parent().find('[name="'+ fieldname +'"]');
	};
	$.fn.getHiddenField = function() {
		var fieldname = this.attr('name');
		if(this.attr('name')=='id[]') return $('#ids');
		if(this.attr('name').lastIndexOf('[]')>0 && this.attr('name').lastIndexOf('_list')==-1) {
			fieldname = fieldname.substring(0,fieldname.lastIndexOf('_display')) + '[]';
		}
		else {
			fieldname = fieldname.substring(0,fieldname.lastIndexOf('_display'));
		}
		// alert(fieldname);
		if(this.parent().find('[name="'+ fieldname +'"]').length>0) {
			return this.parent().find('[name="'+ fieldname +'"]');
		}
		else {
			return this.parent().parent().find('[name="'+ fieldname +'"]');
		}
	};
	$.fn.isFilled = function() {
		var fieldname = this.attr('name');
		if(this.val()!='' && this.val()!='0') return true;
		
		// si la valeur est vide
		if(this.parent().is(':visible') && (this.val()=='')) {
			// vérifie s'il exite un champ display associé
			if($('[name="'+fieldname+'_display"]:checked').length>0) {
				if($('[name="'+fieldname+'_display"]:checked').val()!='') {
					return true;
				}
				else {
					return false;
				}
			}
		}
		return false;
	}
})( jQuery );
$(document).ready(function() {
	$('[type=hidden]').each(function () {
		var hiddenvalue = $(this).val();
		$(this).getDisplayFields().each(function() {
			// checking the proper radio button for display
			if($(this).attr('type')=='radio') {
				if($(this).val()==hiddenvalue) {
					$(this).attr('checked','checked');
				}
			}
			// checking the proper checkbox button for display
			if($(this).attr('type')=='checkbox') {
				var a = hiddenvalue.split(',');
				if(a.length>1 && a.indexOf($(this).val())>=0) {
					$(this).attr('checked','checked');
				}
				else {
					if(a.length=1 && $(this).val()==hiddenvalue) {
						$(this).attr('checked','checked');
					}
				}
			}
			// select the proper option
			if($(this).is('select')) {
				$(this).val(hiddenvalue);
			}
		});
	});
	$('body').on('change','[type=radio]',function() {
		var value = $(this).val();
		$(this).getHiddenField().attr('value',value).val(value);
	});
	$('body').on('change','select', function() {
		var value = $(this).val();
		$(this).getHiddenField().attr('value',value).val(value);
	});
	/*
	$('body').on('change','[type=checkbox]', function() {
		var newvalue = '';
		var hiddenfield = $(this).getHiddenField();
		var hiddenvalue = hiddenfield.val();
		// alert(hiddenvalue);
		var a = new Array();
		if(hiddenvalue.length>0) a = hiddenvalue.split(',');
		if($(this).is(':checked')) {
			// alert($(this).val());
			if(a.indexOf($(this).val())==-1 ) {
				a.push($(this).val());
				newvalue = a.join(',');
				hiddenfield.attr('value',newvalue).val(newvalue);
			}
		}
		else {
			var newarray = new Array();
			for(var i=0;i<a.length;i++) {
				if(a[i]!=$(this).val() && a[i]) newarray.push(a[i]);
			}
			newvalue = newarray.join(',');
			hiddenfield.attr('value',newvalue).val(newvalue);
		}
	});
	*/
});