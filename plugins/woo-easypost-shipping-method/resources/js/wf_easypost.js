jQuery(document).ready(function(){
	wf_services();
	jQuery('#woocommerce_wf_easypost_id_easypost_carrier').on('change', function(){
		wf_services();
	});
});

function wf_services(){
	enabledCarriers = jQuery('#woocommerce_wf_easypost_id_easypost_carrier').val();
	jQuery('.services').each(function(){
		if( jQuery.inArray( jQuery(this).attr('carrier'), enabledCarriers ) < 0 ){
			jQuery(this).hide();
		}else{
			jQuery(this).show();
		}
	});
}
