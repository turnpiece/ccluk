import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.performance = {

        module: 'performance',
        iteration: 0,
        progress: 0,

        init: function () {

            let self = this;

			/** @var {array} wphbPerformanceStrings */
            if (wphbPerformanceStrings) {
				this.strings = wphbPerformanceStrings;
            }

            this.$runTestButton = $('#run-performance-test');

            if (this.$runTestButton.length) {
                this.$runTestButton.click(function (e) {
                    e.preventDefault();

                    SUI.dialogs['run-performance-test-modal'].show();
                    $(this).attr('disabled', true);
                    self.performanceTest(self.strings.finishedTestURLsLink);
                });
            }

            // If a hash is present in URL, let's open the rule extra content
            const hash = window.location.hash;
            if (hash) {
                const row = $(hash);
                if (row.length) {
                    row.find('.trigger-additional-content').trigger('click');
                }
            }

            // Save performance test settings
            $('body').on('submit', '.settings-frm', function (e) {
                e.preventDefault();
                const form_data = $(this).serialize();

                Fetcher.performance.savePerformanceTestSettings( form_data )
                    .then( () => {
						WPHB_Admin.notices.show('wphb-notice-performance-report-settings-updated', true);
                    });
                return false;
            });

            return this;
        },

		performanceTest: function ( redirect ) {
			const self = this;

            if ( typeof redirect === 'undefined' )
                redirect = false;

            // Update progress bar
            self.updateProgressBar();

            Fetcher.performance.runTest()
                .then( ( response ) => {
					if ( ! response.finished ) {
						// Try again 3 seconds later
						window.setTimeout(function () {
							self.performanceTest( redirect );
						}, 3000);
					} else if ( redirect ) {
					    // Give a second for the report to be saved to the db
						window.setTimeout(function () {
							window.location = redirect;
						}, 1000);
					}
                });
        },

        updateProgressBar: function() {
			if ( this.progress < 90 ) {
				this.progress += 35;
			}
			if ( this.progress > 100 ) {
				this.progress = 90;
			}
			$('.sui-progress-block .sui-progress-text span').text( this.progress + '%' );
			$('.sui-progress-block .sui-progress-bar span').attr( 'style', 'width:' + this.progress + '%' );
        }
    };
}( jQuery ));