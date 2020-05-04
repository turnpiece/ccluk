/**
 * Give Admin Recurring Settings JS
 *
 * ]Scripts functions in the admin Recurring Donations tab.
 */
/* global jQuery */
var Give_Recurring_Vars;

jQuery( document ).ready( function( $ ) {

	var Give_Admin_Recurring_Settings = {
		/**
		 * Initialize
		 */
		init: function() {

			var toggles = $( '.recurring-setting-subfield-toggle' );

			$( '#excluded_gateways' ).chosen({
				width: '25em',
            });

			toggles.each( function() {
				Give_Admin_Recurring_Settings.toggle_fields( $( this ) );
			} );

			toggles.on( 'change click', function() {
				Give_Admin_Recurring_Settings.toggle_fields( $( this ) );
			} );

			this.saveButtonTriggered();
			this.toggleSubscriptionReminders();
			this.showHideOnReminders();
			this.showHideOnNoticeState();
		},

		/**
		 * Toggle Set Recurring Fields
		 *
		 * @description:
		 */
		toggle_fields: function( toggle_el ) {

			var val = false;
			if ( toggle_el.is( ':checked' ) ) {
				val = toggle_el.val();
			}

			if ( false === val ) {
				return false;
			}

			var rows_to_toggle = toggle_el.parents( '.give-recurring-enable-row' ).nextUntil( '.give-recurring-enable-row' );

			if ( 'enabled' === val ) {
				rows_to_toggle.css( 'display', 'table-row' );
			} else {
				rows_to_toggle.hide();
			}

		},


		/**
		 * Unbinds the beforeunload event on the wondow object.
		 */
		saveButtonTriggered: function () {
			$('.give-settings-setting-page').on('click', '.subscription-options-save-button', function () {
				$(window).unbind('beforeunload');
			});
		},


		/**
		 * 
		 */
		toggleSubscriptionReminders: function() {

			$( '.give-subscriber-notifications .give-email-notification-status' ).on( 'click', function() {

				let button = $( this ),
				    notice_id = button.data( 'id' ),
				    status    = button.attr( 'data-status' ),
				    icon      = button.find( '.dashicons' ),
				    spinner   = button.next( '.spinner' );

				let active    = button.hasClass( 'give-email-notification-enabled' ) && ( 'enabled' === status );

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						'status': status,
						'notice_id': notice_id,
						'action': 'give_toggle_reminder',
						'nonce' : Give_Recurring_Vars.email_notice_nonce,
					},
					beforeSend: function(){
						button.hide();
						spinner.addClass('is-active');
					},
				}).done( function( response ) {

					if ( true !== response.success ) {
						return;
					}

					if ( active ) {
						button
							.removeClass( 'give-email-notification-enabled' )
							.addClass( 'give-email-notification-disabled' )
							.attr( 'data-status', 'disabled' );

						icon
							.removeClass( 'dashicons-yes' )
							.addClass( 'dashicons-no-alt' )

						button.show();
						spinner.removeClass('is-active');

					} else {
						button
							.removeClass( 'give-email-notification-disabled' )
							.addClass( 'give-email-notification-enabled' )
							.attr( 'data-status', 'enabled' );

						icon
							.removeClass( 'dashicons-no-alt' )
							.addClass( 'dashicons-yes' )

						button.show();
						spinner.removeClass('is-active');
					}
				})
			});
		},

		/**
		 * Show/Hide rows if Reminders are enabled/disabled.
		 */
		showHideOnReminders: function() {
			$( 'input[name="recurring_send_renewal_reminders"], input[name="recurring_send_expiration_reminders"]' ).on( 'change', function() {
				let button = $( this );

				let tr = button.closest( 'tr' ).next( 'tr' );

				// let value = button.filter( 'input:checked' ).val();
				let value = $( `input[name="${button.get(0).name}"]:checked` ).val();

				if ( 'disabled' === value ) {
					tr.hide();
				} else if ( 'enabled' === value ) {
					tr.show();
				}
			}).change();
		},

		/**
		 * Show/Hide individual email notice settings if email notice is enabled/disabled.
		 */
		showHideOnNoticeState: function() {
			let custom_save  = $( 'input[name="custom-save-button"]' ),
			    notice_state = $( 'input[name="save-notice-state-button"]' );

			$( 'input[name="email_notice_state"]' ).on( 'change', function() {
				let button = $( this );

				let tr = button.closest( 'tr' ).siblings( 'tr' ).not( ':last-child' );

				let value = $( 'input[name="email_notice_state"]:checked' ).val();

				if ( 'disabled' === value ) {
					tr.hide();
					custom_save.hide();
					notice_state.show();
				} else if ( 'enabled' === value ) {
					tr.show();
					custom_save.show();
					notice_state.hide();
				}
			}).change();
		},
	};

	Give_Admin_Recurring_Settings.init();
} );
