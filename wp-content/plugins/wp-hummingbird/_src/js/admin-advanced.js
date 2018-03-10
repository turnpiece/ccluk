import Fetcher from './utils/fetcher';

( function( $ ) {
	'use strict';
	WPHB_Admin.advanced = {

		module: 'advanced',

		init: function () {
			let self                = this,
				common_footer       = $('section[class^="box-advanced-"] .box-footer');

			/**
			 * Process form submit for advanced tools forms
			 */
			common_footer.on('click', '.button[type="submit"]', function (e) {
				e.preventDefault();

				const spinner = $(this).parent().find('.spinner');
				const settings_form = $(this).closest('section').find('form[id^="advanced-"]');

				spinner.addClass('visible');

				Fetcher.advanced.saveSettings( settings_form.serialize(), settings_form.attr('id') )
					.then( ( response ) => {
						spinner.removeClass('visible');

						if ( 'undefined' !== typeof response && response.success ) {
							self.showNotice( 'success' );
							// Schedule cleanup.
							Fetcher.advanced.scheduleCleanup();
						} else {
							self.showNotice( 'error', wphb.strings.errorSettingsUpdate );
						}
					});
			});

			/**
			 * Show/hide schedule for database cleanup.
			 */
			$('input[id="scheduled_cleanup"]').on('change', function () {
				$('.schedule-box').toggle();
			});

			return this;
		},

		/**
		 * Notice on settings update.
		 *
		 * @param type
		 * @param message
		 */
		showNotice: function ( type, message = wphb.strings.successUpdate ) {
			const notice = $('#wphb-notice-advanced-tools');

			// Remove set classes if doing multiple calls per page load.
			notice.removeClass('wphb-notice-error');
			notice.removeClass('wphb-notice-success');

			window.scrollTo( 0, 0 );
			notice.addClass('wphb-notice-' + type);

			notice.find('p').html(message);

			notice.slideDown();
			setTimeout( function() {
				notice.slideUp();
			}, 5000 );
		},

		/**
		 * Show the modal window asking if a user is sure he wants to delete the db records.
		 *
		 * @param items Number of records to delete.
		 * @param type  Data type to delete from db (See data-type element for each row in the code).
		 */
		showModal: function ( items, type ) {
			const dialog = wphb.strings.db_delete + ' ' + items + ' ' + wphb.strings.db_entries + '? ' + wphb.strings.db_backup;
			const modal = $('.wphb-database-cleanup-modal');

			modal.find( 'p' ).html( dialog );
			modal.find( '.button-grey' ).attr( 'data-type', type );

			window.WDP.showOverlay("#wphb-database-cleanup-modal", { class: 'wphb-modal small wphb-database-cleanup-modal no-close' } );
		},

		/**
		 * Process database cleanup (both individual and all entries).
		 *
		 * @param type Data type to delete from db (See data-type element for each row in the code).
		 */
		confirmDelete: function ( type ) {
			window.WDP.closeOverlay("#wphb-database-cleanup-modal");

			let row;

			if ( 'all' === type ) {
				row = $('.box-advanced-db .table-footer');
			} else {
				row = $('.box-advanced-db .wphb-border-frame').find('div[data-type=' + type + ']');
			}

			const spinner = row.find('.spinner');

			spinner.addClass('visible');

			Fetcher.advanced.deleteSelectedData( type )
				.then( ( response ) => {
					this.showNotice( 'success', response.message );
					spinner.removeClass('visible');

					for ( let prop in response.left ) {
						if ( 'total' === prop ) {
							$('.box-advanced-db .table-footer .wphb-db-delete-all')
								.html( wphb.strings.deleteAll + ' (' + response.left[prop] + ')' );
						} else {
							$('.box-advanced-db div[data-type=' + prop + '] > .wphb-db-items')
								.html( response.left[prop] );
						}
					}
				})
				.catch( ( error ) => {
					this.showNotice( 'error', error );
					spinner.removeClass('visible');
				});
		}
	}
}( jQuery ));