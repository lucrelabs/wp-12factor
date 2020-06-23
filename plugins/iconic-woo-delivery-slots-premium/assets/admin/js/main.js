(function( $, document ) {

	var iconic_wds = {
		cache: function() {
			iconic_wds.els = {
				date_picker: $( '#jckwds-delivery-date' ),
				date_ymd: $( '#jckwds-delivery-date-ymd' ),
				timeslot_select: $( '#jckwds-delivery-time' ),
				date_changed: $( '#jckwds-date-changed' )
			};
			iconic_wds.vars = {};
		},

		on_load: function() {
			iconic_wds.cache();

			iconic_wds.setup_date_picker();
			iconic_wds.watch_for_change();
			iconic_wds.watch_for_submit();
		},

		/**
		 * Setup date picker.
		 */
		setup_date_picker: function() {
			if ( iconic_wds.els.date_picker.length <= 0 ) {
				return;
			}

			iconic_wds.els.date_picker.datepicker( {
				minDate: "+" + iconic_wds_vars.settings.datesettings_datesettings_minimum + "D",
				maxDate: "+" + iconic_wds_vars.settings.datesettings_datesettings_maximum + "D",
				beforeShowDay: function( date ) {
					var formatted_date = $.datepicker.formatDate( 'yy-mm-dd', date );

					if ( $.inArray( formatted_date, iconic_wds_vars.bookable_dates ) !== - 1 ) {
						return [ true, "", "Available" ];
					} else {
						return [ false, "", "unAvailable" ];
					}
				},
				dateFormat: iconic_wds_vars.settings.datesettings_datesettings_dateformat,
				onSelect: function( dateText, inst ) {
					$( this ).trigger( 'change' );

					if ( this.value === "" ) {
						return;
					}

					var selected_year = iconic_wds.pad_left( inst.selectedYear, 4 ),
						selected_month = iconic_wds.pad_left( inst.selectedMonth + 1, 2 ),
						selected_day = iconic_wds.pad_left( inst.selectedDay, 2 ),
						selected_date_ymd = [ selected_year, selected_month, selected_day ].join( '' );

					/* Add selected date to hidden date ymd field for processing */
					iconic_wds.els.date_ymd.val( selected_date_ymd );

					// if time slots are enabled
					if ( iconic_wds_vars.settings.timesettings_timesettings_setup_enable ) {
						iconic_wds.update_timeslot_options( selected_date_ymd );
					}
				},
				monthNames: iconic_wds_vars.strings.months,
				monthNamesShort: iconic_wds_vars.strings.months_short,
				dayNames: iconic_wds_vars.strings.days,
				dayNamesMin: iconic_wds_vars.strings.days_short,
				//firstDay: iconic_wds.get_first_day_of_the_week()
			} );
		},

		/**
		 * Helper: Get all timeslots available on a specific date,
		 *         and update the timeslots dropdown
		 *
		 * @param [str] [date] [format?]
		 * @param [func] [callback]
		 */
		update_timeslot_options: function( date, callback ) {
			var $first_timeslot_option = iconic_wds.els.timeslot_select.find( "option:eq(0)" );

			iconic_wds.els.timeslot_select.find( "option:gt(0)" ).remove();
			$first_timeslot_option.text( iconic_wds_vars.strings.loading );

			var ajaxData = {
				action: 'iconic_wds_get_slots_on_date',
				nonce: iconic_wds_vars.ajax_nonce,
				date: date
			};

			$.post( iconic_wds_vars.ajax_url + '?post=' + iconic_wds_vars.order_id, ajaxData, function( response ) {
				if ( response.success === true ) {
					$first_timeslot_option.text( iconic_wds_vars.strings.selectslot );
					iconic_wds.els.timeslot_select.append( response.html );
				} else {
					$first_timeslot_option.text( iconic_wds_vars.strings.noslots );
				}

				if ( callback !== undefined ) {
					callback( response );
				}
			} );
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
		 * Watch for delivery date/time change.
		 */
		watch_for_change: function() {
			var $fields = iconic_wds.els.date_picker
				.add( iconic_wds.els.date_ymd )
				.add( iconic_wds.els.timeslot_select );

			$fields.on( 'change', function() {
				iconic_wds.els.date_changed.val( 1 );
			} );
		},

		/**
		 * Watch for submit and warn if no time selected.
		 */
		watch_for_submit: function() {
			if ( iconic_wds.els.timeslot_select.length <= 0 ) {
				return;
			}

			$( "form#post" ).submit( function( event ) {
				var date_changed = parseInt( iconic_wds.els.date_changed.val() ),
					timeslot_value = iconic_wds.els.timeslot_select.val(),
					slot_required = parseInt( iconic_wds_vars.settings.timesettings_timesettings_setup_mandatory ) === 1;

				if( slot_required && date_changed && timeslot_value.length <= 0 ) {
					alert( iconic_wds_vars.strings.selectslot );
					event.preventDefault();
				}
			} );
		}
	};

	$( window ).load( iconic_wds.on_load );

}( jQuery, document ));