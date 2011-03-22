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
	var val = jQuery(this).val();
	var lab = jQuery(this).prev().html();
	if(val=='' || lab.indexOf(val) >= 0) {
		jQuery(this).addClass('need');
	}
	else {
		jQuery(this).removeClass('need');
	}
}

function progo_set_shipping_country(html_form_id, form_id){
	var shipping_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();

	if(country == 'undefined'){
		country =  jQuery("select[title='billingcountry']").val();
	}

	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		shipping_region = "&shipping_region="+region;
	}

	form_values = {
		wpsc_ajax_action: "change_tax",
		form_id: form_id,
		shipping_country: country,
		shipping_region: region
	}
	
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
		jQuery('.statelabel').each(function() {
			if(jQuery(this).next().html() == '') {
				jQuery(this).hide();
			} else {
				jQuery(this).show();//.parents().show();
				if(jQuery(this).parent().next().hasClass('zip')) {
					 jQuery(this).next().children().css('width','54px');
				}
			}
		});
	});
	
}

function progo_set_billing_country(html_form_id, form_id){
	var billing_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();
	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		billing_region = "&billing_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&billing_country="+country+billing_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
		jQuery('.statelabel').each(function() {
			if(jQuery(this).next().html() == '') {
				jQuery(this).hide();
			} else {
				jQuery(this).show();//.parents().show();
				if(jQuery(this).parent().next().hasClass('zip')) {
					 jQuery(this).next().children().css('width','54px');
				}
			}
		});
	});
}

jQuery(function($) {
	$('#edit').change(function() {
		$('#billing,#shipping').toggle();
	});
	$('form.pform input.req').bind('blur.progo',checkThisField);
	$('form.pform select.req').bind('change.progo',checkThisField);
	$('form.pform').bind('submit',checkReq);
	
	$('.pform .current_country').trigger('change');
	
	var editchx = $('#side .editchecks input:checkbox');
	if(editchx.size() > 0) {
		editchx.click(function() {
			var check = $(this).attr('checked');
			var show = 'payment';
			if(check) {
				show = $(this).attr('name') == 'editbilling' ? 'billing' : 'shipping';
			}
			$('#'+show).show().siblings('fieldset').hide();
			$(this).parent().siblings('label').children('input:checkbox').attr('checked',false);
		});
	}
	$('select.current_country').each(function() {
		if($(this).children().size() == 1) {
			$(this).hide().parent().parent().prev().hide();
		}
	});
});