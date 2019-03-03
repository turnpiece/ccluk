import Fetcher from './utils/fetcher';

( function( $ ) {
	'use strict';
	WPHB_Admin.advanced = {
		module: 'advanced',

		init: function () {
			let self                  = this,
				common_form           = $('form[id="advanced-db-settings"], form[id="advanced-general-settings"]'),
				system_info_dropdown  = $('#wphb-system-info-dropdown'),
                hash                    = window.location.hash,
				delete_entries        = $('#wphb-db-delete-all, #wphb-db-row-delete');

			/**
			 * Process form submit for advanced tools forms
			 */
			delete_entries.on('click', function (e) {
				e.preventDefault();
				self.showModal( $(this).attr('data-entries'), $(this).attr('data-type') );
			});

			/**
			 * Process form submit for advanced tools forms
			 */
			common_form.on('submit', function(e) {
				e.preventDefault();

				const spinner = $(this).parent().find('.sui-icon-loader');
				spinner.removeClass('sui-hidden');

				Fetcher.advanced.saveSettings( $(this).serialize(), $(this).attr('id') )
					.then( ( response ) => {
						spinner.addClass('sui-hidden');

						if ( 'undefined' !== typeof response && response.success ) {
							// Schedule cleanup.
							if ( 'advanced-db-settings' === $(this).attr('id') ) {
								Fetcher.advanced.scheduleCleanup();
							}
							WPHB_Admin.notices.show( 'wphb-notice-advanced-tools', true, 'success' );
						} else {
							WPHB_Admin.notices.show( 'wphb-notice-advanced-tools', true, 'error', wphb.strings.errorSettingsUpdate  );
						}
					});
			});

			/**
			 * Show/hide schedule for database cleanup.
			 */
			$('input[id="scheduled_cleanup"]').on('change', function () {
				$('.schedule-box').toggle();
			});

            /**
             * Show initial system information table.
             */
            $('#wphb-system-info-php').removeClass('sui-hidden');
            if ( hash ) {
            	const system = hash.replace('#','');
                $('.wphb-sys-info-table').addClass('sui-hidden');
                $('#wphb-system-info-' + system).removeClass('sui-hidden');
                system_info_dropdown.val(system).trigger('sui:change');
			}

            /**
             * Show/hide system information tables on dropdown change.
             */
            system_info_dropdown.change( function(e) {
            	e.preventDefault();
            	$('.wphb-sys-info-table').addClass('sui-hidden');
                $('#wphb-system-info-' + $(this).val()).removeClass('sui-hidden');
                location.hash = $(this).val();
            });

			/**
			 * Paste default values to url strings option.
			 *
			 * @since 1.9.0
			 */
			$('#wphb-adv-paste-value').on('click', function(e) {
            	e.preventDefault();
            	let url_strings = $('textarea[name="url_strings"]');
            	if ( '' === url_strings.val() ) {
					url_strings.val( url_strings.attr('placeholder') );
				} else {
					url_strings.val( url_strings.val() + '\n' + url_strings.attr('placeholder') );
				}
			});

			return this;
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
			modal.find( '.wphb-delete-db-row' ).attr( 'data-type', type );

            SUI.dialogs["wphb-database-cleanup-modal"].show();
		},

		/**
		 * Process database cleanup (both individual and all entries).
		 *
		 * @param type Data type to delete from db (See data-type element for each row in the code).
		 */
		confirmDelete: function ( type ) {
            SUI.dialogs["wphb-database-cleanup-modal"].hide();

			let row;
			let footer = $('.box-advanced-db .sui-box-footer');

			if ( 'all' === type ) {
				row = footer;
			} else {
				row = $('.box-advanced-db .wphb-border-frame').find('div[data-type=' + type + ']');
			}

			const spinner = row.find('.sui-icon-loader');
			const button = row.find('#wphb-db-row-delete');
			console.log(button);

			spinner.removeClass('sui-hidden');
            button.addClass('sui-hidden');

			Fetcher.advanced.deleteSelectedData( type )
				.then( ( response ) => {
                    WPHB_Admin.notices.show( 'wphb-notice-advanced-tools', false, 'success', response.message );
					spinner.addClass('sui-hidden');
                    button.removeClass('sui-hidden');

					for ( let prop in response.left ) {
						if ( 'total' === prop ) {
							let leftString = wphb.strings.deleteAll + ' (' + response.left[prop] + ')';
							footer.find('.wphb-db-delete-all').html( leftString );
							footer.find('#wphb-db-delete-all').attr( 'data-entries', response.left[prop] );
						} else {
							let itemRow = $('.box-advanced-db div[data-type=' + prop + ']');
							itemRow.find('.wphb-db-items').html( response.left[prop] );
							itemRow.find('#wphb-db-row-delete').attr( 'data-entries', response.left[prop] );
						}
					}
				})
				.catch( ( error ) => {
					WPHB_Admin.notices.show( 'wphb-notice-advanced-tools', false, 'error', error );
					spinner.addClass('sui-hidden');
				});
		}
	}
}( jQuery ));