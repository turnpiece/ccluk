import Fetcher from './utils/fetcher';
import { __, getLink } from './utils/helpers';
import Row from './minification/Row';
import RowsCollection from './minification/RowsCollection';
import Scanner from './minification/Scanner';

( function( $ ) {
    'use strict';

    WPHB_Admin.minification = {

        $checkFilesButton: null,
        $checkFilesResultsContainer : null,
        module: 'minification',
        checkURLSList: null,
        checkedURLS: 0,
        $spinner: null,

        init: function() {
            const self = this;

            // Init files scanner
            this.scanner = new Scanner( wphb.minification.get.totalSteps, wphb.minification.get.currentScanStep );
            this.scanner.onFinishStep = this.updateProgressBar;
            this.scanner.onFinish = ( response ) => {
                this.updateProgressBar( 100 );
                if ( wphb.minification.get.showCDNModal && true === response.show_cdn && $('#enable-cdn-modal').length ) {
                    window.WDP.showOverlay( '#enable-cdn-modal', { class: 'wphb-modal small wphb-progress-modal no-close' } );
                } else {
                    window.location.href = getLink( 'minification' );
                }
            };


            // Check files button
            this.$checkFilesButton = $( '#check-files' );
            this.$spinner = $('.spinner');

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

            // Filter action button on Minification page
            $('#wphb-minification-filter-button').on('click', function(e) {
                e.preventDefault();
                $('#wphb-minification-filter').toggle('slow');
            });

            $('.wphb-discard').click( function(e) {
                e.preventDefault();

                if ( confirm( __( 'discardAlert' ) ) ) {
                    location.reload();
                }
                return false;

            });

            $( '.wphb-enqueued-files input' ).on( 'change', function() {
                $('.wphb-discard').attr( 'disabled', false );
            });

            $('#use_cdn').change( function() {
                const cdn_value = $(this).is(':checked');
                Fetcher.minification.toggleCDN( cdn_value )
                    .then( () => {
                        const notice = $('#wphb-notice-minification-advanced-settings-updated');
                        notice.slideDown();
                        setTimeout( function() {
                            notice.slideUp();
                        }, 5000 );
                    });
            });

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

            $('#wphb-s').keyup( function() {
                self.rowsCollection.addFilter( $(this).val(), 'primary' );
                self.rowsCollection.applyFilters();
            });

            $('#wphb-secondary-filter').change( function() {
                self.rowsCollection.addFilter( $(this).val(), 'secondary' );
                self.rowsCollection.applyFilters();
            });

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

            const selectAll = $('#minification-bulk-file');
            selectAll.click( function() {
                const $this = $( this );
                let items = self.rowsCollection.getItems();
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

            // Include/exclude file checkbox
            $('.toggle-cross').on('click', function() {
                const $this = $(this);
                const checkbox = $this.find( 'input.toggle-include' );
                const row = self.rowsCollection.getItemById( $this.data( 'type' ), $this.data( 'handle' ) );
                // Mark the item as include or not in the rows list
                if ( row ) {
                    row.change( 'include', ! checkbox.prop( 'checked' ) );
                    row.getElement().find( 'input:not(.toggle-include)' ).prop('disabled', ! checkbox.prop( 'checked' ) );
                }
            });

            // Handle two CDN checkboxes on Minification page
            const checkboxes = $("input[type=checkbox][name=use_cdn]");
            checkboxes.change( function() {
                const checkedState = $(this).prop('checked');

                checkboxes.each( function() {
                    this.checked = checkedState;
                });
            });

            /* Show details of minification row on mobile devices */
            $('body').on('click', '.wphb-minification-file-details', function() {
                if ( window.innerWidth < 783 ) {
                    $(this).parent().find('.wphb-minification-row-details').toggle('slow');
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

    }; // End WPHB_Admin.minification

    WPHB_Admin.minification.Row = Row;
    WPHB_Admin.minification.RowsCollection = RowsCollection;

}( jQuery ));