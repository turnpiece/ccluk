import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';

    let WPHB_Admin = {
        modules: [],
        // Common functionality to all screens
        init: function() {

            // Mobile navigation links.
			let mobileNav = document.querySelector('select.mobile-nav');

			if ( mobileNav ) {
				mobileNav.onchange = (e) => {
					const url = e.target.value;
					if (url.length > 0) {
						location.href = url;
					}
				};
            }

            /*
			$('body').on('change', '.mobile-nav', function () {
				let url = $(this).val();
				if (url.length > 0) {
					location.href = url;
				}
			});
			*/

			// Dismiss notice via an ajax call.
            let notice = document.querySelector('#wphb-dismissable > .close');

            if ( notice ) {
				notice.addEventListener('click', () => {
					const notice_id = notice.parentElement.getAttribute('data-id');
					Fetcher.notice.dismiss(notice_id);
				});
            }

			/*
			$('#wphb-dismissable').on('click', '.close', function() {
			    const notice_id = $(this).parent().attr('data-id');
			    Fetcher.notice.dismiss( notice_id );
            });
            */

            function updatePerformanceGraph($wrap){
                let $item = $wrap.find('.wphb-score-result-label'),
                    val = parseInt($item.text(), 10) || 100,
                    $circle = $wrap.find(".wphb-score-graph-result"),
                    r, c, pct
                    ;
                r = $circle.attr('r');
                c = Math.PI*(r*2);

                if (val < 0) { val = 0;}
                if (val > 100) { val = 100;}

                pct = ((100-val)/100)*c;

                $circle.css({ strokeDashoffset: pct});
            }

            function updatePerformanceResultsGraphs(){
                // Update Overall Score
                $(".wphb-performance-report-overall-score").each(function(){
                    updatePerformanceGraph($(this));
                });

                // Update All Scores
                $(".wphb-performance-report-item-score").each(function(){
                    updatePerformanceGraph($(this));
                });
            }
            window.register_events_performance = function(){
                setTimeout(updatePerformanceResultsGraphs, 500);
            };
            $(function(){ setTimeout(updatePerformanceResultsGraphs, 500); });

        },
        initModule: function( module ) {
            if ( this.hasOwnProperty( module ) ) {
                this.modules[ module ] = this[ module ].init();
                return this.modules[ module ];
            }

            return {};
        },
        getModule: function( module ) {
            if ( typeof this.modules[ module ] !== 'undefined' )
                return this.modules[ module ];
            else
                return this.initModule( module );
        }
    };

    WPHB_Admin.utils = {
        membershipModal: {
            open: function() {
                $( '#wphb-upgrade-membership-modal-link').trigger( 'click' );
            }
        },

        post: function( data, module ) {
            data.action = 'wphb_ajax';
            data.module = module;
            return $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: data
            });
        }
    };

    /*WPHB_Admin.notices = {
        init: function() {
            $( '.wphb-notice:not(.notice) a.wphb-dismiss').click( function( e ) {
                e.preventDefault();
                let id = $(this).data( 'id' );
                let nonce = $(this).data( 'nonce' );

                $(this).parent( '.error' ).hide();
            });
        }
    };*/

    window.WPHB_Admin = WPHB_Admin;

}( jQuery ) );