// js for front end of ProGo Themes Direct Response sites

function checkReq() {
	var aok = true;
	jQuery('form.pform .need').removeClass('.need');
	jQuery('form.pform .req').each(function() {
		if(jQuery(this).val()=='') {
			aok = false;
			jQuery(this).addClass('need');
		}
	});
	if(!aok) alert('Please check all *Required fields.');
	return aok; 
}

function checkThisField() {
	if(jQuery(this).val()=='') jQuery(this).addClass('need');
	else jQuery(this).removeClass('need');
}

jQuery(function($) {
	$('#edit').change(function() {
		$('#billing,#shipping').toggle();
	});
	$('form.pform input.req').bind('blur',checkThisField);
	$('form.pform select.req').bind('change',checkThisField);
	$('form.pform').bind('submit',checkReq);
	
	$('#side .editchecks input:checkbox').click(function() {
		var check = $(this).attr('checked');
		var show = 'payment';
		if(check) {
			show = $(this).attr('name') == 'editbilling' ? 'billing' : 'shipping';
		}
		$('#'+show).show().siblings('fieldset').hide();
		$(this).parent().siblings('label').children('input:checkbox').attr('checked',false);
	});
});