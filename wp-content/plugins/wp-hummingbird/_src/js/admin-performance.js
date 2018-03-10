import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.performance = {

        module: 'performance',
        iteration: 0,
        progress: 0,

        init: function () {

            let self = this;
            let body = $('body');

            if (wphbPerformanceStrings)
                this.strings = wphbPerformanceStrings;

            this.$runTestButton = $('#run-performance-test');

            let performanceReportTable = $(".performance-report-table");

            performanceReportTable.off('click', 'button');
            performanceReportTable.on('click', '.wphb-performance-report-item-cta .additional-content-opener' && 'tr.wphb-performance-report-item:not(.wphb-table-score-disabled)', function (e) {
                e.preventDefault();

                let getParentPerformanceItem = $(this).closest(".wphb-performance-report-item"),
                    getNextAdditionalContentRow = getParentPerformanceItem.nextUntil(".wphb-performance-report-item");

                getNextAdditionalContentRow.toggleClass("wphb-performance-report-item-additional-content-opened");

                if (getNextAdditionalContentRow.hasClass("wphb-performance-report-item-additional-content-opened")) {
                    getParentPerformanceItem.addClass("wphb-performance-report-item-opened");
                } else {
                    getParentPerformanceItem.removeClass("wphb-performance-report-item-opened");
                }
            });

            if (this.$runTestButton.length) {
                this.$runTestButton.click(function (e) {
                    e.preventDefault();
					window.WDP.showOverlay("#run-performance-test-modal", { class: 'wphb-modal small wphb-progress-modal no-close' } );
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

            // Schedule show/hide day of week
            $('select[name="email-frequency"]').change(function () {
                if ( '1' === $(this).val() ) {
                    $(this).closest('.schedule-box').find('div.days-container').hide();
                } else {
                    $(this).closest('.schedule-box').find('div.days-container').show();
                }
            }).change();

            // Remove recipient
            body.on('click', '.wphb-remove-recipient', function (e) {
                e.preventDefault();
                $(this).closest('.recipient').remove();
                $('.scan-settings').find("input[id='scan_recipient'][value=" + $(this).attr('data-id') + "]").remove();
            });

            // Add recipient
            $('#add-receipt').click(function () {
                const email = $("#wphb-username-search").val();
                const name = $("#wphb-first-name").val();
                Fetcher.performance.addRecipient( email, name )
                    .then( ( response ) => {
                        const user_row = $('<div class="recipient"/>');

                        const img = $('<img/>').attr({
                            'src': response.avatar,
                            'width': '30'
                        });
                        const name = $('<span/>').html(response.name);

                        user_row.append('<span class="name"/>');
                        user_row.find('.name').append( img, name);


                        user_row.append($('<span class="email"/>').html(email));
                        user_row.append($('<a/>').attr({
                            'data-id': response.user_id,
                            'class': 'remove float-r wphb-remove-recipient',
                            'href': '#',
                            'alt': self.strings.removeButtonText
                        }).html('<i class="dev-icon dev-icon-cross"></i>'));

                        $('<input>').attr({
                            type: 'hidden',
                            id: 'scan_recipient',
                            name: 'email-recipients[]',
                            value: JSON.stringify( { email: response.email, name: response.name } )
                        }).appendTo(user_row);

                        $('.receipt .recipients').append(user_row);
                        $("#wphb-username-search").val('');
                        $("#wphb-first-name").val('');
                    })
                    .catch( ( error ) => {
                        alert( error.message );
                    } );
                return false;
            });

            // Save report settings
            body.on('submit', '.scan-frm', function (e) {
                e.preventDefault();
                const form_data = $(this).serialize();
                let that = $(this);

                that.find('.button').attr('disabled', 'disabled');

                Fetcher.performance.saveReportsSettings( form_data )
                    .then( () => {
                        that.find('.button').removeAttr('disabled');
                        self.showUpdateMessage();
                    });
                return false;
            });
            // Save performance test settings
            body.on('submit', '.settings-frm', function (e) {
                e.preventDefault();
                const form_data = $(this).serialize();

                Fetcher.performance.savePerformanceTestSettings( form_data )
                    .then( () => {
                        self.showUpdateMessage();
                    });
                return false;
            });

            return this;
        },

        showUpdateMessage: function () {
            const notice = $('#wphb-notice-performance-report-settings-updated');
            window.scrollTo(0,0);
            notice.slideDown();
            setTimeout( function() {
                notice.slideUp();
            }, 5000 );
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
			$('.wphb-scan-progress .wphb-scan-progress-text span').text( this.progress + '%' );
			$('.wphb-scan-progress .wphb-scan-progress-bar span').attr( 'style', 'width:' + this.progress + '%' );
        }
    };
}( jQuery ));