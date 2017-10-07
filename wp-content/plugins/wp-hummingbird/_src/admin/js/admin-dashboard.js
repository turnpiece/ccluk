import Fetcher from './utils/fetcher';

( function( $ ) {
    WPHB_Admin.dashboard = {
        module: 'dashboard',

        init: function() {
            const self = this;

			if (wphbDashboardStrings)
				this.strings = wphbDashboardStrings;

            $('#wphb-activate-minification').change( function() {
                const value = $(this).val();
                Fetcher.dashboard.toggleMinification( value )
                    .then( () => {
                        // If disabled, uncheck CDN checkbox and disable it.
                        const CDNcheckbox = $('input[name="use_cdn"]');
                        if ( 'false' === value ) {
                            CDNcheckbox.prop( 'checked', false );
                            CDNcheckbox.prop( 'disabled', true );
                        } else {
                            CDNcheckbox.prop( 'disabled', false );
                        }
                        self.showNotice();
                    });
            });

            $('#use_cdn').change( function() {
                const value = $(this).is(':checked');
                Fetcher.minification.toggleCDN( value )
                    .then( () => {
                        self.showNotice();
                    });
            });

            $('.wphb-performance-report-item').click( function() {
                const url = $(this).data( 'performance-url' );
                if ( url ) {
                    location.href = url;
                }
            });
            return this;
        },

        /**
         * Notice on settings update.
         */
        showNotice: function () {
            const notice = $('#wphb-notice-minification-settings-updated');
            notice.slideDown();
            setTimeout( function() {
                notice.slideUp();
            }, 5000 );
        },

		/**
         * Run quick setup.
		 */
		startQuickSetup: function () {
            // Show quick setup modal
			window.WDP.showOverlay( '#wphb-quick-setup-modal', { class: 'wphb-modal small wphb-quick-setup-modal no-close' } );
        },

		/**
         * Skip quick setup.
		 */
		skipSetup: function () {
            Fetcher.dashboard.skipSetup()
                .then( () => {
                    window.location.reload(true);
                });
        },

		/**
         * Run performance test after quick setup.
		 */
		runPerformanceTest: function() {
			// Show quick setup modal
			window.WDP.showOverlay("#run-performance-test-modal", { class: 'wphb-modal small wphb-progress-modal no-close' } );

			// Run performance test
			const module = window.WPHB_Admin.getModule('performance');
			module.performanceTest( this.strings.finishedTestURLsLink );
        }
    };
}( jQuery ));