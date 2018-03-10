import Fetcher from './utils/fetcher';
import { __, getLink } from './utils/helpers';
import Row from './minification/Row';
import RowsCollection from './minification/RowsCollection';
import Scanner from './minification/Scanner';

( function( $ ) {
    'use strict';

    WPHB_Admin.minification = {

		module: 'minification',
        $checkFilesButton: null,
        $checkFilesResultsContainer : null,
        checkURLSList: null,
        checkedURLS: 0,

        init: function() {
            const self = this;

            // Init files scanner
            this.scanner = new Scanner( wphb.minification.get.totalSteps, wphb.minification.get.currentScanStep );
            this.scanner.onFinishStep = this.updateProgressBar;
            this.scanner.onFinish = ( response ) => {
                this.updateProgressBar( 100 );

				Fetcher.minification
                    .toggleCDN( $( 'input#enable_cdn' ).is(':checked') )
                    .then( () => {
						window.location.href = getLink( 'minification' );
                    });
            };

            // Check files button
            this.$checkFilesButton = $( '#check-files' );

            if ( this.$checkFilesButton.length ) {
                this.$checkFilesButton.click( function( e ) {
                    e.preventDefault();
					window.WDP.showOverlay("#check-files-modal", { class: 'wphb-modal small wphb-progress-modal no-close' } );
                    $(this).attr('disabled', true);
                    self.updateProgressBar( self.scanner.getProgress() );
                    self.scanner.scan();
                });
            }

            // Cancel scan button
            $('body').on( 'click', '#cancel-minification-check', ( e ) => {
                e.preventDefault();
                this.updateProgressBar( 0, true );
				this.scanner.cancel()
                    .then( () => {
                        window.location.href = getLink( 'minification' );
                    });

            });

			// Track changes done to minification files.
			$(':input.toggle-checkbox').on('change', function() {
				$(this).toggleClass('changed');
				let changed = $('.wphb-minification-files').find('input.changed');

				if ( changed.length === 0 ) {
					$('.wphb-minification-changed-notice').slideUp('slow');
					$('input[type=submit]').addClass('disabled');
				} else {
					$('.wphb-minification-changed-notice').slideDown('slow');
					$('input[type=submit]').removeClass('disabled');
				}
			});

			// Enable/disable bulk update button.
			$(':input.wphb-minification-file-selector, :input.wphb-minification-bulk-file-selector').on('change', function() {
				$(this).toggleClass('changed');
				let changed = $('.wphb-minification-files').find('input.changed');
				let bulkUpdateButton = $('#bulk-update');

				if ( changed.length === 0 ) {
					bulkUpdateButton.removeClass('button-grey');
					bulkUpdateButton.addClass('button-notice disabled');
				} else {
					bulkUpdateButton.removeClass('button-notice disabled');
					bulkUpdateButton.addClass('button-grey');
				}
			});

            // Show warning before switching to advanced view
			let switchButtons = $('.box-title-basic > a.wphb-switch-button, #wphb-dismissable a.wphb-switch-button');
			switchButtons.on('click', function(e) {
                e.preventDefault();
				window.WDP.showOverlay("#wphb-advanced-minification-modal" );
				Fetcher.minification.toggleView( 'advanced' );
            });

            // Switch back to basic mode
			$('.box-title-advanced > a').on('click', function(e) {
				e.preventDefault();
				window.WDP.showOverlay("#wphb-basic-minification-modal" );
			});

            // Filter action button on Asset Optimization page
            $('#wphb-minification-filter-button').on('click', function(e) {
                e.preventDefault();
                $('.wphb-minification-filter').toggle('slow');
                $('#wphb-minification-filter-button').toggleClass('active');
            });

            // Discard changes button click
            $('.wphb-discard').click( function(e) {
                e.preventDefault();

                if ( confirm( __( 'discardAlert' ) ) ) {
                    location.reload();
                }
                return false;
            });

            // Enable discard button on any change
            $( '.wphb-enqueued-files input' ).on( 'change', function() {
                $('.wphb-discard').attr( 'disabled', false );
            });

            // CDN checkbox update status
			const checkboxes = $("input[type=checkbox][name=use_cdn]");
			checkboxes.change( function() {
                const cdn_value = $(this).is(':checked');

                // Handle two CDN checkboxes on Asset Optimization page
				checkboxes.each( function() {
					this.checked = cdn_value;
				});

				// Update CDN status
                Fetcher.minification.toggleCDN( cdn_value )
                    .then( () => {
						self.showNotice();
                    });
            });

			$("input[type=checkbox][name=debug_log]").change( function() {
				Fetcher.minification.toggleLog( $(this).is(':checked') )
					.then( () => {
						self.showNotice();
					});
			});

			/**
			 * Save critical css file
			 */
			$('#wphb-minification-tools-form').on('submit', function (e) {
				e.preventDefault();

				const spinner = $(this).find('.spinner');
				spinner.addClass('visible');

				Fetcher.minification.saveCriticalCss( $(this).serialize() )
					.then( ( response ) => {
						spinner.removeClass('visible');
						if ( 'undefined' !== typeof response && response.success ) {
							self.showNotice( 'success', response.message );
						} else {
							self.showNotice( 'error', response.message );
						}

					});
			});

			/**
             * Asset Optimization filters
			 * @type {RowsCollection|*}
			 */
			this.rowsCollection = new WPHB_Admin.minification.RowsCollection();

            const rows = $('.wphb-border-row');

            rows.each( function( index, row ) {
                let _row;
                if ( $(row).data('filter-secondary') ) {
                    _row = new WPHB_Admin.minification.Row( $(row), $(row).data('filter'), $(row).data('filter-secondary') );
                }
                else {
                    _row = new WPHB_Admin.minification.Row( $(row), $(row).data('filter') );
                }
                self.rowsCollection.push( _row );
            });

            // Filter search box
            $('#wphb-s').keyup( function() {
                self.rowsCollection.addFilter( $(this).val(), 'primary' );
                self.rowsCollection.applyFilters();
            });

            // Filter dropdown
            $('#wphb-secondary-filter').change( function() {
                self.rowsCollection.addFilter( $(this).val(), 'secondary' );
                self.rowsCollection.applyFilters();
            });

            // Refresh rows on any filter change
            $('.filter-toggles').change( function() {
                const element = $(this);
                const what = element.data('toggles');
                const value = element.prop( 'checked' );
                const visibleItems = self.rowsCollection.getVisibleItems();

                for ( let i in visibleItems ) {
                    visibleItems[i].change( what, value );
                }
            });

            // Files selectors
            const filesList = $('input.wphb-minification-file-selector');
            filesList.click( function() {
                const $this = $( this );
                const element = self.rowsCollection.getItemById( $this.data( 'type' ), $this.data( 'handle' ) );
                if ( ! element ) {
                    return;
                }

                if ( $this.is( ':checked' ) ) {
                    element.select();
                }
                else {
                    element.unSelect();
                }
            });

            // Handle select/deselect of all files of a certain type for use on bulk update
			const selectAll = $('.wphb-minification-bulk-file-selector');
			selectAll.click( function() {
				const $this = $( this );
				let items = self.rowsCollection.getItemsByDataType( $this.attr( 'data-type' ) );
				for ( let i in items ) {
					if ( items.hasOwnProperty( i ) ) {
						if ( $this.is( ':checked' ) ) {
							items[i].select();
						}
						else {
							items[i].unSelect();
						}
					}
				}
			});

            /* Show details of minification row on mobile devices */
            $('body').on('click', '.wphb-border-row', function() {
                if ( window.innerWidth < 783 ) {
                    $(this).find('.wphb-minification-row-details').toggle();
					$(this).find('.fileinfo-group').toggleClass('opened');
                }
            });

            /*
             Catch window resize and revert styles for responsive divs
             1/4 of a second should be enough to trigger during device rotations (from portrait to landscape mode)
             */
            let minification_resize_rows = _.debounce(function() {

                if ( window.innerWidth >= 783 ) {
                    $('.wphb-minification-row-details').css('display', 'flex');
                } else {
                    $('.wphb-minification-row-details').css('display', 'none');
                }

            }, 250);

            window.addEventListener('resize', minification_resize_rows);

            return this;
        },

        updateProgressBar: function( progress, cancel = false ) {
            if ( progress > 100 ) {
                progress = 100;
            }
            // Update progress bar
            $('.wphb-scan-progress .wphb-scan-progress-text span').text( progress + '%' );
            $('.wphb-scan-progress .wphb-scan-progress-bar span').width( progress + '%' );
            if ( progress >= 90 ) {
                $('.wphb-progress-state .wphb-progress-state-text').text('Finalizing...');
            }
            if ( cancel ) {
                $('.wphb-progress-state .wphb-progress-state-text').text('Cancelling...');
            }
        },

		/**
		 * Switch from advanced to basic view.
		 * Called from switch view modal.
		 */
		switchView: function() {
			Fetcher.minification
				.toggleView( 'basic' )
				.then( () => {
					window.location.href = getLink( 'minification' );
				});
		},

		/**
		 * Notice on settings update.
		 *
		 * @param type
		 * @param message
		 */
		showNotice: function ( type = 'success', message = wphb.strings.successUpdate ) {
			const notice = $('#wphb-notice-minification-advanced-settings-updated');

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

    }; // End WPHB_Admin.minification

    WPHB_Admin.minification.Row = Row;
    WPHB_Admin.minification.RowsCollection = RowsCollection;

}( jQuery ));