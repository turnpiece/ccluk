/**
 * Give Admin Recurring JS
 *
 * Scripts function in admin form creation (single give_forms post) screen.
 */
var Give_Recurring_Vars;


jQuery( document ).ready( function( $ ) {

	var Give_Admin_Recurring = {

		/**
		 * Initialize.
		 */
		init: function() {

			Give_Admin_Recurring.recurring_option = $( 'input[name="_give_recurring"]' );

			Give_Admin_Recurring.set_multi_level_recurring_limits();
			Give_Admin_Recurring.toggle_set_recurring_fields();
			Give_Admin_Recurring.toggle_multi_recurring_fields();
			Give_Admin_Recurring.validate_times();
			Give_Admin_Recurring.validate_period();
			Give_Admin_Recurring.detect_email_access();
			Give_Admin_Recurring.toggle_default_period();
			Give_Admin_Recurring.set_custom_amount_recurring_limits();
			Give_Admin_Recurring.set_recurring_limits();
			Give_Admin_Recurring.toggle_custom_amount_fields();

			// Set or Multi toggle options reveal.
			$( 'input[name="_give_price_option"], input[name="_give_recurring"]' ).on( 'change', function() {

				// Call Recurring option js.
				Give_Admin_Recurring.set_recurring_limits();

				var set_or_multi = $( 'input[name="_give_price_option"]:checked' ).val();

				if ( 'set' === set_or_multi ) {
					Give_Admin_Recurring.toggle_set_recurring_fields();
				} else {
					Give_Admin_Recurring.toggle_multi_recurring_fields();
				}

			});

			// Multi level Custom Amount Recurring Support.
			Give_Admin_Recurring.toggle_custom_amount_period();

			// Toggle repeatable admin choice recurring fields.
			$( 'body' ).on( 'change', '.give-recurring-admin-choice-level', function() {
				Give_Admin_Recurring.toggle_admin_choice_fields( $( this ) );
			});

			// Update subscription ranges when subscription period or interval is changed.
			$( 'select[name="_give_period_interval"], select[name="_give_period"],select[name="_give_period_default_donor_choice"], input[name="_give_period_functionality"]' ).on( 'change', function() {

					var $current_value = $( this ).val();

				Give_Admin_Recurring.set_recurring_limits( $current_value );
			});

			// Update subscription ranges when subscription period or interval is changed.
			$( 'body' ).on( 'change', '.give-inline-row-period select, .give-inline-row-interval select', function() {
				var $this =  $( this ),
					$current_value = $this.val();
				Give_Admin_Recurring.set_multi_level_recurring_limits( $this, $current_value, 'change' );
			});

			// Set recurring options on recurring limit.
			$( '.give-add-repeater-field-section-row' ).on( 'click', function() {

				var $this =  $( this );
				setTimeout( function() {
						var $multi_give_times = $this.closest( 'table' ).find( ' tbody tr:last-child' );
					Give_Admin_Recurring.set_multi_level_recurring_limits( $multi_give_times, 'month', 'add' );
				}, 100 );
			});

		},

		/**
		 * toggle custom amount recurring options.
		 * @since 1.6.0
		 */
		toggle_custom_amount_fields: function() {
			var set_or_multi = $( 'input[name="_give_price_option"]:checked' ).val(),
				recurring = $( 'input[name="_give_recurring"]:checked' ).val(),
				custom_amount = $( 'input[name="_give_custom_amount"]:checked' ).val(),
				$custom_period = $( 'select[name="_give_recurring_custom_amount_period"]' ),
				$custom_period_val = $custom_period.val();

			if ( 'once' === $custom_period_val ) {
				$custom_period.parent().addClass( 'give-control-select-onetime' );
			}

			if (
				'multi' === set_or_multi &&
				'yes_admin' === recurring &&
				'enabled' === custom_amount
			) {

				Give_Admin_Recurring.set_custom_amount_recurring_limits();

				$( '.give_custom_recurring_all_options' ).show();
				$( '.give-recurring-custom-amount-period' ).show();
				if ( 'once' !== $( '#_give_recurring_custom_amount_period' ).val() ) {
					$( '.give-recurring-custom-amount-times' ).show();
					$( '._give_recurring_custom_amount_interval_field' ).show();
				}
			} else {
				$( '.give_custom_recurring_all_options' ).hide();
				$( '.give-recurring-custom-amount-period' ).hide();
				$( '.give-recurring-custom-amount-times' ).hide();
				$( '._give_recurring_custom_amount_interval_field' ).hide();
			}
		},

		/**
		 * Show recurring option based on period and set limit for single-level donation.
		 * @since 1.6.0
		 */
		set_recurring_limits: function( $current_value ) {
			$( '[name^="_give_times"]' ).each( function() {
				var $lengthElement = $( this ),
					hasSelectedLength = false,
					periodSelector,
					recurring_period = $( 'input[name="_give_period_functionality"]:checked' ).val(),
					recurring_donation = $( 'input[name="_give_recurring"]:checked' ).val(),
					billingInterval = parseInt( $( '#_give_period_interval' ).val() );

				if ( 'donors_choice' === recurring_period && 'yes_donor' === recurring_donation ) {
					periodSelector = '#_give_period_default_donor_choice';
				} else {
					periodSelector = '#_give_period';
				}

				$lengthElement.empty();

				var selected_value = $( periodSelector ).val();
				if ( 0 <= $.inArray( $current_value, [ 'day', 'week', 'month', 'quarter', 'year' ]) ) {
					selected_value = $current_value;
				}

				$.each( Give_Recurring_Vars.billingLimits[ selected_value ], function( length, description ) {
					if ( 0 === parseInt( length ) || ( ( 0 === ( parseInt( length ) % billingInterval ) ) && ( parseInt( length ) !== billingInterval ) ) ) {
						$lengthElement.append( $( '<option></option>' ).attr( 'value', length ).text( description ) );
					}
				});

				$lengthElement.children( 'option' ).each( function() {
					if ( this.value === Give_Recurring_Vars.selected_billing_limit ) {
						hasSelectedLength = true;
						return false;
					}
				});

				$lengthElement.val( 0 );
				if ( hasSelectedLength ) {
					$lengthElement.val( Give_Recurring_Vars.selected_billing_limit );
				}

			});
		},

		/**
		 * Show recurring option based on period and set limit for multi-level donation.
		 * @since 1.6.0
		 */
		set_multi_level_recurring_limits: function( $current_obj, $current_value, $action ) {

			var $multi_give_times;

			if ( 'add' !== $action && 'change' !== $action ) {
				$multi_give_times = $( '#_give_donation_levels_field table.give-repeatable-fields-section-wrapper tbody tr.give-row' ).not( '.give-template' );
			} else {
				$multi_give_times = $current_obj;
			}

			if ( 'change' === $action && 'undefined' !== $.type( $current_obj ) ) {
				$multi_give_times = $current_obj.parent().closest( 'tr' );
			}

			$multi_give_times.each( function( index ) {
				var $this = $( this ),
					$lengthElement = $this.find( '.give-inline-row-limit select' ),
					hasSelectedLength = false,
					matches = $lengthElement.attr( 'name' ).match( /\[(.*?)\]/ ),
					periodSelector,
					multilevel_billing_limit = $.parseJSON( Give_Recurring_Vars.selected_multilevel_billing_limit ),
					billingInterval = parseInt( $( '#_give_period_interval' ).val() );

				if ( ! matches ) { // Variation.
					return false;
				}

				periodSelector = '[name="_give_donation_levels[' + matches[ 1 ] + '][_give_period]"]';
				billingInterval = parseInt( $( '[name="_give_donation_levels[' + matches[ 1 ] + '][_give_period_interval]"]' ).val() );

				$lengthElement.empty();

				var selected_value = $( periodSelector ).val();
				if ( 0 <= $.inArray( $current_value, [ 'day', 'week', 'month', 'year' ]) ) {
					selected_value = $current_value;
				}

				$.each( Give_Recurring_Vars.billingLimits[ selected_value ], function( length, description ) {
					if ( 0 === parseInt( length ) || ( ( 0 === ( parseInt( length ) % billingInterval ) ) && ( parseInt( length ) !== billingInterval ) ) ) {
						$lengthElement.append( $( '<option></option>' ).attr( 'value', length ).text( description ) );
					}
				});

				var multilevel_billing_limit_val = 0;
				if (  'add' !== $action && 'change' !== $action && '' !== multilevel_billing_limit[index]) {
					multilevel_billing_limit_val = multilevel_billing_limit[index];
				}

				$lengthElement.children( 'option' ).each( function() {
					if ( this.value === multilevel_billing_limit_val ) {
						hasSelectedLength = true;
						return false;
					}
				});

				$lengthElement.val( 0 );
				if ( hasSelectedLength ) {
					$lengthElement.val( multilevel_billing_limit_val );
				}

			});
		},

		/**
		 * Show recurring option based on period and set limit for custom amount.
		 * @since 1.6.0
		 */
		set_custom_amount_recurring_limits: function( $current_value ) {
			$( '[name^="_give_recurring_custom_amount_times"]' ).each( function() {
				var $lengthElement = $( this ),
					hasSelectedLength = false,
					periodSelector = '#_give_recurring_custom_amount_period',
					billingInterval = parseInt( $( '#_give_recurring_custom_amount_interval' ).val() );

				$lengthElement.empty();

				var selected_value = $( periodSelector ).val();
				if ( 0 <= $.inArray( $current_value, [ 'day', 'week', 'month', 'year' ]) ) {
					selected_value = $current_value;
				}

				$.each( Give_Recurring_Vars.billingLimits[ selected_value ], function( length, description ) {
					if ( 0 === parseInt( length ) || ( ( 0 === ( parseInt( length ) % billingInterval ) ) && ( parseInt( length ) !== billingInterval ) ) ) {
						$lengthElement.append( $( '<option></option>' ).attr( 'value', length ).text( description ) );
					}
				});

				$lengthElement.children( 'option' ).each( function() {
					if ( this.value === Give_Recurring_Vars.selected_custom_amount_billing_limit ) {
						hasSelectedLength = true;
						return false;
					}
				});

				$lengthElement.val( 0 );
				if ( hasSelectedLength ) {
					$lengthElement.val( Give_Recurring_Vars.selected_custom_amount_billing_limit );
				}

			});
		},

		/**
		 * Toggle Custom Amount Recurring Period
		 *
		 * @since 1.5.6
		 */
		toggle_custom_amount_period: function() {


			$( 'input[name="_give_price_option"], input[name="_give_custom_amount"], input[name="_give_recurring"]' ).on( 'change', function() {

				Give_Admin_Recurring.toggle_custom_amount_fields();
			});

			// Set recurring limit based on custom amount period.
			$( '#_give_recurring_custom_amount_period' ).on( 'change', function() {

				var $this =  $( this ),
					$current_value = $this.val();

				if ( 'once' !== $current_value ) {

					Give_Admin_Recurring.set_custom_amount_recurring_limits( $current_value );

					$( '.give-recurring-custom-amount-times' ).show();
					$( '._give_recurring_custom_amount_interval_field' ).show();
					$this.parent().removeClass( 'give-control-select-onetime' );
				} else {
					$( '.give-recurring-custom-amount-times' ).hide();
					$( '._give_recurring_custom_amount_interval_field' ).hide();
					$this.parent().addClass( 'give-control-select-onetime' );
				}
			});

			// Set recurring limit based on custom amount interval.
			$( '#_give_recurring_custom_amount_interval' ).on( 'change', function() {

				var $this =  $( this ),
					$current_value = $this.val();

				Give_Admin_Recurring.set_custom_amount_recurring_limits( $current_value );

			});

		},

		/**
		 * Toggle Set Recurring Fields.
		 *
		 * Hides and shows Recurring fields for the "Set" donation option.
		 *
		 * @since 1.4.3
		 */
		toggle_default_period: function() {

			// Set or Multi toggle options reveal.
			$( 'input[name="_give_period_functionality"]' ).on( 'change', function() {

				var period_functionality = $( this ).filter( ':checked' ).val();

				if ( 'donors_choice' === period_functionality ) {
					$( '.give-recurring-period' ).hide();
					$( '.give-recurring-period-default-choice' ).show();
				} else if ( 'admin_choice' === period_functionality ) {
					$( '.give-recurring-period' ).show();
					$( '.give-recurring-period-default-choice' ).hide();
				}

			}).change();

		},

		/**
		 * Toggle Set Recurring Fields.
		 *
		 * Hides and shows Recurring fields for the "Set" donation option.
		 */
		toggle_set_recurring_fields: function() {

			var recurring_option = Give_Admin_Recurring.recurring_option.filter( ':checked' ).val(),
				set_or_multi = $( 'input[name="_give_price_option"]:checked' ).val();

			// Sanity check: ensure this is set
			if ( 'set' !== set_or_multi ) {
				return false;
			}

			if ( 'yes_admin' === recurring_option ) {

				// Show fields
				$( '.give-recurring-row.give-hidden' ).show();

				// Hide checkbox opt-in option field
				$( '.give-recurring-checkbox-option' ).hide();
				$( '.give-recurring-period-default-choice' ).hide();
				$( '.give-recurring-period-functionality' ).hide();

			} else if ( 'yes_donor' === recurring_option ) {

				// Show fields
				$( '.give-recurring-row.give-hidden' ).show();

				// Show checkbox opt-in option field
				$( '.give-recurring-checkbox-option' ).show();
				$( '.give-recurring-period-default-choice' ).show();
				$( '.give-recurring-period-functionality' ).show();

				Give_Admin_Recurring.toggle_default_period();

			} else {
				$( '.give-recurring-row.give-hidden' ).hide();
			}

		},

		/**
		 * Toggle Multi-Level Recurring Fields
		 *
		 * Hides and shows Recurring fields for the "Multi-level" donation option.
		 */
		toggle_multi_recurring_fields: function() {

			var set_or_multi = $( 'input[name="_give_price_option"]:checked' ).val(),
				recurring_option = Give_Admin_Recurring.recurring_option.filter( ':checked' ).val();

			// Sanity check: ensure this is multi
			if ( 'multi' !== set_or_multi ) {
				return false;
			}

			if ( 'yes_admin' === recurring_option ) {

				// Hide donor-recurring settings fields
				$( '.give-recurring-row.give-hidden' ).hide();

				// Show admin-recurring settings fields
				$( '.give-recurring-multi-el' ).show();

				$( '.give-recurring-admin-choice-level-row' ).each( function() {
					Give_Admin_Recurring.toggle_admin_choice_fields( $( this ).find( '.give-recurring-admin-choice-level' ) );
				});

			} else if ( 'yes_donor' === recurring_option ) {

				$( '.give-recurring-row.give-hidden' ).show();
				$( '.give-recurring-multi-el' ).hide();

				Give_Admin_Recurring.toggle_default_period();

			} else if ( 'no' === recurring_option ) {

				$( '.give-recurring-row.give-hidden' ).hide();
				$( '.give-recurring-multi-el' ).hide();

			}

		},

		/**
		 * Toggle admin choice recurring fields.
		 *
		 * Activates and deactivates the admin choice recurring fields based on the user's selections.
		 *
		 * @param $this
		 */
		toggle_admin_choice_fields: function( $this ) {

			var val = $this.filter( ':checked' ).val(),
				$fields = $this.parents( '.give-row-body' ).find( '[name$="[_give_period]"], [name$="[_give_times]"], [name$="[_give_period_interval]"]' );

			if ( 'yes' === val ) {
				$fields.parent( '.give-field-wrap' ).show();
			} else {
				$fields.parent( '.give-field-wrap' ).hide();
			}

		},

		/**
		 * Validate Times
		 *
		 * Used for client side validation of times set for various recurring gateways.
		 */
		validate_times: function() {

			var recurring_times = $( '.give-billing-time-field' );

			// Validate times on times input blur (client side then server side)
			recurring_times.on( 'change', function() {

				var time_val = $( this ).val();
				var recurring_option = Give_Admin_Recurring.recurring_option.filter( ':checked' ).val();

				// Verify this is recurring.
				// Sanity check: only validate if recurring is set to Yes
				if ( 'no' === recurring_option ) {
					return false;
				}

				// Check if PayPal Standard is set & Validate times are over 1
				if ( 'undefined' !== typeof Give_Recurring_Vars.enabled_gateways.paypal && 1 === time_val ) {

					// Alert user of issue
					alert( Give_Recurring_Vars.invalid_time.paypal );

					// Change to a valid value
					$( this ).val( '2' );

					// Refocus on the faulty input
					$( this ).focus();

				}

			});

		},

		/**
		 * Validate Period
		 *
		 * Used for client side validation of period set for various recurring gateways.
		 */
		validate_period: function() {

			var recurring_period = $( '.give-period-field' );

			// Validate times on times input blur (client side then server side)
			recurring_period.on( 'change', function() {

				var period_val = $( this ).val();
				var recurring_option = Give_Admin_Recurring.recurring_option.filter( ':checked' ).val();

				// Verify this is a recurring first
				// Sanity check: only validate if recurring is set to Yes
				if ( 'no' === recurring_option ) {
					return false;
				}

				if ( 'day' === period_val ) {

					if ( 'undefined' !== typeof Give_Recurring_Vars.enabled_gateways.authorize ) {

						// Alert user of issue
						alert( Give_Recurring_Vars.invalid_period.authorize );

						// Change to a valid value
						$( this ).val( 'month' );

					} else if ( 'undefined' !== typeof Give_Recurring_Vars.enabled_gateways.wepay ) {

						// Alert user of issue
						alert( Give_Recurring_Vars.invalid_period.wepay );

						// Change to a valid value
						$( this ).val( 'month' );

					} else if ( 'undefined' !== typeof Give_Recurring_Vars.enabled_gateways.gocardless ) {

						// Alert user of issue
						alert( Give_Recurring_Vars.invalid_period.gocardless );

						// Change to a valid value
						$( this ).val( 'month' );
					}

					// Refocus on the faulty input
					$( this ).focus();

				}

			});

		},

		/**
		 * Detect Email Access
		 *
		 * Is Email Best Access on? If not, display message and hide register/login fields.
		 *
		 * @since: v1.1
		 */
		detect_email_access: function() {

			var recurring_option = Give_Admin_Recurring.recurring_option.filter( ':checked' ).val();

			// Email Access Not Enabled & Recurring Enabled.
			if (
				! Give_Recurring_Vars.email_access &&
				'no' !== recurring_option
			) {
				Give_Admin_Recurring.toggle_login_message( 'on' );
			}

			Give_Admin_Recurring.recurring_option.on( 'change', function() {

				var this_val = $( this ).val();

				if ( 'no' !== this_val ) {
					Give_Admin_Recurring.toggle_login_message( 'on' );
				} else {
					Give_Admin_Recurring.toggle_login_message( 'off' );
				}

			});

		},

		/**
		 * Toggle a message that form login is required.
		 *
		 * @param toggle_state
		 */
		toggle_login_message: function( toggle_state ) {

			var register_field = $( '._give_show_register_form_field' ),
				login_req_msg = $( '.give-recurring-login-required' );

			if (
				'on' === toggle_state &&
				0 === login_req_msg.length
			) {

				// Add class for styles
				register_field.addClass( 'recurring-email-access-message' );

				// Prepend message
				register_field.before( Give_Recurring_Vars.messages.login_required );

			} else if ( 'off' === toggle_state ) {

				// Add class for styles
				register_field.removeClass( 'recurring-email-access-message' );

				// Prepend message
				login_req_msg.remove();
			}

		}

	};

	Give_Admin_Recurring.init();

});
