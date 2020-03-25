<tr valign="top" id="service_options" class="rates_tab_field">
	<td class="forminp" colspan="2" style="padding-left:0px">
	<strong><?php _e( 'Services', 'wf-easypost' ); ?></strong><br/>
		<table class="easypost_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php _e( 'Service(s) ', 'wf-easypost' ); ?></th>
				<th><?php _e( 'Name <span style="vertical-align: super;color:green;font-size:12px">Premium</span>', 'wf-easypost' ); ?></th>
				<th><?php echo sprintf( __( 'Price Adjustment (%s) <span style="vertical-align: super;color:green;font-size:12px">Premium</span>', 'wf-easypost' ), get_woocommerce_currency_symbol() ); ?></th>
				<th><?php _e( 'Price Adjustment (%) <span style="vertical-align: super;color:green;font-size:12px">Premium</span>', 'wf-easypost' ); ?></th>
			</thead>
			<tbody>
				<?php
					$sort = 0;
					$this->ordered_services = array();
					
					foreach ( $this->services as $code => $values ) {
						$ordered_services = array();
						// if( in_array($code, $this->carrier) ){
							foreach ($values['services'] as $key => $value) {
								if ( is_array($this->custom_services) && isset( $this->custom_services[ $code ][ $key ]['order'] ) && !empty( $this->custom_services[ $code ][ $key ]['order'] ) ){
									$sort = $this->custom_services[ $code ] [ $key ] ['order'];
								}

								while ( isset( $this->ordered_services[ $sort ] ) ) {
                                    $sort++;
                                }
									
								if( !empty( $this->custom_services ) && array_key_exists( $code, $this->custom_services ) ){
									$ordered_services[ $sort ] = array( $key, $this->custom_services[ $code ][ $key ] );
								}
								else{
									$ordered_services[ $sort ] = array( 
										$key, array(
											$code => array(
												$key=> array(
													'enalbled' => true,
													'adjustment' => '',
													'adjustment_percent' => '',
													'name' => '',
													'order' =>'' 
												)
											)
										)
									);
								}

								$sort++;
							}
						// }
						$this->ordered_services[ $code ] = $ordered_services;
					}


					foreach ($this->ordered_services as $key => $value) {
						ksort( $this->ordered_services[$key] );
					}
					foreach ( $this->ordered_services as $code => $value ) {
						if ( !empty($this->custom_services) &&  !isset( $this->custom_services[$code] ) )
                            {
                                $this->custom_services[$code] = array();
                            }
												
						foreach ( $value as $order => $values ){
							$key   = $values[0];
							
							?>
							<tr class="services" carrier="<?php echo $code; ?>">
								<td class="sort">
									<input type="hidden" class="order" name="easypost_service[<?php echo $code; ?>][<?php echo $key; ?>][order]" value="<?php echo isset( $this->custom_services[ $code ][ $key ]['order'] ) ? $this->custom_services[ $code ][ $key ]['order'] : ''; ?>" />
								</td>
								<td>
									<label>
										<input type="checkbox" name="easypost_service[<?php echo $code; ?>][<?php echo $key; ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ][ $key ]['enabled'] ) || ! empty( $this->custom_services[ $code ][ $key ]['enabled'] ) ), true ); ?> />
										<?php echo $key; ?>
									</label>
								</td>
								<td>	
									<input type="text" name="easypost_service" placeholder="<?php echo (string)$this->services[$code]['services'][$key] ?>"  size="30" disabled />
								</td>
								<td>
									<?php echo get_woocommerce_currency_symbol(); ?><input type="text"  placeholder="N/A"  size="4" disabled/>
								</td>
								<td>
									<input type="text"  placeholder="N/A"  size="4" disabled/>%
								</td>
							</tr>
							<?php
						}
					}
				?>
			</tbody>
		</table>
	</td>
</tr>

<style type="text/css">
	.easypost_services {
		    width: 100.5%;
	}
	.easypost_services td,.easypost_services th {
		vertical-align: middle;
		padding: 4px 7px;
	}
	.easypost_boxes .check-column {
		vertical-align: middle;
		text-align: left;
		padding: 0 7px;
	}
	.easypost_services th.sort {
		width: 16px;
	}
	.easypost_services td.sort {
		cursor: move;
		width: 16px;
		padding: 0;
		cursor: move;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;
	}
</style>
<script type="text/javascript">

	jQuery(window).load(function(){

		jQuery('#woocommerce_usps_enable_standard_services').change(function(){
			if ( jQuery(this).is(':checked') ) {
				jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').show();
				jQuery('#service_options, #packing_options').show();
				jQuery('#woocommerce_usps_packing_method, #woocommerce_usps_offer_rates').closest('tr').show();
				jQuery('#woocommerce_usps_packing_method').change();
			} else {
				jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').hide();
				jQuery('#service_options, #packing_options').hide();
				jQuery('#woocommerce_usps_packing_method, #woocommerce_usps_offer_rates').closest('tr').hide();
			}
		}).change();

		// Ordering
		jQuery('.usps_services tbody').sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: '.sort',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css('baclbsround-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
				usps_services_row_indexes();
			}
		});

		function usps_services_row_indexes() {
			jQuery('.usps_services tbody tr').each(function(index, el){
				jQuery('input.order', el).val( parseInt( jQuery(el).index('.usps_services tr') ) );
			});
		};

	});

</script>
