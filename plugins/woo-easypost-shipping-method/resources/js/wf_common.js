jQuery(document).ready(function(){
	
	//Toggle packing methods
	wf_load_packing_method_options();
	jQuery('.packing_method').change(function(){
		wf_load_packing_method_options();
	});
	
	
	// Advance settings tab
	jQuery('.wf_settings_heading_tab').next('table').hide();
	jQuery('.wf_settings_heading_tab').click(function(event){
		event.stopImmediatePropagation();
		jQuery(this).next('table').toggle();
	});
        
        //Specific Countries in rates tab field
        jQuery('#woocommerce_wf_easypost_id_availability').change(function(){
		var value=document.getElementById('woocommerce_wf_easypost_id_availability').value;
		if(value=='specific')
		{
            jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').show();
		}	
		else
		{
            jQuery('#woocommerce_wf_easypost_id_countries').closest('tr').hide();
		}
	});
});

function wf_load_packing_method_options(){
	pack_method	=	jQuery('.packing_method').val();
	jQuery('#packing_options').hide();
	jQuery('.weight_based_option').closest('tr').hide();
	switch(pack_method){
		case 'per_item':
		default:
			break;
			
		case 'box_packing':
			jQuery('#packing_options').show();
			break;
			
		case 'weight_based':
			jQuery('.weight_based_option').closest('tr').show();
			break;
	}
}
