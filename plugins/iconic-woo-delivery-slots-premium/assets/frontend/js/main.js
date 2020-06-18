(function( $, document ) {
	var jckwds = {

		cache: function() {
			jckwds.els = {};
			jckwds.vars = {};

			// common elements
			jckwds.els.document = $( document );
			jckwds.els.document_body = $( document.body );
			jckwds.els.reservation_table = $( '.jckwds-reserve' );
			jckwds.els.reservation_table_rows = jckwds.els.reservation_table.find( 'tr' );
			jckwds.els.reservation_table_prev = $( '.jckwds-prevday' );
			jckwds.els.reservation_table_next = $( '.jckwds-nextday' );
			jckwds.els.date_picker = $( "#jckwds-delivery-date" );
			jckwds.els.date_ymd = $( "#jckwds-delivery-date-ymd" );
			jckwds.els.timeslot_select = $( '#jckwds-delivery-time' );
			jckwds.els.timeslot_select_wrapper = $( '#jckwds-delivery-time-wrapper' );
			jckwds.els.timeslot_field_row = $( '#jckwds-delivery-time_field' );
			jckwds.els.checkout_fields = $( '#jckwds-fields' );
			jckwds.els.fields_hidden = $( '[name="iconic-wds-fields-hidden"]' );
			jckwds.els.ship_to_different_address_checkbox = $( '#ship-to-different-address-checkbox' );
			jckwds.els.shipping_postcode_field = $( '#shipping_postcode' );
			jckwds.els.billing_postcode_field = $( '#billing_postcode' );
			jckwds.els.multi_step_checkout = $( '#wizard' );
			jckwds.els.calender_wrap = $( '.jckwds-reserve-wrap' );

			// common vars
			jckwds.vars.is_checkout = jckwds.els.document_body.hasClass( 'woocommerce-checkout' ) || jckwds.els.document_body.hasClass( 'checkout-wc' );
			jckwds.vars.has_multi_step = jckwds.els.multi_step_checkout.length > 0 ? true : false;
			jckwds.vars.inactive_class = 'jckwds-fields-inactive';
			jckwds.vars.chosen_shipping_method = false;
			jckwds.vars.block_checkout_index = 0;
			jckwds.vars.set_date_flag = false;
		},

		on_load: function() {
			// on load stuff here
			jckwds.cache();

			jckwds.setup_reservation_table();
			jckwds.setup_checkout();
			jckwds.setup_multi_step_checkout();
		},

		/**
		 * Reservation Table: Functions to run for the reservation table
		 */
		setup_reservation_table: function() {

			if ( jckwds.els.reservation_table.length <= 0 ) {
				return;
			}

			jckwds.setup_prev_next();
			jckwds.setup_reserve_button();
			jckwds.load_reserved_slot();

		},

		/**
		 * Reservation Table: Load the reserved slot via AJAX.
		 */
		load_reserved_slot: function() {
			if ( jckwds.els.calender_wrap.length === 0 ) {
				return false;
			}

			jckwds.els.calender_wrap.block({
				message: null,
				overlayCSS: {
					opacity: 0.6
				}
			});

			var data = {
				action: "iconic_wds_get_reserved_slot"
			};

			jQuery.post( jckwds_vars.ajax_url, data , function ( response ) {
				if ( response.success ) {
					$( ".jckwds-reserved" ).removeClass( "jckwds-reserved" );
					$( '[data-timeslot-id="' + response.data.id + '"]' ).addClass( "jckwds-reserved" );
				}
				jckwds.els.calender_wrap.unblock();
			});
		},

		/**
		 * Checkout: Functions to run on checkout
		 */
		setup_checkout: function() {

			if ( ! jckwds.vars.is_checkout || jckwds.vars.has_multi_step ) {
				return;
			}

			jckwds.setup_checkout_fields();
			jckwds.watch_update_checkout();

		},

		/**
		 * Checkout: If multi step checkout is enabled
		 */
		setup_multi_step_checkout: function() {

			if ( ! jckwds.vars.is_checkout || ! jckwds.vars.has_multi_step ) {
				return;
			}

			jckwds.els.multi_step_checkout.init( function() {
				jckwds.cache();
				jckwds.setup_checkout_fields();
				jckwds.watch_update_checkout();
			} );
		},

		/**
		 * Reservation Table: Setup Prev/Next buttons on reservation table
		 */
		setup_prev_next: function() {

			jckwds.els.reservation_table_prev.on( 'click', function() {

				$.each( jckwds.els.reservation_table_rows, function() {

					var $firstVisIndex = $( this ).find( '.colVis:first' ).index();

					if ( $firstVisIndex !== 1 ) {

						$( this ).children().eq( $firstVisIndex - 1 ).addClass( 'colVis' );
						$( this ).find( '.colVis:last' ).removeClass( 'colVis' );

					}

				} );

				return false;

			} );

			jckwds.els.reservation_table_next.on( 'click', function() {

				var $lastVisIndex = $( '.jckwds-reserve thead tr .colVis:last' ).index(),
					$firstVisIndex = $( '.jckwds-reserve thead tr .colVis:first' ).index();

				$.each( jckwds.els.reservation_table_rows, function() {

					var $lastVisIndex = $( this ).find( '.colVis:last' ).index();

					if ( $lastVisIndex + 1 < $( this ).children().length ) {

						$( this ).children().eq( $lastVisIndex + 1 ).addClass( 'colVis' );
						$( this ).find( '.colVis:first' ).removeClass( 'colVis' );

					}

				} );

				return false;

			} );

		},

		/**
		 * Reservation Table: Setup reservation table reserve button
		 */
		setup_reserve_button: function() {

			jckwds.els.document.on( 'click', '.jckwds-reserve-slot', function() {

				jckwds.activate_slot( $( this ) );
				return false;

			} );

		},

		/**
		 * Reservation Table: Activate the clicked slot
		 */
		activate_slot: function( $the_slot ) {

			var $slot_parent = $the_slot.parent(),
				cell_data = $slot_parent.html(),
				slot_id = $slot_parent.attr( 'data-timeslot-id' ),
				slot_date = $slot_parent.attr( 'data-timeslot-date' ),
				slot_start_time = $slot_parent.attr( 'data-timeslot-start-time' ),
				slot_end_time = $slot_parent.attr( 'data-timeslot-end-time' ),
				$table_wrap = $the_slot.closest( '.jckwds-reserve-wrap' ),
				loader = '<div class="jckwds_loading"><i class="jckwds-icn-loading animate-spin"></i></div>',
				remove_reserved_data = {
					action: 'iconic_wds_remove_reserved_slot',
					nonce: jckwds_vars.ajax_nonce
				};

			$the_slot.hide().after( loader );

			jQuery.post( jckwds_vars.ajax_url, remove_reserved_data, function( response ) {

				if ( response.success ) {

					jckwds.els.document_body.trigger( 'reservation_removed' );

					var reserve_data = {
						action: 'iconic_wds_reserve_slot',
						nonce: jckwds_vars.ajax_nonce,
						slot_id: slot_id,
						slot_date: slot_date,
						slot_start_time: slot_start_time,
						slot_end_time: slot_end_time
					};

					jQuery.post( jckwds_vars.ajax_url, reserve_data, function( response ) {
						$( 'td.jckwds-reserved' ).removeClass( 'jckwds-reserved' );

						if ( response.success ) {
							$slot_parent.addClass( 'jckwds-reserved' ).html( cell_data );
						} else {
							$slot_parent.addClass( 'jckwds_full' ).html( '<i class="jckwds-icn-lock"></i>' );
						}

						jckwds.els.document_body.trigger( 'reservation_added', response );
					} );

				}

			} );

		},

		/**
		 * Checkout: Setup date/time fields
		 */
		setup_checkout_fields: function() {
			jckwds.setup_date_picker();
			jckwds.setup_timeslot_select();
		},

		/**
		 * Checkout: Setup date_picker
		 */
		setup_date_picker: function() {
			jckwds.els.date_picker.datepicker( {
				defaultDate: jckwds_vars.bookable_dates[ 0 ],
				minDate: jckwds_vars.bookable_dates[ 0 ],
				beforeShow: function( input, inst ) {
					var theme = $.inArray( jckwds_vars.settings.datesettings_datesettings_setup_uitheme, [ 'none', 'dark', 'light' ] ) >= 0 ? jckwds_vars.settings.datesettings_datesettings_setup_uitheme : 'dark';

					inst.dpDiv.addClass( 'iconic-wds-datepicker iconic-wds-datepicker--' + theme );
				},
				beforeShowDay: function( date ) {
					var formatted_date = $.datepicker.formatDate( jckwds_vars.settings.datesettings_datesettings_dateformat, date, {
							monthNames: jckwds_vars.strings.months,
							monthNamesShort: jckwds_vars.strings.months_short,
							dayNames: jckwds_vars.strings.days,
							dayNamesShort: jckwds_vars.strings.days_short,
							dayNamesMin: jckwds_vars.strings.days_short
						} ).toString(),
						cell_class = 'iconic-wds-date';

					if ( jckwds.is_date_available( formatted_date ) ) {
						var fee = jckwds.get_fee_for_date( date ),
							text = fee ? jckwds_vars.strings.available + ': +' + fee : jckwds_vars.strings.available;

						if ( fee ) {
							cell_class += ' iconic-wds-date--fee';
						}

						return [ true, cell_class, text ];
					} else {
						return [ false, cell_class, jckwds_vars.strings.unavailable ];
					}
				},
				dateFormat: jckwds_vars.settings.datesettings_datesettings_dateformat,
				onSelect: function( dateText, inst ) {
					/* Trigger change event */
					$( this ).trigger( 'change' );

					if ( this.value === "" ) {
						return;
					}

					var selected_year = jckwds.pad_left( inst.selectedYear, 4 ),
						selected_month = jckwds.pad_left( inst.selectedMonth + 1, 2 ),
						selected_day = jckwds.pad_left( inst.selectedDay, 2 ),
						selected_date_ymd = [ selected_year, selected_month, selected_day ].join( '' );

					/* Add selected date to hidden date ymd field for processing */
					jckwds.els.date_ymd.val( selected_date_ymd );

					// If we programatically set the date, we don't need to refresh the time slots.
					if ( jckwds.vars.set_date_flag ) {
						jckwds.vars.set_date_flag = false;

						return;
					}

					// if time slots are enabled
					if ( jckwds.is_true( jckwds_vars.settings.timesettings_timesettings_setup_enable ) ) {
						/* timeslot lookup after date selection */
						jckwds.update_timeslot_options( selected_date_ymd );
					} else {
						jckwds.els.document_body.trigger( 'update_checkout' );
					}
				},
				monthNames: jckwds_vars.strings.months,
				monthNamesShort: jckwds_vars.strings.months_short,
				dayNames: jckwds_vars.strings.days,
				dayNamesShort: jckwds_vars.strings.days_short,
				dayNamesMin: jckwds_vars.strings.days_short,
				firstDay: jckwds.get_first_day_of_the_week(),
				duration: 0
			} );

			$.datepicker.setDefaults( $.datepicker.regional[ '' ] );
			jckwds.els.date_picker.datepicker( $.datepicker.regional[ '' ] );

			// Fix Google Translate bug
			$( '.ui-datepicker' ).addClass( 'notranslate' );
		},

		/**
		 * Check if value is true.
		 *
		 * @param value
		 * @return {boolean}
		 */
		is_true: function( value ) {
			if ( ! value || value === 0 || value === '0' || value === '' || value.length <= 0 ) {
				return false;
			}

			return true;
		},

		/**
		 * Get fee for date.
		 *
		 * @param date
		 *
		 * @return string|bool
		 */
		get_fee_for_date: function( date ) {
			var day_number = date.getDay(),
				fee = parseFloat( jckwds_vars.settings.datesettings_fees_days[ day_number ] ),
				ymd = $.datepicker.formatDate( jckwds_vars.settings.datesettings_datesettings_dateformat, date, {
					monthNames: jckwds_vars.strings.months,
					monthNamesShort: jckwds_vars.strings.months_short,
					dayNames: jckwds_vars.strings.days,
					dayNamesShort: jckwds_vars.strings.days_short,
					dayNamesMin: jckwds_vars.strings.days_short
				} ).toString();

			// Same day
			if ( ymd === jckwds_vars.dates.same_day && jckwds_vars.settings.datesettings_fees_same_day.length > 0 ) {
				fee += parseFloat( jckwds_vars.settings.datesettings_fees_same_day );
			}

			// Next day
			if ( ymd === jckwds_vars.dates.next_day && jckwds_vars.settings.datesettings_fees_next_day.length > 0 ) {
				fee += parseFloat( jckwds_vars.settings.datesettings_fees_next_day );
			}

			return isNaN( fee ) ? false : accounting.formatMoney( fee, jckwds_vars.currency );
		},

		/**
		 * Set date.
		 *
		 * @param value
		 */
		set_date: function( value ) {
			if ( value.length <= 0 || ! jckwds.is_date_available( value, false ) ) {
				jckwds.els.date_picker
					.blur()
					.datepicker( 'hide' )
					.datepicker( 'setDate', null );
				jckwds.els.date_ymd.val( '' );
				jckwds.clear_timeslots( jckwds_vars.strings.selectdate );
				return;
			}

            jckwds.vars.set_date_flag = true;
			value = $.datepicker.parseDate( jckwds_vars.settings.datesettings_datesettings_dateformat, value, {
				monthNames: jckwds_vars.strings.months,
				monthNamesShort: jckwds_vars.strings.months_short,
				dayNames: jckwds_vars.strings.days,
				dayNamesShort: jckwds_vars.strings.days_short,
				dayNamesMin: jckwds_vars.strings.days_short
			} );

			jckwds.els.date_picker
				.blur()
				.datepicker( 'hide' )
				.datepicker( 'setDate', value );
			$( '.ui-datepicker .ui-state-active' ).click();
		},

		/**
		 * Is date available to be selected (case insensitive).
		 *
		 * There was an issue with date formats sometimes being lower case,
		 * and sometimes not - thus they weren't matching.
		 *
		 * @param date
		 * @return {boolean}
		 */
		is_date_available: function( date ) {
			if ( jckwds_vars.bookable_dates.length <= 0 ) {
				return false;
			}

			var match = false;

			date = date.toLowerCase();

			$.each( jckwds_vars.bookable_dates, function( index, value ) {
				if ( false === match && value.toLowerCase() === date ) {
					match = true;
					return false;
				}
			} );

			return match;
		},

		/**
		 * Helper: Get all timeslots available on a specific date,
		 *         and update the timeslots dropdown
		 *
		 * @param [str] [date] [format?]
		 * @param [func] [callback]
		 */
		update_timeslot_options: function( date, callback ) {

			var $first_timeslot_option = jckwds.els.timeslot_select.find( "option:eq(0)" ),
				currently_selected = jckwds.els.timeslot_select.val(),
				postcode = ( jckwds.els.ship_to_different_address_checkbox.is( ":checked" ) ? $( '#shipping_postcode' ).val() : $( '#billing_postcode' ).val() );

			jckwds.clear_timeslots( jckwds_vars.strings.loading );

			jckwds.els.timeslot_select.attr( 'disabled', 'disabled' ).trigger( 'change', [ 'update_timeslots' ] );

			var ajaxData = {
				action: 'iconic_wds_get_slots_on_date',
				nonce: jckwds_vars.ajax_nonce,
				date: date,
				postcode: postcode
			};

			jQuery.post( jckwds_vars.ajax_url, ajaxData, function( response ) {
				if ( response.success === true ) {
					jckwds.clear_timeslots( jckwds_vars.strings.selectslot );
					jckwds.els.timeslot_select.append( response.html );

					currently_selected = jckwds.els.timeslot_select.find( 'option[value="' + currently_selected + '"]' ).length > 0 ? currently_selected : 0;
					jckwds.els.timeslot_select.val( currently_selected );

					if ( response.reservation ) {
						if ( jckwds.els.timeslot_select.find( "option[value='" + response.reservation + "']" ).length > 0 ) {
							jckwds.els.timeslot_select.val( response.reservation );
						}
					}
					// It causes infinite loops when we trigger the event on failed responses
					jckwds.els.document_body.trigger( 'update_checkout' );
				} else {
					$first_timeslot_option.text( jckwds_vars.strings.noslots );
				}

				jckwds.els.document_body.trigger( 'timeslots_loaded' );
				jckwds.els.timeslot_select.removeAttr( 'disabled' ).trigger( 'change', [ 'update_timeslots' ] );

				if ( callback !== undefined ) {
					callback( response );
				}

			} );

		},

		/**
		 * Checkout: Refresh time slots
		 *
		 * @param force
		 */
		refresh_timeslots: function( force ) {
			force = typeof force !== 'undefined' ? force : false;

			// if a reservation is in place, don't refresh timeslots
			if (
				jckwds.els.timeslot_field_row.hasClass( 'jckwds-delivery-time--has-reservation' ) &&
				force === false
			) {
				jckwds.els.timeslot_field_row.removeClass( 'jckwds-delivery-time--has-reservation' );
				return;
			}

			// refresh timeslots, based on date
			var date = jckwds.els.date_ymd.val();

			if ( typeof date !== "undefined" && date !== "" ) {
				jckwds.update_timeslot_options( date );
			}

			jckwds.els.document_body.trigger( 'timeslots_refreshed' );
		},

		/**
		 * Clear date fields.
		 */
		clear_date: function() {
			jckwds.els.date_ymd.val( '' );
			jckwds.set_date( '' );
		},

		/**
		 * Clear timeslots from select and optionally replace first option text.
		 *
		 * @param first_option_text
		 */
		clear_timeslots: function( first_option_text ) {
			jckwds.els.timeslot_select.children().not( ':first' ).remove();

			if ( typeof first_option_text === 'string' ) {
				jckwds.els.timeslot_select.find( "option:eq(0)" ).text( first_option_text );
			}
		},

		/**
		 * Checkout: Setup timeslot field
		 *
		 * Don't update checkout if we've triggered the select change ourselves
		 */
		setup_timeslot_select: function() {
			// update checkout on time selection
			jckwds.els.timeslot_select.on( 'change', function( event, type ) {

				type = typeof type !== "undefined" ? type : false;

				if ( type === "update_timeslots" ) {
					return;
				}

				jckwds.els.document_body.trigger( 'update_checkout' );
			} );

		},

		/**
		 * Checkout: Watch for the update_checkout trigger
		 */
		watch_update_checkout: function() {
			/**
			 * Toggle checkout fields loading and disable place order button.
			 */
			jckwds.els.document_body.on( 'update_checkout', function( e ) {
				jckwds.block_checkout();
			} );

			jckwds.els.document_body.on( 'updated_checkout', function( e, data ) {
				jckwds.update_checkout_field_labels( data.fragments.iconic_wds.labels );

				/**
				 * If shipping method hasn't changed.
				 */
				if ( data.fragments.iconic_wds.chosen_shipping_method === jckwds.vars.chosen_shipping_method ) {
					jckwds.unblock_checkout( true );
					return;
				}

				/**
				 * Re-cache the selected shipping method
				 */
				jckwds.vars.chosen_shipping_method = data.fragments.iconic_wds.chosen_shipping_method.toString();

				/**
				 * Toggle and update fields. Then refresh datepicker and time slots if delivery
				 * date fields are allowed.
				 */
				jckwds.toggle_date_time_fields( function( fields_allowed, data ) {
					if ( ! fields_allowed ) {
						jckwds.unblock_checkout( data.index );
						return;
					}

					jckwds.refresh_datepicker( data.bookable_dates, function() {
						jckwds.refresh_timeslots( true );
					} );

					jckwds.unblock_checkout( data.index );
				} );
			} );

			if ( jckwds.els.checkout_fields.hasClass( jckwds.vars.inactive_class ) ) {
				jckwds.hide_date_time_fields();
			}
		},

		/**
		 * Block the checkout.
		 */
		block_checkout: function() {
			/**
			 * Start indexing block checkout requests
			 * so we know when to unblock.
			 */
			jckwds.vars.block_checkout_index++;

			jckwds.els.checkout_fields.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			$( '#place_order' ).attr( 'disabled', 'disabled' );
		},

		/**
		 * Unblock the checkout.
		 *
		 * @param bool|int index If true or the int matches the block_checkout_index, the checkout will be unblocked.
		 */
		unblock_checkout: function ( index ) {
			if ( true !== index && index !== jckwds.vars.block_checkout_index ) {
				return;
			}

			jckwds.els.checkout_fields.unblock( { fadeOut: 0 } );
			$( '#place_order' ).removeAttr( 'disabled' );
		},

		/**
		 * Update checkout field labels.
		 *
		 * @param labels
		 */
		update_checkout_field_labels: function( labels ) {
			if ( labels.length <= 0 ) {
				return;
			}

			var elements = {
				details: $( '.iconic-wds-fields__title' ),
				date: $( '.jckwds-delivery-date label' ),
				select_date: $( '#jckwds-delivery-date' ),
				choose_date: $( '#jckwds-delivery-date-description' ),
				time_slot: $( '.jckwds-delivery-time label' ),
				choose_time_slot: $( ' #jckwds-delivery-time-description' )
			};

			$.each( labels, function( index, label ) {
				if ( typeof elements[ index ] === 'undefined' ) {
					return true;
				}

				if ( elements[ index ].is( 'input' ) ) {
					elements[ index ].attr( 'placeholder', label );
					return true;
				}

				var $html = elements[ index ].find( '*' );

				elements[ index ].text( label );

				if ( $html.length > 0 ) {
					elements[ index ].append( '&nbsp;' ).append( $html );
				}
			} );
		},

		/**
		 * Refresh datepicker
		 *
		 * Fetch new bookable dates based on shipping method selected
		 * and update the cached bookable_dates variable. Then, refresh
		 * the datepicker
		 */
		refresh_datepicker: function( bookable_dates, callback ) {
			jckwds_vars.bookable_dates = bookable_dates;
			jckwds.els.date_picker
				.blur()
				.datepicker( 'hide' )
				.datepicker( 'option', 'defaultDate', bookable_dates[0] )
				.datepicker( 'option', 'minDate', bookable_dates[0] );

			/**
			 * Set date if one is reserved and it is bookable
			 */
			if ( jckwds_vars.reserved_slot ) {
				if ( $.inArray( jckwds_vars.reserved_slot.date.formatted, jckwds_vars.bookable_dates ) !== - 1 ) {
					jckwds.set_date( jckwds_vars.reserved_slot.date.formatted );
				}
			} else {
				var selected_date = jckwds.els.date_picker.val();
				jckwds.set_date( selected_date );
			}

			if ( typeof callback !== "undefined" ) {
				callback();
			}
		},

		/**
		 * Clear date and time fields
		 */
		clear_date_time_fields: function() {
			jckwds.clear_date();
			jckwds.clear_timeslots();
		},

		/**
		 * Checkout: Toggle date/time fields
		 */
		toggle_date_time_fields: function( callback ) {
			/**
			 * If the selected shipping method isn't allowed, no need to load
			 * anything more. Just hide the fields and return the callback.
			 */
			if ( $.inArray( 'any', jckwds_vars.settings.general_setup_shipping_methods ) < 0 && $.inArray( jckwds.vars.chosen_shipping_method, jckwds_vars.settings.general_setup_shipping_methods ) < 0 ) {
				jckwds.hide_date_time_fields();

				if ( typeof callback === 'function' ) {
					/**
					 * @param bool   allowed
					 * @param object data
					 */
					callback( false, { index: true } );
				}

				return;
			} else {
				jckwds.show_date_time_fields();
			}

			jckwds.is_delivery_slots_allowed( function( allowed, data ) {
				if ( allowed ) {
					jckwds.show_date_time_fields();
				} else {
					jckwds.hide_date_time_fields();
				}

				if ( typeof callback === 'function' ) {
					callback( allowed, data );
				}
			} );
		},

		/**
		 * Checkout: Hide date/time fields
		 */
		hide_date_time_fields: function() {
			jckwds.els.checkout_fields
				.removeClass( jckwds.vars.inactive_class )
				.removeClass( 'woocommerce-billing-fields' )
				.hide();

			jckwds.els.fields_hidden.val( 1 );

			jckwds.clear_date_time_fields();
		},

		/**
		 * Checkout: Show date/time fields
		 */
		show_date_time_fields: function() {
			jckwds.els.checkout_fields
				.addClass( 'woocommerce-billing-fields' )
				.show();

			jckwds.els.fields_hidden.val( 0 );
		},

		/**
		 * Check if delivery slots should be shown.
		 *
		 * @param callback
		 */
		is_delivery_slots_allowed: function( callback ) {
			var args = {
				action: 'iconic_wds_is_delivery_slots_allowed',
				index: jckwds.vars.block_checkout_index
			};

			$.post( jckwds_vars.ajax_url, args, function( response ) {
				/**
				 * If the index matches the request, run the callback. otherwise we wait.
				 */
				if ( typeof callback === 'function' ) {
					callback( response.success, response.data );
				}
			} );
		},

		/**
		 * Get last day of the week
		 *
		 * @return int
		 */
		get_last_day_of_the_week: function() {

			var days = {
				'monday': 1,
				'tuesday': 2,
				'wednesday': 3,
				'thursday': 4,
				'friday': 5,
				'saturday': 6,
				'sunday': 0
			};

			if ( typeof jckwds_vars.settings.datesettings_datesettings_last_day_of_week === "undefined" || typeof days[ jckwds_vars.settings.datesettings_datesettings_last_day_of_week ] === "undefined" ) {
				return 6;
			}

			return days[ jckwds_vars.settings.datesettings_datesettings_last_day_of_week ];

		},

		/**
		 * Get first day of the week
		 *
		 * @return int
		 */
		get_first_day_of_the_week: function() {

			var last_day = jckwds.get_last_day_of_the_week();

			if ( last_day === 6 ) {

				return 0;

			} else {

				return last_day + 1;

			}

		},

		/**
		 * Pad left
		 *
		 * @param int number
		 * @param int count
		 * @param str string
		 * @return str
		 */
		pad_left: function( number, count, string ) {
			return new Array( count - String( number ).length + 1 ).join( string || '0' ) + number;
		},

		/**
		 * Run on checkout error.
		 */
		checkout_error: function() {
			var $clear_date = $( '[data-iconic-wds-clear-date="1"]' ),
				$clear_time = $( '[data-iconic-wds-clear-time="1"]' );

			if ( $clear_date.length <= 0 && $clear_time.length <= 0 ) {
				return;
			}

			if ( $clear_date.length > 0 ) {
				jckwds.clear_date();
			}

			if ( $clear_time.length > 0 ) {
				jckwds.refresh_timeslots( true );
			}
		}

	};

	$( window ).load( jckwds.on_load );
	$( document.body ).on( 'checkout_error', jckwds.checkout_error );

}( jQuery, document ));