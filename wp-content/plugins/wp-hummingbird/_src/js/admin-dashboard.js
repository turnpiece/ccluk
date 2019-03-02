import Fetcher from './utils/fetcher';

( function( $ ) {
    WPHB_Admin.dashboard = {
        module: 'dashboard',

        init: function() {
            const self = this;
            let cloudFlareDismissLink = $('#dismiss-cf-notice');
            let cloudFlareDashNotice = $('.cf-dash-notice');

			if (wphbDashboardStrings)
				this.strings = wphbDashboardStrings;

            $('#wphb-activate-minification').change( function() {
                const value = $(this).val();
                Fetcher.dashboard.toggleMinification( value )
                    .then( () => {
                        // If disabled, uncheck CDN checkbox and disable it.
                        const CDNcheckbox    = $('input[name="use_cdn"]');
                        const CDNtooltip     = $('span[id="cdn_tooltip"]');
                        const enabledNotice  = $('#wphb-notice-minification-enabled');
                        if ( 'false' === value ) {
                            CDNcheckbox.prop( 'checked', false );
                            CDNcheckbox.prop( 'disabled', true );
                            CDNtooltip.attr( 'tooltip', $('input[id="cdn_disabled_tooltip"]').val() );
                            enabledNotice.addClass( 'sui-hidden' );
                        } else {
                            CDNcheckbox.prop( 'disabled', false );
                            CDNtooltip.attr( 'tooltip', $('input[id="cdn_enabled_tooltip"]').val() );
                            enabledNotice.addClass( 'sui-hidden' );
                        }
						WPHB_Admin.notices.show( 'wphb-notice-minification-settings-updated' );
                    });
            });

            $('#use_cdn').change( function() {
                const value = $(this).is(':checked');
                Fetcher.minification.toggleCDN( value )
                    .then( () => {
						WPHB_Admin.notices.show( 'wphb-notice-minification-settings-updated' );
                    });
            });

            $("input[type=checkbox][name=debug_log]").change( function() {
                Fetcher.minification.toggleLog( $(this).is(':checked') )
                    .then( () => {
                        WPHB_Admin.notices.show( 'wphb-notice-minification-settings-updated' );
                        $('#wphb-minification-debug-log').toggleClass('sui-hidden');
                    });
            });

            $('#admins_disable_caching').change( function() {
                const value = $(this).is(':checked');
                Fetcher.caching.toggleSubsitePageCaching( value )
                    .then( () => {
						WPHB_Admin.notices.show( 'wphb-notice-pc-settings-updated' );
                    });
            });

            $('.wphb-performance-report-item').click( function() {
                const url = $(this).data( 'performance-url' );
                if ( url ) {
                    location.href = url;
                }
            });

            cloudFlareDismissLink.click( function(e) {
                e.preventDefault();
                Fetcher.notice.dismissCloudflareDash();
                cloudFlareDashNotice.slideUp();
                cloudFlareDashNotice.parent().addClass('no-background-image');

            });
            return this;
        },

		/**
         * Skip quick setup.
		 */
		skipSetup: function () {
            Fetcher.dashboard.skipSetup()
                .then( () => {
                    location.reload();
                });
        },

		/**
         * Run performance test after quick setup.
		 */
		runPerformanceTest: function() {
			// Show performance test modal
            SUI.dialogs['run-performance-test-modal'].show();

			// Run performance test
			const module = window.WPHB_Admin.getModule('performance');
			module.performanceTest( this.strings.finishedTestURLsLink );
        }
    };
}( jQuery ));